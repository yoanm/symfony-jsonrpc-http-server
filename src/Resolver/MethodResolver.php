<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Resolver;

use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;

/**
 * Class ServiceSubscriberMethodResolver
 */
class MethodResolver implements JsonRpcMethodResolverInterface
{
    /** @var ContainerInterface */
    private $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : ?JsonRpcMethodInterface
    {
        return $this->locator->has($methodName)
            ? $this->locator->get($methodName)
            : null
        ;
    }
}
