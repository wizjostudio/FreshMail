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

    public function testCreateClass()
    {
        new FreshMail(WIZJO_FM_API_KEY, WIZJO_FM_API_SECRET);
    }

    public function testCreateClassWithCustomGuzzle()
    {
        $guzzle = new Client();
        new FreshMail(WIZJO_FM_API_KEY, WIZJO_FM_API_SECRET, $guzzle);
    }

    public function testRequest()
    {
        $fm = $this->getFreshMail([new Response()]);
        $action = $fm->request('/ping');

        static::assertInstanceOf(Action::class, $action);
    }

    public function testRequestWithAttributes()
    {
        $fm = $this->getFreshMail([new Response()]);
        $action = $fm->request('/ping', ['test' => 'data']);

        static::assertInstanceOf(Action::class, $action);
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

        static::assertInstanceOf(Action::class, $action);

        $action = $fm->request('/ping', [], 'POST');

        static::assertInstanceOf(Action::class, $action);

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

        $endpoints = [
            'rest/ping',
            '/rest/ping',
            'ping',
            '/ping'
        ];

        foreach ($endpoints as $endpoint) {
            $action = $fm->request($endpoint);
            static::assertInstanceOf(Action::class, $action);
        }
    }
}
