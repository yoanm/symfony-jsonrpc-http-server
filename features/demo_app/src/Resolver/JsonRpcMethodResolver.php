<?php
namespace DemoApp\Resolver;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodAwareInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface as BasResolverInterface;

class JsonRpcMethodResolver implements BasResolverInterface, JsonRpcMethodAwareInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList;

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : ?JsonRpcMethodInterface
    {
        if (!array_key_exists($methodName, $this->methodList)) {
            return null;
        }

        return $this->methodList[$methodName];
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param string $methodName
     */
    public function addJsonRpcMethod(string $methodName, JsonRpcMethodInterface $method) : void
    {
        $this->methodList[$methodName] = $method;
    }
}
