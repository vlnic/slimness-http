<?php

namespace Vlnic\Slimness\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseTypeIntreface
 * @package Vlnic\Slimness\Response
 */
interface ResponseTypeIntreface
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
}
