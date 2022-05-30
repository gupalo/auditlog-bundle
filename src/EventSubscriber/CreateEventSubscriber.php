<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\CreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'create';
    
    public static function getSubscribedEvents(): array
    {
        return [
            CreateEvent::class => 'logCreateEvent',
        ];
    }

    public function logCreateEvent(CreateEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
