<?php
namespace Tests\Wizjo\Freshmail;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wizjo\FreshMail\Action;
use Wizjo\FreshMail\FreshMail;

class FreshMailTest extends TestCase
{
    /**
     * Create FreshMail instance using MockHandler for Guzzle
     * http://docs.guzzlephp.org/en/latest/testing.html#mock-handler
     *
     * @param array $expectedResponses
     *
     * @return FreshMail
     */
    private function getFreshMail(array $expectedResponses)
    {
        $guzzle = null;
        if (WIZJO_FM_MOCK_REQUEST) {
            $mock = new MockHandler($expectedResponses);
            $handler = HandlerStack::create($mock);
            $guzzle = new Client(['handler' => $handler]);
        }

        return new FreshMail(WIZJO_FM_API_KEY, WIZJO_FM_API_SECRET, $guzzle);
    }

    public function testDefaultGuzzleInit()
    {
        new FreshMail(WIZJO_FM_API_KEY, WIZJO_FM_API_SECRET);
    }

    public function testRequest()
    {
        $responses = [
            new Response(
                200,
                [],
                json_encode([
                    'status' => 'OK',
                    'data' => 'pong'
                ])
            )
        ];
        $fm = $this->getFreshMail($responses);

        $action = $fm->request('/ping');

        $response = $action->getResponse();
        $request = $action->getRequest();
        $data = $action->getData();

        static::assertEquals('GET', $request->getMethod());

        static::assertFalse($action->hasErrors());
        static::assertEquals(200, $response->getStatusCode());

        static::assertArrayHasKey('status', $data);
        static::assertArrayHasKey('data', $data);

        static::assertEquals('OK', $data['status']);
        static::assertEquals('pong', $data['data']);
    }

    public function testPostRequest()
    {
        $testData = ['test' => 'data'];
        $responses = [
            new Response(
                200,
                [],
                json_encode([
                    'status' => 'OK',
                    'data' => $testData
                ])
            )
        ];
        $fm = $this->getFreshMail($responses);

        $action = $fm->request('/ping', $testData);

        $response = $action->getResponse();
        $request = $action->getRequest();
        $data = $action->getData();

        static::assertEquals('POST', $request->getMethod());

        static::assertFalse($action->hasErrors());
        static::assertEquals(200, $response->getStatusCode());

        static::assertArrayHasKey('status', $data);
        static::assertArrayHasKey('data', $data);

        static::assertEquals('OK', $data['status']);
        static::assertEquals($testData, $data['data']);
    }

    public function testInvalidRequest()
    {
        $responses = [
            new Response(
                200,
                [],
                json_encode([
                    'status' => 'ERROR',
                    'errors' => [
                        [
                            'id' => 1000,
                            'message' => 'Error'
                        ]
                    ]
                ])
            )
        ];
        $fm = $this->getFreshMail($responses);

        $action = $fm->request('/invalid');

        $data = $action->getData();

        static::assertTrue($action->hasErrors());

        static::assertArrayHasKey('status', $data);
        static::assertEquals('ERROR', $data['status']);

        static::assertNotEmpty($action->getErrors());
    }

    public function testHttpMethodEnforce()
    {
        $responses = [
            new Response(200),
            new Response(200),
            new Response(200)
        ];

        $fm = $this->getFreshMail($responses);
        $action = $fm->request('/ping', [], 'GET');
        static::assertEquals('GET', $action->getRequest()->getMethod());

        $action = $fm->request('/ping', [], 'POST');
        static::assertEquals('POST', $action->getRequest()->getMethod());

        $this->expectException(\InvalidArgumentException::class);
        $fm->request('/ping', [], 'BAD');
    }

    public function testEndpointPrefix()
    {
        $responses = [
            new Response(200),
            new Response(200),
            new Response(200),
            new Response(200)
        ];
        $fm = $this->getFreshMail($responses);

        $expected = '/rest/ping';

        $endpoints = [
            'rest/ping',
            '/rest/ping',
            'ping',
            '/ping'
        ];

        foreach ($endpoints as $endpoint) {
            $action = $fm->request($endpoint);
            static::assertEquals($expected, $action->getRequest()->getUri()->getPath());
        }
    }
}
