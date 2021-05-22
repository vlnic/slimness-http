<?php

namespace Vlnic\Slimness\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseTypeInterface
 * @package Vlnic\Slimness\Response
 */
interface ResponseTypeInterface
{
    /**
     * @param ResponseInterface $r
     * @return bool
     */
    public function isType(ResponseInterface $r) : bool;

    /**
     * @param ResponseInterface $r
     * @return mixed
     */
    public function handle(ResponseInterface $r);

    /**
     * @return string
     */
    public static function methodName() : string;
}
