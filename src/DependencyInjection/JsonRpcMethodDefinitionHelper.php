<?php
namespace Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

/**
 * Class JsonRpcMethodDefinitionHelper
 */
class JsonRpcMethodDefinitionHelper
{

    private $annotationsEnabled;

    private $reader;

    private const METHOD_ANNOTATION_CLASS = 'Yoanm\\SymfonyJsonRpcHttpServer\\Annotation\\JsonRpcMethod';

    public function __construct(ContainerBuilder $container) {
        $this->annotationsEnabled = $container->has('routing.loader.annotation')
            && class_exists('Doctrine\Common\Annotations\Annotation');
        if ($this->annotationsEnabled) {
            $this->reader = new AnnotationReader();
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    public function findAndValidateJsonRpcMethodDefinition(ContainerBuilder $container) : array
    {
        $definitionList = [];

        $taggedServiceList = $container->findTaggedServiceIds(JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG);

        foreach ($taggedServiceList as $serviceId => $tagAttributeList) {
            $this->validateJsonRpcMethodDefinition($serviceId, $container->getDefinition($serviceId));

            foreach ($tagAttributeList as $tagAttributeKey => $tagAttributeData) {
                try {
                    $this->validateJsonRpcMethodTagAttributes($serviceId, $tagAttributeData);
                    $methodName = $tagAttributeData[JsonRpcHttpServerExtension::JSONRPC_METHOD_TAG_METHOD_NAME_KEY];
                }
                catch (\LogicException $e) {
                    // Annotation routing loader is conditionally added by the framework
                    // bundle; if it's enabled, allow for our own annotation-based
                    // method discovery.
                    if ($this->annotationsEnabled) {
                        $reflection = $container->getReflectionClass($container->getDefinition($serviceId)->getClass());
                        $annotation = $this->reader->getClassAnnotation($reflection, self::METHOD_ANNOTATION_CLASS);
                        if ($annotation) {
                            /** @var \Yoanm\SymfonyJsonRpcHttpServer\Annotation\JsonRpcMethod $annotation */
                            $methodName = $annotation->getName();
                        }
                        else {
                            throw $e;
                        }
                    }
                    else {
                        throw $e;
                    }
                }
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
