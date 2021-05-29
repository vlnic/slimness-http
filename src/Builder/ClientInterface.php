<?php


namespace Vlnic\Slimness\Builder;

use Psr\Http\Message\UriInterface;

/**
 * Class ClientInterface
 * @package Vlnic\Slimness\Builder
 */
interface ClientInterface
{
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_GET = 'GET';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_PATCH = 'PATCH';

    public function request(string $method, UriInterface $uri, array $params);
}