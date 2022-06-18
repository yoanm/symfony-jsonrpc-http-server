<?php
namespace DemoApp;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class KernelWithMappingCollectorListener extends AbstractKernel
{
    /**
     * {@inheritdoc}
     */
    public function getConfigDirectoryName() : string
    {
        return 'mapping_collector_config';
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir().'/'.$this->getConfigDirectoryName();
    }
}
