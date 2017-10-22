<?php
/**
 * Created by PhpStorm.
 * User: swxua
 * Date: 2017/10/22
 * Time: 8:47
 */

namespace Proxy;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Proxy\Middleware\BasicMiddleware;
use Psr\Http\Message\RequestInterface;

class Proxy
{
    private $request;

    private $response;

    private $options;

    private $proxyClient;

    private $handleStack;

    public function __construct()
    {
        // timeout if a server does not return response in 3 seconds.
        //$this->setOption('timeout', 3.0);
        //$this->setOption('http_errors', false);

        $this->handleStack = HandlerStack::create(new CurlHandler());
    }

    public function forward(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    public function to($target = '')
    {
        // modify the target uri and deal with the multipart/form-data
        $this->request = $this->setUri($this->request, $target);
        $this->request = $this->setMultipart($this->request);

        // Create a guzzle client instance with handle stack
        $this->proxyClient = new Client(['handler' => $this->handleStack]);

        // Forward the request and get the response.
        $this->response = $this->proxyClient->send($this->request, $this->options);

        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function fromGlobals()
    {
        return ServerRequest::fromGlobals();
    }

    public function addMiddleware($middlewareName)
    {
        $middlewareName = 'Proxy\Middleware\\' . $middlewareName;
        if (class_exists($middlewareName)) {
            $middleware = new $middlewareName();
            $this->handleStack->push(Middleware::mapRequest($middleware->before()));
            $this->handleStack->push(Middleware::mapResponse($middleware->after()));
        }
        return $this;
    }

    private function setUri(RequestInterface $request, $target = '')
    {
        if (!empty($target)) {
            $target = new Uri($target);

            // Overwrite target scheme and host.
            $uri = $request->getUri()
                ->withScheme($target->getScheme())
                ->withHost($target->getHost())
                ->withQuery($target->getQuery());

            // Check for custom port.
            if ($port = $target->getPort()) {
                $uri = $uri->withPort($port);
            }

            // Check for subdirectory.
            if ($path = $target->getPath()) {
                $uri = $uri->withPath(rtrim($path, '/'));
            }

            $request = $request->withUri($uri);
        }

        return $request;
    }

    private function setMultipart(RequestInterface $request)
    {
        // if Method is POST and Content-Type is multipart/form-data
        // Make new stream with $_POST and $_FILES
        $contentType = $request->getHeader('Content-Type');
        $contentType = empty($contentType) ? $contentType : $contentType[0];
        if (strpos($contentType, 'multipart/form-data') !== false && $request->getMethod() == 'POST') {
            // Make multipart stream
            $elements = array();
            foreach ($_POST as $key => $value) {
                $tmp = array();
                $tmp['name'] = $key;
                $tmp['contents'] = $value;
                array_push($elements, $tmp);
            }

            foreach ($_FILES as $key => $value) {
                $tmp = array();
                $tmp['name'] = $key;
                $tmp['filename'] = $value['name'];
                $tmp['headers']['Content-Type'] = $value['type'];
                $tmp['headers']['Content-Length'] = $value['size'];
                $tmp['contents'] = fopen($value['tmp_name'], 'r');
                array_push($elements, $tmp);
            }
            $body = new Psr7\MultipartStream($elements);
            $request = $request->withBody($body)
                ->withHeader('Content-Type', 'multipart/form-data; Boundary=' . $body->getBoundary());
        }
        return $request;
    }
}
