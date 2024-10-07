<?php /** @noinspection UnknownInspectionInspection */

namespace Gupalo\AuditLogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('audit_log');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('events')
                ->children()
                    ->booleanNode('archive')->defaultFalse()->end()
                    ?->booleanNode('universal')->defaultFalse()->end()
                    ?->booleanNode('create')->defaultFalse()->end()
                    ?->booleanNode('export')->defaultFalse()->end()
                    ?->booleanNode('list')->defaultFalse()->end()
                    ?->booleanNode('login')->defaultFalse()->end()
                    ?->booleanNode('restore')->defaultFalse()->end()
                    ?->booleanNode('view')->defaultFalse()->end()
        ;

        return $treeBuilder;
    }
}
