<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Event\ExportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected ?AuditLogAction $action = AuditLogAction::Export;

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
