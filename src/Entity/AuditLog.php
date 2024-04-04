<?php

namespace Gupalo\AuditLogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use DateTime;
use DateTimeInterface;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $action;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $entity = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $field;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $beforeValue;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $afterValue;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new DateTime();
        }
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getBeforeValue(): ?string
    {
        return $this->beforeValue;
    }

    public function setBeforeValue(?string $beforeValue): self
    {
        $this->beforeValue = $beforeValue;

        return $this;
    }

    public function getAfterValue(): ?string
    {
        return $this->afterValue;
    }

    public function setAfterValue(?string $afterValue): self
    {
        $this->afterValue = $afterValue;

        return $this;
    }
}
