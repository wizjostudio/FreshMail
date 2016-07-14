<?php
namespace Tests\Wizjo\Freshmail;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wizjo\FreshMail\Action;

class ActionTest extends TestCase
{
    const TEST_URI = 'http://localhost/';

    public function testCreateClass()
    {
        $request = new Request('GET', self::TEST_URI);
        $response = new Response(200);

        $action = new Action($request, $response);
        static::assertInstanceOf(Action::class, $action);
    }

    public function testGetRequest()
    {
        $request = new Request('GET', self::TEST_URI);
        $response = new Response();

        $action = new Action($request, $response);

        static::assertInstanceOf(RequestInterface::class, $action->getRequest());
        static::assertEquals('GET', $action->getRequest()->getMethod());
    }

    public function testGetResponse()
    {
        $request = new Request('GET', self::TEST_URI);
        $response = new Response(200);

        $action = new Action($request, $response);

        static::assertInstanceOf(ResponseInterface::class, $action->getResponse());
        static::assertEquals(200, $action->getResponse()->getStatusCode());
    }

    public function testGetData()
    {
        $responseData = ['status' => 'OK', 'data' => 'pong'];

        $request = new Request('GET', self::TEST_URI);
        $response = new Response(
            200,
            [],
            json_encode($responseData)
        );

        $action = new Action($request, $response);

        static::assertEquals($responseData, $action->getData());
    }

    public function testApiResponseError()
    {
        $responseData = [
            'status' => 'ERROR',
            'errors' => [
                ['code' => 1000, 'message' => 'Error']
            ]
        ];

        $request = new Request('GET', self::TEST_URI);
        $response = new Response(
            200,
            [],
            json_encode($responseData)
        );

        $action = new Action($request, $response);

        static::assertTrue($action->hasErrors());
        static::assertEquals($responseData['errors'], $action->getErrors());
    }
}
