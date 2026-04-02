<?php

namespace Gupalo\AuditLogBundle\Tests\Entity;

use DateTime;
use DateTimeInterface;
use Gupalo\AuditLogBundle\Entity\AuditLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuditLog::class)]
class AuditLogTest extends TestCase
{
    private AuditLog $auditLog;

    protected function setUp(): void
    {
        $this->auditLog = new AuditLog();
    }

    public function testIdIsNullByDefault(): void
    {
        self::assertNull($this->auditLog->getId());
    }

    public function testSetGetAction(): void
    {
        $result = $this->auditLog->setAction('create');

        self::assertSame($this->auditLog, $result);
        self::assertSame('create', $this->auditLog->getAction());
    }

    public function testSetGetEntity(): void
    {
        $result = $this->auditLog->setEntity('App\\Entity\\User');

        self::assertSame($this->auditLog, $result);
        self::assertSame('App\\Entity\\User', $this->auditLog->getEntity());
    }

    public function testSetGetEntityNull(): void
    {
        $this->auditLog->setEntity(null);

        self::assertNull($this->auditLog->getEntity());
    }

    public function testSetGetEntityId(): void
    {
        $result = $this->auditLog->setEntityId(42);

        self::assertSame($this->auditLog, $result);
        self::assertSame(42, $this->auditLog->getEntityId());
    }

    public function testSetGetEntityIdNull(): void
    {
        $this->auditLog->setEntityId(null);

        self::assertNull($this->auditLog->getEntityId());
    }

    public function testSetGetUser(): void
    {
        $result = $this->auditLog->setUser('admin@example.com');

        self::assertSame($this->auditLog, $result);
        self::assertSame('admin@example.com', $this->auditLog->getUser());
    }

    public function testSetGetUserNull(): void
    {
        $this->auditLog->setUser(null);

        self::assertNull($this->auditLog->getUser());
    }

    public function testSetGetCreatedAt(): void
    {
        $date = new DateTime('2024-01-15 10:30:00');
        $result = $this->auditLog->setCreatedAt($date);

        self::assertSame($this->auditLog, $result);
        self::assertSame($date, $this->auditLog->getCreatedAt());
    }

    public function testSetGetIp(): void
    {
        $result = $this->auditLog->setIp('192.168.1.1');

        self::assertSame($this->auditLog, $result);
        self::assertSame('192.168.1.1', $this->auditLog->getIp());
    }

    public function testSetGetCountry(): void
    {
        $result = $this->auditLog->setCountry('US');

        self::assertSame($this->auditLog, $result);
        self::assertSame('US', $this->auditLog->getCountry());
    }

    public function testSetGetField(): void
    {
        $result = $this->auditLog->setField('email');

        self::assertSame($this->auditLog, $result);
        self::assertSame('email', $this->auditLog->getField());
    }

    public function testSetGetBeforeValue(): void
    {
        $result = $this->auditLog->setBeforeValue('old@example.com');

        self::assertSame($this->auditLog, $result);
        self::assertSame('old@example.com', $this->auditLog->getBeforeValue());
    }

    public function testSetGetAfterValue(): void
    {
        $result = $this->auditLog->setAfterValue('new@example.com');

        self::assertSame($this->auditLog, $result);
        self::assertSame('new@example.com', $this->auditLog->getAfterValue());
    }

    public function testInitializeCreatedAtSetsDateTime(): void
    {
        $this->auditLog->initializeCreatedAt();

        self::assertInstanceOf(DateTimeInterface::class, $this->auditLog->getCreatedAt());
    }

    public function testInitializeCreatedAtPreservesExisting(): void
    {
        $existing = new DateTime('2020-01-01 00:00:00');
        $this->auditLog->setCreatedAt($existing);

        $this->auditLog->initializeCreatedAt();

        self::assertSame($existing, $this->auditLog->getCreatedAt());
    }
}
