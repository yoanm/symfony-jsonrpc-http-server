<?php
namespace Tests\Functional\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\MethodResolver;

/**
 * @covers Yoanm\SymfonyJsonRpcHttpServer\Resolver\MethodResolver
 */
class MethodResolverTest extends TestCase
{
    use ProphecyTrait;

    /** @var MethodResolver */
    private $resolver;

    /** @var ContainerInterface|ObjectProphecy */
    private $locator;

    protected function setUp(): void
    {
        $this->locator = $this->prophesize(ContainerInterface::class);

        $this->resolver = new MethodResolver(
            $this->locator->reveal()
        );
    }

    public function testShouldReturnJsonRpcMethodIfRegistered()
    {
        $methodName = 'method_name';

        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->locator->has($methodName)
            ->willReturn(true)
            ->shouldBeCalled()
        ;

        $this->locator->get($methodName)
            ->willReturn($method->reveal())
            ->shouldBeCalled()
        ;

        $this->assertSame(
            $method->reveal(),
            $this->resolver->resolve($methodName)
        );
    }

    public function testShouldReturnNullIfNoMethodRegisteredForGivenName()
    {
        $methodName = 'method_name';

        $this->locator->has($methodName)
            ->willReturn(false)
            ->shouldBeCalled()
        ;

        $this->assertNull(
            $this->resolver->resolve($methodName)
        );
    }
}
