<?php
/**
 * Created by PhpStorm.
 * User: swxua
 * Date: 2017/10/22
 * Time: 16:22
 */
require "vendor/autoload.php";

// create proxy instance
$proxy = new \Proxy\Proxy();

// an array contains the most common rest method
$targetUrlArray = [
    'GET' => 'http://httpbin.org/get',
    'POST' => 'http://httpbin.org/post',
    'PUT' => 'http://httpbin.org/put',
    'DELETE' => 'http://httpbin.org/delete',
    'OPTIONS' => 'http://httpbin.org/options',
    'HEAD' => 'http://httpbin.org/head'
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
