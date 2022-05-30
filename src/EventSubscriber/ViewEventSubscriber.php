<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ViewEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'view';
    
    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvent::class => 'logViewEvent',
        ];
    }

    public function logViewEvent(ViewEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
