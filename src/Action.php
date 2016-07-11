<?php
namespace Wizjo\FreshMail;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Action
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * @var array
     */
    private $errors = [];

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->response->getBody()->rewind();
        $this->loadData();
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Loads the data and info about errors
     */
    private function loadData()
    {
        $json = (string) $this->response->getBody()->getContents();
        $data = json_decode($json, true);

        if ($data) {
            $this->data = $data;

            if (array_key_exists('errors', $this->data) && is_array($this->data['errors'])) {
                $this->hasErrors = true;
                $this->errors = $this->data['errors'];
            }
        }
    }
}
