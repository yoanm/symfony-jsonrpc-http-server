<?php
namespace DemoApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseHttpKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

abstract class AbstractKernel extends BaseHttpKernel
{
    use MicroKernelTrait;
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /** @var string|null */
    private $customCacheDir = null;

    public function registerBundles(): iterable
    {
        /** @noinspection PhpIncludeInspection */
        $contents = require $this->getConfigDir().'/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        // Use a specific cache for each kernels
        if (null === $this->customCacheDir) {
            $this->customCacheDir = $this->getProjectDir().'/var/cache/'.$this->environment.'/'.$this->getConfigDirectoryName();
        }

        return $this->customCacheDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log';
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
    {
        return realpath(__DIR__.'/../');
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir().'/'.$this->getConfigDirectoryName();
    }

    /**
     * @param RouteCollectionBuilder|RoutingConfigurator $routes
     */
    protected function configureRoutes($routes)
    {
        $confDir = $this->getConfigDir();
        if ($routes instanceof RoutingConfigurator) {
            $routes->import($confDir . '/routes' . self::CONFIG_EXTS, 'glob');
        } else {
            $routes->import($confDir . '/routes' . self::CONFIG_EXTS, '/', 'glob');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getConfigDir();
        $loader->load($confDir.'/config'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass(): string
    {
        // In order to avoid collisions between kernels use a dedicated name
        return parent::getContainerClass().Container::camelize($this->getConfigDirectoryName());
    }

    abstract public function getConfigDirectoryName() : string;
}
