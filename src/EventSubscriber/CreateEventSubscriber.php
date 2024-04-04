<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Event\CreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected ?AuditLogAction $action = AuditLogAction::Create;

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
