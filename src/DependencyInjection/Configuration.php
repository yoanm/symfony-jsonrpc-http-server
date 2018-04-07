<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root(JsonRpcHttpServerExtension::EXTENSION_IDENTIFIER);

        $rootNode
            ->children()
                ->variableNode('method_resolver')
                    ->info('Your custom method resolver service')
                    ->treatNullLike(false)
                    ->defaultFalse()
                ->end()
                ->arrayNode('methods_mapping')
                    ->requiresAtLeastOneElement()
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->beforeNormalization()
                            // Convert simple string to an array with the string as service
                            ->ifString()->then(function ($v) {
                                return ['service' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('service')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('aliases')
                                ->requiresAtLeastOneElement()
                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
