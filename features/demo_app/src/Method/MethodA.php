<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class MethodA implements JsonRpcMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        return 'MethodA';
    }
}
