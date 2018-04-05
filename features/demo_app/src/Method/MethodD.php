<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;

class MethodD implements JsonRpcMethodInterface
{
    public function validateParams(array $paramList)
    {
    }

    public function apply(array $paramList = null)
    {
        return 'MethodD';
    }
}
