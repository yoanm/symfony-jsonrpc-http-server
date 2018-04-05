<?php
namespace DemoApp\Resolver;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

class JsonRpcMethodResolver implements MethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList;

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName)
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
    public function addMethod(JsonRpcMethodInterface $method, string $methodName)
    {
        $this->methodList[$methodName] = $method;
    }
}
