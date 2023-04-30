<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_ENDPOINT = '/json-rpc';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(JsonRpcHttpServerExtension::EXTENSION_IDENTIFIER);

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('endpoint')
                    ->info('Your custom JSON-RPC http endpoint path')
                    ->treatNullLike(self::DEFAULT_ENDPOINT)
                    ->defaultValue(self::DEFAULT_ENDPOINT)
                ->end()
                ->arrayNode('debug')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info(
                                'Whether to render debug information on error or not (should NOT be enabled on prod)'
                            )
                            ->defaultFalse()
                        ->end()
                        ->integerNode('max_trace_size')
                            ->info('Max debug trace size')
                            ->min(0)
                            ->defaultValue(10)
                        ->end()
                        ->booleanNode('show_trace_arguments')
                            ->info('Whether to render debug stack trace arguments or not')
                            ->defaultValue(true)
                        ->end()
                        ->booleanNode('simplify_trace_arguments')
                            ->info('Whether to simplify representation of debug stack trace arguments or not')
                            ->defaultValue(true)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
