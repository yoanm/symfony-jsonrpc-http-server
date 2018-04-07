<?php
namespace Tests\Technical\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\Configuration;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\DependencyInjection\Configuration
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
        $this->assertProcessedConfigurationEquals([[]], ['method_resolver'=> false], 'method_resolver');
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
            ],
            'method_resolver'
        );
    }

    public function testShouldManageSimpleMethodMapping()
    {
        $expectedMethodName = 'my-method-name';
        $expectedService = 'my-service-id';

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'methods_mapping' => [
                        $expectedMethodName => $expectedService
                    ]
                ]
            ],
            [
                'methods_mapping'=> [
                    $expectedMethodName => [
                        'service' => $expectedService,
                        'aliases' => []
                    ]
                ]
            ],
            'methods_mapping'
        );
    }

    public function testShouldManageFullyConfiguredMapping()
    {
        $expectedMethodName = 'my-method-name';
        $expectedAlias1 = 'my-method-name-alias-1';
        $expectedAlias2 = 'my-method-name-alias-2';
        $expectedService = 'my-service-id';

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'methods_mapping' => [
                        $expectedMethodName => [
                            'service' => $expectedService,
                            'aliases' => [$expectedAlias1, $expectedAlias2]
                        ]
                    ]
                ]
            ],
            [
                'methods_mapping'=> [
                    $expectedMethodName => [
                        'service' => $expectedService,
                        'aliases' => [$expectedAlias1, $expectedAlias2]
                    ]
                ]
            ],
            'methods_mapping'
        );
    }

    public function testShouldManageASimpleAliasDefinition()
    {
        $expectedMethodName = 'my-method-name';
        $expectedAlias = 'my-method-name-alias';
        $expectedService = 'my-service-id';

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'methods_mapping' => [
                        $expectedMethodName => [
                            'service' => $expectedService,
                            'aliases' => $expectedAlias,
                        ]
                    ]
                ]
            ],
            [
                'methods_mapping'=> [
                    $expectedMethodName => [
                        'service' => $expectedService,
                        'aliases' => [$expectedAlias]
                    ]
                ]
            ],
            'methods_mapping'
        );
    }
}
