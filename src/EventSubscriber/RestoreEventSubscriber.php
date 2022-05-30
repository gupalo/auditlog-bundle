<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\RestoreEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RestoreEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'restore';
    
    public static function getSubscribedEvents(): array
    {
        return [
            RestoreEvent::class => 'logRestoreEvent',
        ];
    }

    public function logRestoreEvent(RestoreEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
