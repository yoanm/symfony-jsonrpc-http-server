<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

/**
 * Class JsonRpcMethodDefinitionHelper
 */
class JsonRpcMethodDefinitionHelper
{
    /**
     * @param ContainerBuilder $container
     *
     * @return array
     * @throws \ReflectionException
     */
    public function findAndValidateJsonRpcMethodDefinition(ContainerBuilder $container) : array
    {
        $definitionList = [];

        $taggedServiceList = $container->findTaggedServiceIds(JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG);

        foreach ($taggedServiceList as $serviceId => $tagAttributeList) {
            $this->validateJsonRpcMethodDefinition($serviceId, $container->getDefinition($serviceId));

            foreach ($tagAttributeList as $tagAttributeKey => $tagAttributeData) {
                $this->validateJsonRpcMethodTagAttributes($serviceId, $tagAttributeData);
                $methodName = $tagAttributeData[JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG_METHOD_NAME_KEY];
                $definitionList[$serviceId][] = $methodName;
            }
        }

        return $definitionList;
    }

    /**
     * @param string $serviceId
     * @param array  $tagAttributeData
     */
    private function validateJsonRpcMethodTagAttributes(string $serviceId, array $tagAttributeData) : void
    {
        if (!isset($tagAttributeData[JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG_METHOD_NAME_KEY])) {
            throw new LogicException(sprintf(
                'Service "%s" is taggued as JSON-RPC method but does not have'
                . ' method name defined under "%s" tag attribute key',
                $serviceId,
                JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG_METHOD_NAME_KEY
            ));
        }
    }

    /**
     * @param string     $serviceId
     * @param Definition $definition
     *
     * @throws \ReflectionException
     * @throws \LogicException      In case definition is not valid
     */
    private function validateJsonRpcMethodDefinition(string $serviceId, Definition $definition) : void
    {
        if (!in_array(JsonRpcMethodInterface::class, class_implements($definition->getClass()))) {
            throw new LogicException(sprintf(
                'Service "%s" is taggued as JSON-RPC method but does not implement %s',
                $serviceId,
                JsonRpcMethodInterface::class
            ));
        }
    }
}
