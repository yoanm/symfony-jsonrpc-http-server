<?php
namespace Tests\Functional\Infra\Symfony\DependencyInjection;

use Tests\Common\Infra\Symfony\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionWithConfigParsedTest extends AbstractTestClass
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

    public function testShouldManageCustomResolverFromConfiguration()
    {
        $myCustomResolverServiceId = 'my-custom-resolver';
        $this->setDefinition($myCustomResolverServiceId, $this->createCustomMethodResolverDefinition());

        $this->load(['method_resolver' => $myCustomResolverServiceId]);

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasAlias(
            self::EXPECTED_METHOD_RESOLVER_STUB_SERVICE_ID,
            $myCustomResolverServiceId
        );

        $this->assertEndpointIsUsable();
    }
}
