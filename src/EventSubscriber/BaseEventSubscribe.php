<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use Gupalo\GoogleAuthBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseEventSubscribe
{
    protected string $action = '';

    public function __construct(
        protected AuditLogRepository $auditLogRepository,
        protected Security $security,
        protected RequestStack $requestStack
    ) {
    }

    protected function saveLog(?AwareAuditLogInterface $entity = null, ?UserInterface $user = null): void
    {
        $audit = new AuditLog();
        if ($entity) {
            $audit->setEntity($entity::class);
            $audit->setEntityId($entity->getId());
        }
        $audit->setAction($this->action);
        $user = $user ?? $this->security->getUser();
        if ($user instanceof UserInterface) {
            $audit->setUser($user->getUserIdentifier());
        }
        $audit->setIp($this->requestStack->getCurrentRequest()->getClientIp());

        $this->auditLogRepository->add($audit, true);
    }
}
