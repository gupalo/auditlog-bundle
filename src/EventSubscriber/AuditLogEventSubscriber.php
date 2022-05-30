<?php

namespace Gupalo\AuditLogBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class AuditLogEventSubscriber implements EventSubscriber
{

    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate
        ];
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {

    }
}
