<?php
namespace Tests\Common\Mock;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class ConcreteJsonRpcMethod implements JsonRpcMethodInterface
{
    public function apply(array $paramList = null)
    {
        return null;
    }

}
