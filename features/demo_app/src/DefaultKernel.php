<?php
namespace DemoApp;

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
