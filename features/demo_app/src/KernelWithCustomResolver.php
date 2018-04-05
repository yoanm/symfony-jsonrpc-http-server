<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KernelWithCustomResolver extends BaseKernel
{
    public function getCacheDir()
    {
        // Use another cache to not be dependent of other kernel cache
        return $this->getProjectDir().'/var/cache_custom_loader/'.$this->environment;
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        // Load custom method resolver configuration
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/custom_method_resolver'.self::CONFIG_EXTS, 'glob');

        parent::configureContainer($container, $loader);
    }
}
