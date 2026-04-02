<?php

namespace Gupalo\AuditLogBundle\Tests\DependencyInjection;

use Gupalo\AuditLogBundle\DependencyInjection\AuditLogExtension;
use Gupalo\AuditLogBundle\DependencyInjection\Configuration;
use Gupalo\AuditLogBundle\EventSubscriber\ArchiveEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\AuditLogEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\CreateEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ExportEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ListEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\LoginSuccessEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\RestoreEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ViewEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(AuditLogExtension::class)]
class AuditLogExtensionTest extends TestCase
{
    public function testLoadWithNoEventsEnabled(): void
    {
        $container = $this->createContainer([]);

        $subscriberClasses = [
            ArchiveEventSubscriber::class,
            AuditLogEventSubscriber::class,
            CreateEventSubscriber::class,
            ExportEventSubscriber::class,
            ListEventSubscriber::class,
            LoginSuccessEventSubscriber::class,
            RestoreEventSubscriber::class,
            ViewEventSubscriber::class,
        ];

        foreach ($subscriberClasses as $class) {
            self::assertFalse($container->hasDefinition($class), "$class should not be registered");
        }
    }

    /** @return iterable<string, array{string, class-string}> */
    public static function eventSubscriberProvider(): iterable
    {
        yield 'archive' => ['archive', ArchiveEventSubscriber::class];
        yield 'universal' => ['universal', AuditLogEventSubscriber::class];
        yield 'create' => ['create', CreateEventSubscriber::class];
        yield 'export' => ['export', ExportEventSubscriber::class];
        yield 'list' => ['list', ListEventSubscriber::class];
        yield 'login' => ['login', LoginSuccessEventSubscriber::class];
        yield 'restore' => ['restore', RestoreEventSubscriber::class];
        yield 'view' => ['view', ViewEventSubscriber::class];
    }

    #[DataProvider('eventSubscriberProvider')]
    public function testLoadRegistersSubscriberWhenEnabled(string $eventName, string $subscriberClass): void
    {
        $container = $this->createContainer(['events' => [$eventName => true]]);

        self::assertTrue($container->hasDefinition($subscriberClass), "$subscriberClass should be registered");

        $definition = $container->getDefinition($subscriberClass);
        self::assertTrue($definition->isAutowired());
        self::assertTrue($definition->isAutoconfigured());
        self::assertTrue($definition->isPublic());
    }

    public function testGetConfigurationReturnsConfigurationInstance(): void
    {
        $extension = new AuditLogExtension();
        $container = new ContainerBuilder();

        $result = $extension->getConfiguration([], $container);

        self::assertInstanceOf(ConfigurationInterface::class, $result);
        self::assertInstanceOf(Configuration::class, $result);
    }

    private function createContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new AuditLogExtension();
        $extension->load([$config], $container);

        return $container;
    }
}
