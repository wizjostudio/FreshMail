<?php
namespace Wizjo\FreshMail;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FreshMail
{
    const API_URL = 'https://api.freshmail.com';
    const API_PREFIX = '/rest';

    const UA_STRING = 'Wizjo/FreshMail 1.0.0';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param ClientInterface $guzzle
     */
    public function __construct($apiKey, $apiSecret, ClientInterface $guzzle = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if (!$guzzle) {
            $guzzle = new Client([
                'base_uri' => self::API_URL,
                'timeout' => 5
            ]);
        }

        $this->guzzle = $guzzle;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @param bool|string $enforceMethod
     *
     * @return Action
     * @throws \InvalidArgumentException|GuzzleException
     */
    public function request($endpoint, array $params = [], $enforceMethod = false)
    {
        if ($enforceMethod !== false) {
            if (is_string($enforceMethod) && in_array($enforceMethod, ['POST', 'GET'], true)) {
                $method = $enforceMethod;
            } else {
                throw new \InvalidArgumentException('Wrong method enforced. Allowed methods to enforce are: GET, POST');
            }
        } else {
            $method = 'GET';
            if ($params) {
                $method = 'POST';
            }
        }

        $this->request = new Request(
            $method,
            $this->buildEndpoint($endpoint),
            $this->getRequestHeaders()
        );

        $this->doRequest($params);

        return new Action($this->request, $this->response);
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    private function buildEndpoint($endpoint)
    {
        if (strpos($endpoint, '/') !== 0) {
            $endpoint = '/' . $endpoint;
        }

        if (strpos($endpoint, self::API_PREFIX) !== 0) {
            $endpoint = self::API_PREFIX . $endpoint;
        }

        return $endpoint;
    }

    /**
     * @return array
     */
    private function getRequestHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'X-Rest-ApiKey' => $this->apiKey,
            'User-Agent' => self::UA_STRING,
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed|ResponseInterface
     * @throws \InvalidArgumentException|GuzzleException
     */
    private function doRequest($params)
    {
        if ($params) {
            $stream = \GuzzleHttp\Psr7\stream_for(json_encode($params));
            $this->request = $this->request->withBody($stream);
        }

        $this->request = $this->request->withAddedHeader(
            'X-Rest-ApiSign',
            sha1($this->apiKey . $this->request->getUri()->getPath() . $this->request->getBody() . $this->apiSecret)
        );

        $this->response = $this->guzzle->send($this->request, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::SYNCHRONOUS => true
        ]);
    }
}
