<?php

namespace Gupalo\AuditLogBundle\Tests\Event;

use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Event\ArchiveEvent;
use Gupalo\AuditLogBundle\Event\CreateEvent;
use Gupalo\AuditLogBundle\Event\ExportEvent;
use Gupalo\AuditLogBundle\Event\ListEvent;
use Gupalo\AuditLogBundle\Event\RestoreEvent;
use Gupalo\AuditLogBundle\Event\ViewEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /** @return iterable<string, array{class-string}> */
    public static function eventClassProvider(): iterable
    {
        yield 'CreateEvent' => [CreateEvent::class];
        yield 'ArchiveEvent' => [ArchiveEvent::class];
        yield 'ExportEvent' => [ExportEvent::class];
        yield 'ListEvent' => [ListEvent::class];
        yield 'RestoreEvent' => [RestoreEvent::class];
        yield 'ViewEvent' => [ViewEvent::class];
    }

    #[DataProvider('eventClassProvider')]
    public function testGetEntityReturnsEntity(string $eventClass): void
    {
        $entity = $this->createMock(AwareAuditLogInterface::class);
        $event = new $eventClass($entity);

        self::assertSame($entity, $event->getEntity());
    }
}
