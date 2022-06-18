<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class DefaultKernel extends AbstractKernel
{
    /**
     * {@inheritdoc}
     */
    public function getConfigDirectoryName() : string
    {
        return 'default_config';
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir().'/'.$this->getConfigDirectoryName();
    }
}
