<?php
namespace DemoApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel as BaseHttpKernel;

abstract class AbstractKernel extends BaseHttpKernel
{
    use MicroKernelTrait;
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /** @var string|null */
    private $customCacheDir = null;

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        // Use a specific cache for each kernels
        if (null === $this->customCacheDir) {
            $this->customCacheDir = $this->getProjectDir().'/var/cache/'.$this->environment.'/'.$this->getConfigDirectory();
        }

        return $this->customCacheDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir()
    {
        return realpath(__DIR__.'/../');
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        // In order to avoid collisions between kernels use a dedicated name
        return parent::getContainerClass().Container::camelize($this->getConfigDirectory());
    }

    abstract public function getConfigDirectory() : string;
}
