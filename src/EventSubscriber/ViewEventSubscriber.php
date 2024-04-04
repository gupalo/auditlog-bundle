<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ViewEventSubscriber extends BaseEventSubscribe implements EventSubscriberInterface
{
    protected ?AuditLogAction $action = AuditLogAction::View;

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
