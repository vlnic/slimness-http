<?php

use Vlnic\Slimness\Client;

if (! function_exists('client')) {
    /**
     * @return Client
     */
    function client(): Client
    {
        return new Client();
    }
}
