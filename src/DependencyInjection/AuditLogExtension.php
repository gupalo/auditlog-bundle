<?php

namespace Gupalo\AuditLogBundle\DependencyInjection;

use Exception;
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
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        if ($config['events']['archive']) {
            $this->registerAutowire($container, ArchiveEventSubscriber::class);
        }

        if ($config['events']['universal']) {
            $this->registerAutowire($container, AuditLogEventSubscriber::class, 'doctrine.event_subscriber');
        }

        if ($config['events']['create']) {
            $this->registerAutowire($container, CreateEventSubscriber::class);
        }

        if ($config['events']['export']) {
            $this->registerAutowire($container, ExportEventSubscriber::class);
        }

        if ($config['events']['list']) {
            $this->registerAutowire($container, ListEventSubscriber::class);
        }

        if ($config['events']['login']) {
            $this->registerAutowire($container, LoginSuccessEventSubscriber::class);
        }

        if ($config['events']['restore']) {
            $this->registerAutowire($container, RestoreEventSubscriber::class);
        }

        if ($config['events']['view']) {
            $this->registerAutowire($container, ViewEventSubscriber::class);
        }
    }

    private function registerAutowire(ContainerBuilder $container, $service, $tag = ''): void
    {
        if ($tag !== '') {
            $container->register($service)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag($tag);
        } else {
            $container->register($service)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }
}
