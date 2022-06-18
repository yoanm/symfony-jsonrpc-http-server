<?php
namespace DemoApp;

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
