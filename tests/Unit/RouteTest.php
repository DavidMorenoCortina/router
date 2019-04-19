<?php

namespace Unit;


use DavidMorenoCortina\Router\Exception\InvalidRouteException;
use DavidMorenoCortina\Router\HttpRequest;
use DavidMorenoCortina\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase {
    public function testRouteMatch() {
        $route = new Route(HttpRequest::HTTP_GET, '/login', '', '');

        try {
            $this->assertTrue($route->isMatch('/login'));
        } catch (InvalidRouteException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->assertFalse($route->isMatch('/loginn'));
        } catch (InvalidRouteException $e) {
            $this->assertTrue(true);
        }

        $route = new Route(HttpRequest::HTTP_GET, '/product/{id}', '', '');
        try {
            $this->assertTrue($route->isMatch('/product/1234567890'));
            $params = $route->getParams();
            $this->assertCount(1, $params);
            $this->assertArrayHasKey('id', $params);
            $this->assertTrue(strcmp('1234567890', $params['id']) === 0);
        } catch (InvalidRouteException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->assertTrue($route->isMatch('/product/1'));
            $params = $route->getParams();
            $this->assertCount(1, $params);
            $this->assertArrayHasKey('id', $params);
            $this->assertTrue(strcmp('1', $params['id']) === 0);
        } catch (InvalidRouteException $e) {
            $this->assertTrue(false);
        }

        $route = new Route(HttpRequest::HTTP_GET, '/product/{id}/image', '', '');
        try {
            $this->assertTrue($route->isMatch('/product/1234567890/image'));
            $params = $route->getParams();
            $this->assertCount(1, $params);
            $this->assertArrayHasKey('id', $params);
            $this->assertTrue(strcmp('1234567890', $params['id']) === 0);
        } catch (InvalidRouteException $e) {
            $this->assertTrue(false);
        }

        $route = new Route(HttpRequest::HTTP_GET, '/product/{id}/image/{name}', '', '');
        try {
            $this->assertTrue($route->isMatch('/product/1/image/hello-world'));
            $params = $route->getParams();
            $this->assertCount(2, $params);
            $this->assertArrayHasKey('id', $params);
            $this->assertTrue(strcmp('1', $params['id']) === 0);
            $this->assertArrayHasKey('name', $params);
            $this->assertTrue(strcmp('hello-world', $params['name']) === 0);
        } catch (InvalidRouteException $e) {
            $this->assertTrue(false);
        }
    }

    public function testRouteMethodMatch() {
        $route = new Route(HttpRequest::HTTP_GET, '/login', '', '');

        $this->assertTrue($route->isMethodMatch(HttpRequest::HTTP_GET));
        $this->assertFalse($route->isMethodMatch(HttpRequest::HTTP_POST));

        $route = new Route(HttpRequest::HTTP_POST, '/login', '', '');

        $this->assertTrue($route->isMethodMatch(HttpRequest::HTTP_POST));
        $this->assertFalse($route->isMethodMatch(HttpRequest::HTTP_PUT));
    }

    public function testNotMatch() {
        $route = new Route(HttpRequest::HTTP_GET, '/invalid/{id', '', '');

        try {
            $route->isMatch('/invalid/4321');
        } catch (InvalidRouteException $e) {
            $this->assertTrue(true);
        }
    }
}