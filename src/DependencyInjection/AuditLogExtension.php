<?php

namespace Gupalo\AuditLogBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Gupalo\AuditLogBundle\EventSubscriber\ArchiveEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\AuditLogEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\CreateEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ExportEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ListEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\LoginSuccessEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\RestoreEventSubscriber;
use Gupalo\AuditLogBundle\EventSubscriber\ViewEventSubscriber;

class AuditLogExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $events = $config['events'] ?? [];

        if ($events['archive'] ?? false) {
            $this->registerAutowire($container, ArchiveEventSubscriber::class);
        }

        if ($events['universal'] ?? false) {
            $this->registerAutowire($container, AuditLogEventSubscriber::class);
        }

        if ($events['create'] ?? false) {
            $this->registerAutowire($container, CreateEventSubscriber::class);
        }

        if ($events['export'] ?? false) {
            $this->registerAutowire($container, ExportEventSubscriber::class);
        }

        if ($events['list'] ?? false) {
            $this->registerAutowire($container, ListEventSubscriber::class);
        }

        if ($events['login'] ?? false) {
            $this->registerAutowire($container, LoginSuccessEventSubscriber::class);
        }

        if ($events['restore'] ?? false) {
            $this->registerAutowire($container, RestoreEventSubscriber::class);
        }

        if ($events['view'] ?? false) {
            $this->registerAutowire($container, ViewEventSubscriber::class);
        }
    }

    private function registerAutowire(ContainerBuilder $container, string $service): void
    {
        $container->register($service)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true);
    }

    /** @param array<mixed> $config */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
