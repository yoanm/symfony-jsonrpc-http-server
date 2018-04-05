<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

class KernelWithCustomResolver extends BaseKernel
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

        // Load custom method resolver configuration
        $confDir = $this->getProjectDir().'/config';
        $loader->load($confDir.'/custom_method_resolver'.self::CONFIG_EXTS, 'glob');

        parent::configureContainer($container, $loader);
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
