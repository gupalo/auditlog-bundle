<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use DateTimeInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Gupalo\DateUtils\DateUtils;
use JsonException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditLogEventSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private TokenStorageInterface $tokenStorage
    ) {
    }
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postPersist(\Doctrine\Persistence\Event\LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof AwareAuditLogInterface) {
            $uow = $this->em->getUnitOfWork();
            $changes = $uow->getEntityChangeSet($entity);

            $this->addAuditItem($this->em, $entity, AuditLogAction::Create);
            $this->addAuditItemsForChangeSet($this->em, $entity, $changes);
            $this->em->flush();
        }
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     * @throws JsonException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postUpdate(\Doctrine\Persistence\Event\LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof AwareAuditLogInterface) {
            $uow = $this->em->getUnitOfWork();
            $changes = $uow->getEntityChangeSet($entity);
            if (isset($changes['archivedAt'])) {
                $this->addAuditItem(
                    $this->em,
                    $entity,
                    $changes['archivedAt'][1] ? AuditLogAction::Archive : AuditLogAction::Restore
                );
            }

            $this->addAuditItemsForChangeSet($this->em, $entity, $changes);

            $this->em->flush();
        }
    }

    private function addAuditItem(
        EntityManagerInterface $em,
        AwareAuditLogInterface $entity,
        AuditLogAction $action,
        ?string $field = null,
        ?string $before = null,
        ?string $after = null,
    ): void {

        $audit = (new AuditLog())
            ->setCreatedAt(DateUtils::now())
            ->setUser($this->tokenStorage->getToken()?->getUserIdentifier())
            ->setAction($action->value)
            ->setEntity(get_class($entity))
            ->setEntityId($entity->getId())
            ->setField($field)
            ->setBeforeValue($before)
            ->setAfterValue($after);
        $em->persist($audit);
    }

    private function addAuditItemsForChangeSet(EntityManagerInterface $em, AwareAuditLogInterface $entity, array $changes)
    {
        foreach ($changes as $field => $change) {
            $this->addAuditItem(
                $em,
                $entity,
                AuditLogAction::Edit,
                $field,
                $this->resolveFieldValue($change[0]),
                $this->resolveFieldValue($change[1]),
            );
        }
    }

    /**
     * @throws JsonException
     */
    private function resolveFieldValue($value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateUtils::FORMAT_FULL);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return (string)$value;
    }
}
