<?php

namespace Gupalo\AuditLogBundle\Event;

use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;

readonly class ListEvent
{
    public function __construct(private AwareAuditLogInterface $entity)
    {
    }

    public function getEntity(): AwareAuditLogInterface
    {
        return $this->entity;
    }
}
