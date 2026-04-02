<?php

namespace Gupalo\AuditLogBundle\Tests\DependencyInjection;

use Gupalo\AuditLogBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            ['events' => []],
        ]);

        self::assertFalse($config['events']['archive']);
        self::assertFalse($config['events']['universal']);
        self::assertFalse($config['events']['create']);
        self::assertFalse($config['events']['export']);
        self::assertFalse($config['events']['list']);
        self::assertFalse($config['events']['login']);
        self::assertFalse($config['events']['restore']);
        self::assertFalse($config['events']['view']);
    }

    public function testCustomConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            ['events' => ['archive' => true, 'login' => true]],
        ]);

        self::assertTrue($config['events']['archive']);
        self::assertTrue($config['events']['login']);
        self::assertFalse($config['events']['create']);
    }

    public function testTreeBuilderRootNode(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();

        self::assertSame('audit_log', $treeBuilder->buildTree()->getName());
    }
}
