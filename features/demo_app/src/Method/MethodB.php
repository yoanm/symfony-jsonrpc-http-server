<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;

class MethodB implements JsonRpcMethodInterface
{
    public function validateParams(array $paramList)
    {
    }

    public function apply(array $paramList = null)
    {
        return 'MethodB';
    }
}
