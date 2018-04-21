<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;

class MethodA implements JsonRpcMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateParams(array $paramList) : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        return 'MethodA';
    }
}
