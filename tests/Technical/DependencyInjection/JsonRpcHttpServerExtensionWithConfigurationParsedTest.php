<?php
namespace Tests\Technical\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionWithConfigurationParsedTest extends AbstractTestClass
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new JsonRpcHttpServerExtension(true)
        ];
    }

    public function testShouldNormalizeExternalServiceIdStringPassedForMethodResolver()
    {
        $myCustomResolverServiceId = 'my-custom-resolver';
        $this->setDefinition($myCustomResolverServiceId, $this->createCustomMethodResolverDefinition());

        $this->load(['method_resolver' => '@'.$myCustomResolverServiceId]);

        $this->assertEndpointIsUsable();
    }

    public function testShouldNormalizeExternalServiceIdStringPassedForMethodMapping()
    {
        $jsonRpcMethodServiceId = uniqid();

        $methodService = $this->createJsonRpcMethodDefinition();
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);


        $this->load([
            'methods_mapping' => [
                'a-method' => '@'.$jsonRpcMethodServiceId
            ]
        ]);

        $this->assertJsonRpcMethodServiceIsAvailable($jsonRpcMethodServiceId);

        $this->assertEndpointIsUsable();
    }
}
