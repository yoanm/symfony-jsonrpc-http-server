<?php
namespace Tests\Technical\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\Configuration;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionTest extends AbstractTestClass
{
    public function testShouldHaveADefaultEndpointConfigured()
    {
        $this->load();

        // Assert custom resolver is an alias of the stub
        $this->assertContainerBuilderHasParameter(
            self::EXPECTED_HTTP_ENDPOINT_PATH_CONTAINER_PARAM,
            Configuration::DEFAULT_ENDPOINT
        );

        $this->assertEndpointIsUsable();
    }
}
