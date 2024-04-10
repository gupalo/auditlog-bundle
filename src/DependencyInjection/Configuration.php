<?php

namespace Gupalo\AuditLogBundle\DependencyInjection;

use FOS\ElasticaBundle\Serializer\Callback as SerializerCallback;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * If the kernel is running in debug mode.
     *
     * @var bool
     */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('audit_log');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('events')
                ->children()
                    ->booleanNode('archive')->defaultFalse()->end()
                    ->booleanNode('universal')->defaultFalse()->end()
                    ->booleanNode('create')->defaultFalse()->end()
                    ->booleanNode('export')->defaultFalse()->end()
                    ->booleanNode('list')->defaultFalse()->end()
                    ->booleanNode('login')->defaultFalse()->end()
                    ->booleanNode('restor')->defaultFalse()->end()
                    ->booleanNode('view')->defaultFalse()->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function createTreeBuilderNode(string $name)
    {
        return (new TreeBuilder($name))->getRootNode();
    }
}
