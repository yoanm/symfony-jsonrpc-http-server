<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

class KernelWithBundleAndCustomLoader extends AbstractKernel
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
        // Load custom method resolver configuration
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/custom_method_resolver'.self::CONFIG_EXTS, 'glob');

        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
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
