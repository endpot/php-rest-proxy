# php-rest-proxy
## About
This project is aimed to ultilize a rest proxy with php basing on Guzzle. Up to now, the script works fine for the most common request method (GET/POST/PUT/DELETE/OPTIONS). When data is submitted with POST method and contain multipart/form-data content, the script would create a new MultipartStream with global constant $_POST and $_FILES, and then transfer to target uri. The script also supports simple middleware within which you can deal with the request/response.
## 关于
该项目基于Guzzle实现请求的转发，包括但不限于GET/POST/PUT/DELETE/OPTIONS等方法。当请求方法为POST，且内容类型为multipart/form-data时，由于PHP的特性，从php://input中获取不到原始的数据，脚本会解析POST和FILES数组生成MultipartStream，再进行转发。该脚本还支持简单的中间件，分别作用于请求转发前后，对请求和响应进行处理。
## Installation
Install using composer:
```
composer require endpot/php-rest-proxy
```
## Example
```php
// create proxy instance
$proxy = new \Proxy\Proxy();

// set target url
$targetUrl = 'http://httpbin.org/';

// create psr7 request based on the global parameters
$request = $proxy->fromGlobals();

// add middlewares
// forward request to target
$response = $proxy->addMiddleware('ExampleMiddleware')
    ->forward($request)
    ->to($targetUrl);
    
// get and show the response
echo $response->getBody();
```
## License
It is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
