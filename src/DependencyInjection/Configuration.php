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

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root(JsonRpcHttpServerExtension::EXTENSION_IDENTIFIER);
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('endpoint')
                    ->info('Your custom JSON-RPC http endpoint path')
                    ->treatNullLike(self::DEFAULT_ENDPOINT)
                    ->defaultValue(self::DEFAULT_ENDPOINT)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
