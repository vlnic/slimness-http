<?php

use Vlnic\Slimness\Client;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @return Client
 */
function client() : Client
{
    return new Client();
}
