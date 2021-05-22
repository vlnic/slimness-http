<?php

namespace Vlnic\Slimness\Action;

use Vlnic\Slimness\Client;

/**
 * Class Action
 * @package Vlnic\Slimness\Action
 */
class Action
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $response;

    /**
     *  func action()
     * Action constructor.
     */
    public function __construct(Client $client, string $request, string $responseType = 'text')
    {
        $this->client = $client;
        $this->method = $request;
        $this->response = $responseType;
    }

    public function run()
    {
        return $this->client->{$this->method}()->{$this->response}();
    }
}

/**
 * @param Client $client
 * @param string $request
 * @param string $responseType
 * @return Action
 */
function queueAction(Client $client, string $request, string $responseType = 'text') {
    return new Action($client, $request, $responseType);
}