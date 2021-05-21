<?php


namespace Vlnic\Slimness\Response;

use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

/**
 * Class FileResponse
 * @package Vlnic\Slimness\Response
 */
class FileResponse implements ResponseTypeIntreface
{
    /**
     * @param ResponseInterface $r
     * @return bool
     */
    public function isType(ResponseInterface $r): bool
    {
        return in_array($r->getHeaderLine('Content-Type'), $this->getMimeTypes());
    }

    /**
     * @param ResponseInterface $r
     * @return SplFileInfo
     */
    public function handle(ResponseInterface $r) : SplFileInfo
    {
        $filename = tempnam(sys_get_temp_dir(), 'download');
        file_put_contents($filename, $r->getBody());
        return new SplFileInfo($filename);
    }

    /**
     * @return string[]
     */
    private function getMimeTypes() : array
    {
        return [
            'application/pdf',
            'text/plain',
            'text/html',
            'application/octet-stream',
            'application/zip',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',
            'image/gif',
            'image/png',
            'image/jpg',
            'text/csv',
            'application/json',
            'text/json',
        ];
    }
}