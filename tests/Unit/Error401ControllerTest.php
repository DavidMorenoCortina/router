<?php

namespace Unit;


use DavidMorenoCortina\DependencyContainer\Container;
use DavidMorenoCortina\Router\Controllers\Error401Controller;
use DavidMorenoCortina\Router\Response\Response;
use PHPUnit\Framework\TestCase;

class Error401ControllerTest extends TestCase {
    public function testResponse() {
        $container = new Container([]);

        $controller = new Error401Controller($container);

        $response = $controller->action();

        $this->assertInstanceOf(Response::class, $response);

        $output = $response->send(true);

        $parts = explode(Response::EOL, $output);

        $this->assertEquals(4, count($parts));

        $this->assertEquals('HTTP/1.1 401', $parts[0]);
    }
}