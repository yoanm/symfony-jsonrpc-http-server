<?php
namespace Tests\Functional\Endpoint;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\ServerDoc;
use Yoanm\SymfonyJsonRpcHttpServer\Listener\ServerDocCreatedListener;
use Yoanm\SymfonyJsonRpcHttpServerDoc\Event\ServerDocCreatedEvent;

/**
 * @covers \Yoanm\SymfonyJsonRpcHttpServer\Listener\ServerDocCreatedListener
 */
class ServerDocCreatedListenerTest extends TestCase
{
    /** @var ServerDocCreatedListener */
    private $listener;

    protected function setUp()
    {
        $this->listener = new ServerDocCreatedListener();
    }

    public function testShouldAddSdkErrors()
    {
        $expectedSdkErrorTitleList = [
            'Parse error', //-32700
            'Invalid request', //-32600
            'Method not found', //-32601
            'Params validations error', //-32602
            'Internal error', //-32603
        ];
        $doc = new ServerDoc();
        $event = new ServerDocCreatedEvent($doc);

        $this->listener->appendJsonRpcServerErrorsDoc($event);

        $sdkErrorTitleList = array_map(
            function (ErrorDoc $errorDoc) {
                return $errorDoc->getTitle();
            },
            $doc->getServerErrorList()
        );

        $this->assertSame($expectedSdkErrorTitleList, $sdkErrorTitleList);
    }
}
