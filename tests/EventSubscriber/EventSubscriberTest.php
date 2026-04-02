<?php

namespace Gupalo\AuditLogBundle\Tests\EventSubscriber;

use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\Event\ArchiveEvent;
use Gupalo\AuditLogBundle\Event\CreateEvent;
use Gupalo\AuditLogBundle\Event\ExportEvent;
use Gupalo\AuditLogBundle\Event\ListEvent;
use Gupalo\AuditLogBundle\Event\RestoreEvent;
use Gupalo\AuditLogBundle\Event\ViewEvent;
use Gupalo\AuditLogBundle\EventSubscriber\ArchiveEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\CreateEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ExportEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ListEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\LoginSuccessEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\RestoreEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ViewEventSubscriber;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class EventSubscriberTest extends TestCase
{
    /** @return iterable<string, array{class-string, class-string, string, AuditLogAction}> */
    public static function entityEventSubscriberProvider(): iterable
    {
        yield 'CreateEventSubscriber' => [CreateEventSubscriber::class, CreateEvent::class, 'logCreateEvent', AuditLogAction::Create];
        yield 'ArchiveEventSubscriber' => [ArchiveEventSubscriber::class, ArchiveEvent::class, 'logArchiveEntity', AuditLogAction::Archive];
        yield 'ExportEventSubscriber' => [ExportEventSubscriber::class, ExportEvent::class, 'logExportEvent', AuditLogAction::Export];
        yield 'ListEventSubscriber' => [ListEventSubscriber::class, ListEvent::class, 'logListEvent', AuditLogAction::List];
        yield 'RestoreEventSubscriber' => [RestoreEventSubscriber::class, RestoreEvent::class, 'logRestoreEvent', AuditLogAction::Restore];
        yield 'ViewEventSubscriber' => [ViewEventSubscriber::class, ViewEvent::class, 'logViewEvent', AuditLogAction::View];
    }

    #[DataProvider('entityEventSubscriberProvider')]
    public function testGetSubscribedEvents(string $subscriberClass, string $eventClass, string $method, AuditLogAction $expectedAction): void
    {
        $events = $subscriberClass::getSubscribedEvents();

        self::assertArrayHasKey($eventClass, $events);
        self::assertSame($method, $events[$eventClass]);
    }

    #[DataProvider('entityEventSubscriberProvider')]
    public function testAction(string $subscriberClass, string $eventClass, string $method, AuditLogAction $expectedAction): void
    {
        $subscriber = new $subscriberClass(
            $this->createMock(AuditLogRepository::class),
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(RequestStack::class),
        );

        $reflection = new \ReflectionProperty($subscriber, 'action');
        self::assertSame($expectedAction, $reflection->getValue($subscriber));
    }

    public function testLoginSuccessGetSubscribedEvents(): void
    {
        $events = LoginSuccessEventSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(AuthenticationSuccessEvent::class, $events);
        self::assertSame('logLoginEvent', $events[AuthenticationSuccessEvent::class]);
    }

    public function testLoginSuccessAction(): void
    {
        $subscriber = new LoginSuccessEventSubscriber(
            $this->createMock(AuditLogRepository::class),
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(RequestStack::class),
        );

        $reflection = new \ReflectionProperty($subscriber, 'action');
        self::assertSame(AuditLogAction::Login, $reflection->getValue($subscriber));
    }
}
