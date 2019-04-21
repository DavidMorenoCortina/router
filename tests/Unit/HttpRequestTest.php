<?php

namespace Unit;


use DavidMorenoCortina\Router\Exception\CLIRequestException;
use DavidMorenoCortina\Router\HttpRequest;
use PHPUnit\Framework\TestCase;

class HttpRequestTest extends TestCase {
    public function testCliRequest() {
        $_SERVER['REQUEST_METHOD'] = null;
        $_SERVER['REQUEST_URI'] = null;
        unset($_SERVER['QUERY_STRING']);

        $request = new HttpRequest();

        $this->assertEquals(HttpRequest::HTTP_GET, $request->getMethod());
        try {
            $request->getRoutePath();
        } catch (CLIRequestException $e) {
            $this->assertTrue(true);
        }
        $this->assertIsArray($request->getBody());

        $this->assertEmpty($request->getBody());
    }

    public function testGETRequest() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        unset($_SERVER['QUERY_STRING']);

        $request = new HttpRequest();

        $this->assertEquals(HttpRequest::HTTP_GET, $request->getMethod());
        try {
            $routePath = $request->getRoutePath();
            $this->assertEquals('/', $routePath);
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
        $this->assertIsArray($request->getBody());

        $this->assertEmpty($request->getBody());
    }

    public function testPostRequest() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/Say-Hello';
        unset($_SERVER['QUERY_STRING']);
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $msg = 'hello world';

        $inputStream = __DIR__ . '/test.txt';
        file_put_contents($inputStream, json_encode(['param' => $msg]));

        $request = new HttpRequest($inputStream);

        $this->assertEquals(HttpRequest::HTTP_POST, $request->getMethod());
        try {
            $routePath = $request->getRoutePath();
            $this->assertEquals('/say-hello', $routePath);
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
        $this->assertIsArray($request->getBody());

        $body = $request->getBody();
        $this->assertArrayHasKey('param', $body);
        $this->assertEquals($msg, $body['param']);
    }

    public function testPostRequestAlternateHeader() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/Say-Hello?q=1';
        $_SERVER['QUERY_STRING'] = 'q=1';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

        $msg = 'hello world';

        $inputStream = __DIR__ . '/test.txt';
        file_put_contents($inputStream, json_encode(['param' => $msg]));

        $request = new HttpRequest($inputStream);

        $this->assertEquals(HttpRequest::HTTP_POST, $request->getMethod());
        try {
            $routePath = $request->getRoutePath();
            $this->assertEquals('/say-hello', $routePath);
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
        $this->assertIsArray($request->getBody());

        $body = $request->getBody();
        $this->assertArrayHasKey('param', $body);
        $this->assertEquals($msg, $body['param']);
    }

    public function testPutRequest() {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $msg = 'hello world';

        $inputStream = __DIR__ . '/test.txt';
        file_put_contents($inputStream, json_encode(['param' => $msg]));

        $request = new HttpRequest($inputStream);

        $this->assertEquals(HttpRequest::HTTP_PUT, $request->getMethod());
        try {
            $routePath = $request->getRoutePath();
            $this->assertEquals('/', $routePath);
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
        $this->assertIsArray($request->getBody());

        $body = $request->getBody();
        $this->assertArrayHasKey('param', $body);
        $this->assertEquals($msg, $body['param']);
    }

    public function testGetRequestToken() {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['REQUEST_URI'] = '/?';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_AUTHORIZATION'] = 'bearer xxx';

        $request = new HttpRequest();

        $this->assertEquals('xxx', $request->getToken());
    }

    public function testGetRequestInvalidToken() {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['REQUEST_URI'] = '/';
        unset($_SERVER['QUERY_STRING']);
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_AUTHORIZATION'] = 'berer xxx';

        $request = new HttpRequest();

        $this->assertEquals('', $request->getToken());
    }
}