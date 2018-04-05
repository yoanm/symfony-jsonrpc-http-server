<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\Infra\Resolver;

use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;

/**
 * Class ServiceNameResolver
 */
class ServiceNameResolver implements ServiceNameResolverInterface
{
    /** @var string[] */
    private $methodMappingList = [];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : string
    {
        return array_key_exists($methodName, $this->methodMappingList)
            ? $this->methodMappingList[$methodName]
            : $methodName
        ;
    }

    /**
     * @param string $methodName
     * @param string $serviceId
     */
    public function addMethodMapping(string $methodName, string $serviceId)
    {
        $this->methodMappingList[$methodName] = $serviceId;
    }
}
