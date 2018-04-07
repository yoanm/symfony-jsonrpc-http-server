<?php
namespace Tests\Technical\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\LogicException;
use Tests\Common\DependencyInjection\AbstractTestClass;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\JsonRpcHttpServerExtension
 */
class JsonRpcHttpServerExtensionTest extends AbstractTestClass
{
    public function testShouldThrowAnExceptionIfMoreThanOneMethodResolverHaveTheMethodResolverTag()
    {
        // A two custom resolver with tag
        $serviceId1 = uniqid();
        $serviceId2 = uniqid();
        $this->setDefinition($serviceId1, $this->createCustomMethodResolverDefinition());
        $this->setDefinition($serviceId2, $this->createCustomMethodResolverDefinition());

        $this->expectException(LogicException::class);
        // Check that exception is for the second method
        $this->expectExceptionMessage(
            sprintf(
                'Only one method resolver could be defined, found following services : %s',
                implode(', ', [$serviceId1, $serviceId2])
            )
        );

        $this->load();
    }
}
