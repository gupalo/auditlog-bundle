<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class LoginSuccessEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'login';

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'logLoginEvent',
        ];
    }

    public function logLoginEvent(AuthenticationSuccessEvent $event): void
    {
        $this->saveLog(null, $event->getAuthenticationToken()->getUser());
    }
}
