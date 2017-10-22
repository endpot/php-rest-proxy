<?php
/**
 * Created by PhpStorm.
 * User: swxua
 * Date: 2017/10/22
 * Time: 16:22
 */
namespace Example;

use Proxy\Middleware\BasicMiddleware;
use Proxy\Middleware\ExampleMiddleware;
use Proxy\Proxy;

// create proxy instance
$proxy = new Proxy();

// an array contains the most common rest method
$targetUrlArray = [
    'GET' => 'https://httpbin.org/get',
    'POST' => 'https://httpbin.org/post',
    'PUT' => 'https://httpbin.org/put',
    'DELETE' => 'https://httpbin.org/delete',
    'OPTIONS' => 'https://httpbin.org/options',
    'HEAD' => 'https://httpbin.org/head'
];
// create psr7 request based on the global parameters
$request = $proxy->fromGlobals();

// check if the request is in $targetUrlArray
// it does not mean that the script only supports the only methods listed here
$requestMethod = strtoupper($request->getMethod());
$requestMethod = array_key_exists($requestMethod, $targetUrlArray) ? $requestMethod : 'GET';
$targetUrl = $targetUrlArray[$requestMethod];

$response = $proxy->addMiddleware('ExampleMiddleware')
    ->forward($request)
    ->to($targetUrl);

echo $response->getBody();