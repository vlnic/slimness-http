<?php


namespace Vlnic\Slimness\Response;


use Psr\Http\Message\ResponseInterface;

class XmlResponse implements ResponseTypeIntreface
{

    /**
     * @inheritDoc
     */
    public function isType(ResponseInterface $r): bool
    {
        // TODO: Implement isType() method.
    }

    /**
     * @inheritDoc
     */
    public function handle(ResponseInterface $r)
    {
        // TODO: Implement handle() method.
    }
}