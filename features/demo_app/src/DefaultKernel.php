<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class DefaultKernel extends AbstractKernel
{
    public function registerBundles(): iterable
    {
        /** @noinspection PhpIncludeInspection */
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
    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ) {
        $confDir = $this->getConfigDir();
        $container->parameters()->set('container.dumper.inline_class_loader', true);
        $container->import($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $container->import($confDir.'/services'.self::CONFIG_EXTS, 'glob');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import($this->getConfigDir().'/routes'.self::CONFIG_EXTS);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigDirectory() : string
    {
        return 'default_config';
    }
}
