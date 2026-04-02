<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\DateUtils\DateUtils;
use JsonException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
readonly class AuditLogEventSubscriber
{
    public function __construct(
        private EntityManagerInterface $em,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /** @param LifecycleEventArgs<EntityManagerInterface> $args */
    public function postPersist(LifecycleEventArgs $args): void
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

    /** @param LifecycleEventArgs<EntityManagerInterface> $args */
    public function postUpdate(LifecycleEventArgs $args): void
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
            ->setEntity($entity::class)
            ->setEntityId(method_exists($entity, 'getId') ? $entity->getId() : null)
            ->setField($field)
            ->setBeforeValue($before)
            ->setAfterValue($after);
        $em->persist($audit);
    }

    /** @param array<string, array{0: mixed, 1: mixed}|mixed> $changes */
    private function addAuditItemsForChangeSet(EntityManagerInterface $em, AwareAuditLogInterface $entity, array $changes): void
    {
        foreach ($changes as $field => $change) {
            $this->addAuditItem(
                em: $em,
                entity: $entity,
                action: AuditLogAction::Edit,
                field: (string) $field,
                before: $this->resolveFieldValue($change[0]),
                after: $this->resolveFieldValue($change[1]),
            );
        }
    }

    /**
     * @throws JsonException
     */
    private function resolveFieldValue(mixed $value): string
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
