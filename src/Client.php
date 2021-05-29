<?php

namespace Vlnic\Slimness;

use Closure;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use SplFileInfo;
use Throwable;
use Vlnic\Slimness\Builder\ClientInterface as SlimnessClient;
use Vlnic\Slimness\Exceptions\ClientException;
use Vlnic\Slimness\Response\Facade;

/**
 * Class Client
 * @package Vlnic\Slimness
 *
 * @method array jsonResponse()
 * @method SplFileInfo fileResponse()
 */
class Client implements SlimnessClient
{
    /**
     * @var Closure
     */
    private Closure $errorHandler;

    /**
     * @var Closure|null
     */
    private ?Closure $retryState = null;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var int
     */
    private int $retryTimeout;

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var int
     */
    private int $retry = 1;

    /**
     * @var Closure|null
     */
    private ?Closure $authHandler = null;

    /**
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $lastResponse = null;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->ifError(function (int $code, string $body, Throwable $exception) {
            throw new ClientException(
                sprintf(
                    'Request Error: request headers: %s, response code: %s, body: %s',
                    json_encode($this->headers, JSON_UNESCAPED_UNICODE),
                    $code,
                    $body
                ),
                $code,
                $exception
            );
        });
        $this->options = ['allow_redirects' => false, 'timeout' => 30];
        $this->retryTimeout = 2;
    }

    /**
     * @param Closure $handler
     * @param ...$params
     * @return $this
     */
    public function ifError(Closure $handler, ...$params) : self
    {
        $this->errorHandler = $handler;
        $this->addParams = $params;
        return $this;
    }

    /**
     * Метод для того чтобы установить условие при котором запрос будет повторно вызван
     * принимает функцию, которое принимает тело ответа, в последствии должно вернуть значение
     * булевого типа прим.
     * function (Client $body) : bool {
     *   return key_exists('key', $body);
     * }
     *
     * @param Closure $statement
     * @param int $retry
     * @param int|null $timeout
     * @return $this
     */
    public function ifRetry(Closure $statement, int $retry = 1, int $timeout = null) : self
    {
        $this->retryState = $statement;
        $this->retry = $retry;
        $this->retryTimeout = null === $timeout ? $this->retryTimeout : $timeout;
        return $this;
    }

    /**
     * @param array $headers
     * @return Client
     */
    public function addHeaders(array $headers) : self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * @param Closure $handler
     * @return $this
     */
    public function setAuthHandler(Closure $handler) : self
    {
        $this->authHandler = $handler;
        return $this;
    }


    /**
     * Метод для добавления доп параметров запроса
     * параметры должны соответствовать документации
     * @link http://docs.guzzlephp.org/en/v6/request-options.html
     *
     * @param array $options
     * @return Client
     */
    public function addOptions(array $options) : self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @param string $url
     * @param array $data
     * @return array
     */
    public function postJson(string $url, array $data = []) : self
    {
        return $this->request(static::METHOD_POST, new Uri($url), ['json' => $data]);
    }

    /**
     * @param string $url
     * @param array $query
     * @return array
     */
    public function getJson(string $url, array $query = []) : array
    {
        $this->headers = array_merge($this->headers, ['Accept' => 'application/json']);
        $this->request(static::METHOD_GET, new Uri($url), ['query' => $query]);

        return Facade::handleCall('jsonResponse', $this->lastResponse);
    }

    /**
     * @param string $method
     * @param Uri $uri
     * @param array $params
     * @return ResponseInterface
     */
    public function request(string $method, UriInterface $uri, array $params): self
    {
        try {
            $this->lastResponse = $this->buildClient()->request($method, $uri, $params);

            if ($this->isRetry($this)) {
                $this->handleRetry();
                return $this->request($method, $uri, $params);
            }
        } catch (GuzzleException $e) {
            if (401 === $e->getCode() && is_callable($this->authHandler)) {
                call_user_func($this->authHandler);
                $this->authHandler = null;
                return $this->request($method, $uri, $params);
            }
            if ($e instanceof RequestException && $e->hasResponse()) {
                call_user_func($this->errorHandler, $e->getResponse()->getStatusCode(), (string) $e->getResponse()->getBody(), $e);
            }
            call_user_func($this->errorHandler, $e->getCode(), $e->getMessage(), $e);
        }
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function handleJsonResponse(ResponseInterface $response) : array
    {
        return json_decode((string) $response->getBody(), true, 512, JSON_OBJECT_AS_ARRAY) ?? [];
    }


    private function handleRetry() : void
    {
        $this->retry = $this->retry - 1;
        sleep($this->retryTimeout);
    }

    /**
     * @param Client $client
     * @return bool
     */
    private function isRetry(Client $client) : bool
    {
        return null !== $this->retry
            && $this->retry > 0
            && call_user_func($this->retryState, $client);
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * @param $name
     * @return ResponseInterface
     */
    public function __call($name)
    {
        return Facade::handleCall($name, $this->lastResponse);
    }

    /**
     * @return ClientInterface
     */
    protected function buildClient() : ClientInterface
    {
        return new \GuzzleHttp\Client(array_merge(['headers' => $this->headers], $this->options));
    }
}
