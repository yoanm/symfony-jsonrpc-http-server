<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class DefaultKernel extends AbstractKernel implements CompilerPassInterface
{
    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/'.$this->getConfigDirectory().'/bundles.php';
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
        /**** Add extension **/
        $container->registerExtension($extension = new JsonRpcHttpServerExtension());
        $container->loadFromExtension($extension->getAlias());

        /**** Continue as usual **/
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/'.$this->getConfigDirectory();
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
            ->addMethodCall('addMethodMapping', ['defaultGetDummy', 'jsonrpc.method.c'])
            ->addMethodCall('addMethodMapping', ['defaultGetAnotherDummy', 'jsonrpc.method.d'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/'.$this->getConfigDirectory();
        $routes->import($confDir.'/routes'.self::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigDirectory() : string
    {
        return 'default_config';
    }
}
