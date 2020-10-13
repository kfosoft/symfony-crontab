<?php

namespace KFOSOFT\Cron\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('crontab');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('tab')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('command')->isRequired()->end()
                            ->scalarNode('expression')->isRequired()->end()
                            ->scalarNode('type')->isRequired()->end()
                            ->arrayNode('params')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
