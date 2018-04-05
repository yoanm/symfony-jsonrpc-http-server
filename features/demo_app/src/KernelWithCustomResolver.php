<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class KernelWithCustomResolver extends AbstractKernel
{
    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        /**** Add extension **/
        $container->registerExtension($extension = new JsonRpcHttpServerExtension());
        $container->loadFromExtension($extension->getAlias());

        // Load custom method resolver configuration
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/custom_method_resolver'.self::CONFIG_EXTS, 'glob');

        /**** Continue as usual **/
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/config';
        $routes->import($confDir.'/routes'.self::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        // Use another cache to not be dependent of other kernel cache
        return parent::getCacheDir().'/custom';
    }
}
