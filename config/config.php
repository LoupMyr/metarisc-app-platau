<?php

use Dotenv\Dotenv;


require __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->safeLoad();


return [
    'debug'           => filter_var(getenv('DEBUG'), \FILTER_VALIDATE_BOOLEAN),

    \Metarisc\Metarisc::class => [
        'metarisc_url' => getenv('METARISC_URL'),
        'client_id'    => getenv('CLIENT_ID'),
        'client_secret'=> getenv('CLIENT_SECRET'),
    ],
];
