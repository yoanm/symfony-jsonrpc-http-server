<?php
namespace Tests\Technical\Infra\Symfony\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\Configuration;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Infra\Symfony\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }


    public function testShouldHaveFalseAsCustomMethodResolverByDefault()
    {
        $this->assertProcessedConfigurationEquals([[]], ['method_resolver'=> false]);
    }

    public function testShouldReturnCustomMethodResolverServiceIfDefined()
    {
        $expectedCustomMethodResolverService = 'my-service';

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'method_resolver' => $expectedCustomMethodResolverService
                ]
            ],
            [
                'method_resolver'=> $expectedCustomMethodResolverService
            ]
        );
    }
}
