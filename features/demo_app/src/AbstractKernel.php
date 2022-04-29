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
    public function getCacheDir(): string
    {
        // Use a specific cache for each kernels
        if (null === $this->customCacheDir) {
            $this->customCacheDir =
                $this->getProjectDir().'/var/cache/'.$this->environment.'/'.$this->getConfigDirectory();
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

    protected function getConfigDir(): string
    {
        return $this->getProjectDir().'/'.$this->getConfigDirectory();
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass(): string
    {
        // In order to avoid collisions between kernels use a dedicated name
        return parent::getContainerClass().Container::camelize($this->getConfigDirectory());
    }

    abstract public function getConfigDirectory() : string;
}
