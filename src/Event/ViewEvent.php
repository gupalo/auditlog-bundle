<?php

namespace Gupalo\AuditLogBundle\Event;

use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;

readonly class ViewEvent
{
    public function __construct(private AwareAuditLogInterface $entity)
    {
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
