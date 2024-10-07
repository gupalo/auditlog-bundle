<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseEventSubscribe
{
    private const IGNORE_USERS = [
        'prometheus',
    ];

    protected ?AuditLogAction $action = null;

    public function __construct(
        protected AuditLogRepository $auditLogRepository,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack
    ) {
    }

    protected function saveLog(?AwareAuditLogInterface $entity = null, ?UserInterface $user = null): void
    {
        $audit = new AuditLog();

        $user ??= $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof UserInterface) {
            $audit->setUser($user->getUserIdentifier());
        }

        if (in_array($audit->getUser(), self::IGNORE_USERS, true)) {
            return;
        }

        if ($entity) {
            $audit->setEntity($entity::class);
            $audit->setEntityId(method_exists($entity, 'getId') ? $entity->getId() : null);
        }
        $audit->setAction($this->action?->value ?? '');
        $audit->setIp($this->requestStack->getCurrentRequest()?->getClientIp());

        $this->auditLogRepository->add($audit, true);
    }
}
