<?php
namespace Tests\Common\Infra\Symfony\DependencyInjection;

use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

/**
 * Class CustomMethodResolverClass
 */
class CustomMethodResolverClass implements MethodResolverInterface
{
    public function resolve(string $methodName)
    {
        // TODO: Implement resolve() method.
    }
}
