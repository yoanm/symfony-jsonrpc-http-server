<?php
namespace Tests\Functional\Endpoint;

use PHPUnit\Framework\TestCase;
use Yoanm\SymfonyJsonRpcHttpServer\Resolver\ServiceNameResolver;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Resolver\ServiceNameResolver
 */
class ServiceNameResolverTest extends TestCase
{
    /** @var ServiceNameResolver */
    private $resolver;

    protected function setUp()
    {
        $this->resolver = new ServiceNameResolver();
    }

    public function testShouldSaveMappingAndGiveItBack()
    {
        $this->resolver->addMethodMapping('a', 'b');
        $this->resolver->addMethodMapping('c', 'D');
        $this->resolver->addMethodMapping('e', 'blabla');

        $this->assertSame('b', $this->resolver->resolve('a'));
        $this->assertSame('D', $this->resolver->resolve('c'));
        $this->assertSame('blabla', $this->resolver->resolve('e'));
    }

    public function testShouldReturnOriginalValueInCaseNoMappingHaveBeenDefinedForAMethod()
    {
        $this->resolver->addMethodMapping('a', 'b');

        $this->assertSame('abcde', $this->resolver->resolve('abcde'));
    }
}
