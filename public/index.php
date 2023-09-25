<?php

require __DIR__.'/../vendor/autoload.php';

use App\Container;
use App\Http\HttpPipeline;

use function Http\Response\send;

use GuzzleHttp\Psr7\ServerRequest;

$container = Container::initWithDefaults(require __DIR__.'/../config/config.php');

$api = new HttpPipeline($container);

$request  = ServerRequest::fromGlobals();
$response = $api->handle($request);
send($response);
