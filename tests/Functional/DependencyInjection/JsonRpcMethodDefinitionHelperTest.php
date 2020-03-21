<?php
namespace Tests\Functional\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Tests\Common\DependencyInjection\AbstractTestClass;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcMethodDefinitionHelper;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcMethodDefinitionHelper
 */
class JsonRpcMethodDefinitionHelperTest extends AbstractTestClass
{
    /** @var JsonRpcMethodDefinitionHelper */
    private $helper;

    protected function setUp(): void
    {
        $this->helper = new JsonRpcMethodDefinitionHelper();
        parent::setUp();
    }

    public function testShouldManageJsonRpcMethodMappingFromTag()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';
        $methodName2 = 'my-method-name-2';
        $methodName3 = 'my-method-name-3';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method
        $methodService2 = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService2, $methodName2);
        $this->addJsonRpcMethodTag($methodService2, $methodName3);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $methodList = $this->helper->findAndValidateJsonRpcMethodDefinition($this->container);

        $this->assertSame(
            [
                $jsonRpcMethodServiceId => [$methodName],
                $jsonRpcMethodServiceId2 => [$methodName2, $methodName3],
            ],
            $methodList
        );
    }

    public function testShouldThrowAnExceptionIfJsonRpcMethodUsedWithTagDoesNotHaveTheMethodTagAttribute()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method with empty tag attribute
        $methodService2 = $this->createJsonRpcMethodDefinition();
        $methodService2->addTag(self::EXPECTED_JSONRPC_METHOD_TAG);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Service "%s" is taggued as JSON-RPC method but does not have'
                . ' method name defined under "%s" tag attribute key',
                $jsonRpcMethodServiceId2,
                self::EXPECTED_JSONRPC_METHOD_TAG_METHOD_NAME_KEY
            )
        );

        $this->helper->findAndValidateJsonRpcMethodDefinition($this->container);
    }

    public function testShouldThrowAnExceptionIfJsonRpcMethodDoesNotImplementsRightInterface()
    {
        $jsonRpcMethodServiceId = uniqid();
        $jsonRpcMethodServiceId2 = uniqid();
        $methodName = 'my-method-name';

        // A first method
        $methodService = $this->createJsonRpcMethodDefinition();
        $this->addJsonRpcMethodTag($methodService, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId, $methodService);
        // A second method with empty tag attribute
        $methodService2 = $this->createJsonRpcMethodDefinition(\stdClass::class);
        $this->addJsonRpcMethodTag($methodService2, $methodName);
        $this->setDefinition($jsonRpcMethodServiceId2, $methodService2);

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Service "%s" is taggued as JSON-RPC method but does not implement %s',
                $jsonRpcMethodServiceId2,
                JsonRpcMethodInterface::class
            )
        );

        $this->helper->findAndValidateJsonRpcMethodDefinition($this->container);
    }
}
