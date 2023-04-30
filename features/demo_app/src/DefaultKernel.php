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
}
