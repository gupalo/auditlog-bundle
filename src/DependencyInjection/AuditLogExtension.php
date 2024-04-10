<?php

namespace Gupalo\AuditLogBundle\DependencyInjection;

use Exception;
use Gupalo\AuditLogBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\ArchiveEventSubscriber');
        }

        if ($config['events']['universal']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\AuditLogEventSubscriber', 'doctrine.event_subscriber');
        }

        if ($config['events']['create']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\CreateEventSubscriber');
        }

        if ($config['events']['export']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\ExportEventSubscriber');
        }

        if ($config['events']['list']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\ListEventSubscriber');
        }

        if ($config['events']['login']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\LoginSuccessEventSubscriber');
        }

        if ($config['events']['restor']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\RestoreEventSubscriber');
        }

        if ($config['events']['view']) {
            $this->registerAutowire($container, 'Gupalo\\AuditLogBundle\\EventSubscriber\\ViewEventSubscriber');
        }

        $env = $container->getParameter('kernel.environment');
    }

    private function registerAutowire(ContainerBuilder $container, $service, $tag = '')
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
        return new Configuration($container->getParameter('kernel.debug'));
    }

}
