<?php

namespace Vlnic\Slimness\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonResponse
 * @package Vlnic\Slimness\Response
 */
class JsonResponse implements ResponseTypeInterface
{
    /**
     * @param ResponseInterface $r
     * @return bool
     */
    public function isType(ResponseInterface $r): bool
    {
        json_decode($r->getBody());
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param ResponseInterface $r
     * @return array
     */
    public function handle(ResponseInterface $r) : array
    {
        return json_decode(
            $r->getBody(),
            true,
            1024,
            JSON_OBJECT_AS_ARRAY
        ) ?? [];
    }

    /**
     * @return string
     */
    public static function methodName(): string
    {
        return 'jsonResponse';
    }
}