<?php

namespace Gupalo\AuditLogBundle\Event;

use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;

readonly class RestoreEvent
{
    public function __construct(private AwareAuditLogInterface $entity)
    {
    }

    public function getEntity(): AwareAuditLogInterface
    {
        return $this->entity;
    }
}
