<?php
namespace DemoApp;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    public function getCacheDir()
    {

        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // You can manually inject method mapping if you want, use ServiceNameResolver::addMethodMapping method
        $container->getDefinition(JsonRpcHttpServerExtension::SERVICE_NAME_RESOLVER_SERVICE_NAME)
            ->addMethodCall('addMethodMapping', ['getDummy', 'jsonrpc.method.c'])
            ->addMethodCall('addMethodMapping', ['getAnotherDummy', 'jsonrpc.method.d'])
        ;
    }
}
