<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\ExportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'export';
    
    public static function getSubscribedEvents(): array
    {
        return [
            ExportEvent::class => 'logExportEvent',
        ];
    }

    public function logExportEvent(ExportEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
