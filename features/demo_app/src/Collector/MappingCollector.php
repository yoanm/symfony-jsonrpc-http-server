<?php
namespace DemoApp\Collector;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodAwareInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class MappingCollector implements JsonRpcMethodAwareInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $mappingList = [];

    public function addJsonRpcMethod(string $methodName, JsonRpcMethodInterface $method): void
    {
        $this->mappingList[$methodName] = $method;
    }

    /**
     * @return JsonRpcMethodInterface[]
     */
    public function getMappingList() : array
    {
        return $this->mappingList;
    }
}
