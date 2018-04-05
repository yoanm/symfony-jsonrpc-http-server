<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class DefaultKernel extends BaseKernel implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        // Mandatory if no Bundle used
        $container->registerExtension(new JsonRpcHttpServerExtension());

        // You can either add in config.yml
        // or load the extension manually with loadFromExtension method
        // -> $container->loadFromExtension($extension->getAlias());

        parent::configureContainer($container, $loader);
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

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        // Use another cache to not be dependent of other kernel cache
        return parent::getCacheDir().'/default';
    }
}
