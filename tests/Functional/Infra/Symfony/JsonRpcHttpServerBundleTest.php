<?php
namespace Tests\Functional\Infra\Symfony\DependencyInjection;

use Tests\Common\Infra\Symfony\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\JsonRpcHttpServerBundle;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\JsonRpcHttpServerBundle
 */
class JsonRpcHttpServerBundleTest extends AbstractTestClass
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            (new JsonRpcHttpServerBundle())->getContainerExtension()
        ];
    }

    public function testShouldManageConfigurationByDefault()
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
