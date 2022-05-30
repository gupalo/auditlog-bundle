<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Event\ArchiveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArchiveEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected string $action = 'archive';

    public static function getSubscribedEvents(): array
    {
        return [
            ArchiveEvent::class => 'logArchiveEntity',
        ];
    }

    public function logArchiveEntity(ArchiveEvent $event): void
    {
        $entity = $event->getEntity();

        $this->saveLog($entity);
    }
}
