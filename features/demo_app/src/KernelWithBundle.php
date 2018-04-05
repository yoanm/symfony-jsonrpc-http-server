<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class KernelWithBundle extends AbstractKernel implements CompilerPassInterface
{
    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/fullbundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
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
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
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
