<?php
namespace Tests\Common\Mock;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;

class ConcreteResolver implements JsonRpcMethodResolverInterface
{
    public function resolve(string $methodName): ?JsonRpcMethodInterface
    {
        return null;
    }
}
