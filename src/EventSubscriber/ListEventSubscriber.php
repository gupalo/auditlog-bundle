<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\ListEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'list';
    
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
