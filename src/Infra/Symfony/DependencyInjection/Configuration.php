<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_JSONRPC_ENDPOINT = '/json-rpc';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(JsonRpcHttpServerExtension::EXTENSION_IDENTIFIER);

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('method_resolver')
                    ->info('Your custom method resolver service')
                    ->treatNullLike(false)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
