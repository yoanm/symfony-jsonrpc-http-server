<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class MethodD implements JsonRpcMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        return 'MethodD';
    }
}
