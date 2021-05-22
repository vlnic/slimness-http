<?php

namespace Vlnic\Slimness\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Facade
 * @package Vlnic\Slimness\Response
 */
final class Facade
{
    /**
     * @var ResponseTypeInterface[]
     */
    private array $handlers;

    /**
     * @var Facade|null
     */
    private static ?Facade $instance;

    /**
     * Facade constructor.
     */
    private function __construct()
    {
        $this->handlers = [
            JsonResponse::methodName() => JsonResponse::class,
            FileResponse::methodName() => FileResponse::class,
        ];
    }

    private static function getInstance(): Facade
    {
        if (null === self::$instance) {
            self::$instance = new Facade();
        }
        return self::$instance;
    }

    /**
     * @param string $method
     * @param ResponseInterface $response
     * @return mixed
     */
    public static function handleCall(string $method, ResponseInterface $response)
    {
        $instance = self::getInstance();
        return key_exists($method, $instance->handlers) && $instance->handlers[$method]->isType($response)
            ? $instance->handlers[$method]->handle($response)
            : $response;
    }
}