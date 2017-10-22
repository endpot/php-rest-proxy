<?php
/**
 * Created by PhpStorm.
 * User: swxua
 * Date: 2017/10/22
 * Time: 10:50
 */
namespace Proxy\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BasicMiddleware
{
    final public function before()
    {
        return function (RequestInterface $request) {
            return $this->beforeAction($request);
        };
    }

    final public function after()
    {
        return function (ResponseInterface $response) {
            return $this->afterAction($response);
        };
    }

    public function beforeAction(RequestInterface $request)
    {
        return $request;
    }

    public function afterAction(ResponseInterface $response)
    {
        return $response;
    }
}
