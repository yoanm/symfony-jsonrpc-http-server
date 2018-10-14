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

    public function testShouldTreatNullAsDefaultEndpoint()
    {
        $this->assertProcessedConfigurationEquals(
            [['endpoint'=> null]],
            ['endpoint'=> Configuration::DEFAULT_ENDPOINT],
            'endpoint'
        );
    }
}
