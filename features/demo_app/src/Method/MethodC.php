<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class MethodC implements JsonRpcMethodInterface
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
        return 'MethodC';
    }
}
