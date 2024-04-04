<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Event\ListEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected ?AuditLogAction $action = AuditLogAction::List;

    public static function getSubscribedEvents(): array
    {
        return [
            ListEvent::class => 'logListEvent',
        ];
    }

    public function logListEvent(ListEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
