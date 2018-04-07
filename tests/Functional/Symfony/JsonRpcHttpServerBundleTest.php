<?php
namespace Tests\Functional\Symfony\DependencyInjection;

use Tests\Common\Symfony\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\Symfony\JsonRpcHttpServerBundle;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Symfony\JsonRpcHttpServerBundle
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
