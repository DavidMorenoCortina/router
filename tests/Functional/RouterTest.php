<?php

namespace Functional;


use DavidMorenoCortina\DependencyContainer\Container;
use DavidMorenoCortina\JWT\JWT;
use DavidMorenoCortina\Router\Controllers\Error401Controller;
use DavidMorenoCortina\Router\Controllers\Error404Controller;
use DavidMorenoCortina\Router\Controllers\Error500Controller;
use DavidMorenoCortina\Router\Exception\CLIRequestException;
use DavidMorenoCortina\Router\Route;
use DavidMorenoCortina\Router\Router;
use PDO;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {
    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var Router $router
     */
    protected $router;

    protected function setUp() :void {
        parent::setUp();

        $this->container = new Container([
            'inputStream' => __DIR__ . '/test.txt',
            'jwtKeyName' => 'tests'
        ]);

        $this->container['pdo'] = function(){
            $settings = require __DIR__ . '/../../phpunit-settings.php';

            $dsn = 'mysql:host=' . $settings['db']['host'] . ';port=' . $settings['db']['port'] . ';dbname=' . $settings['db']['dbName'];
            return new PDO($dsn, $settings['db']['user'], $settings['db']['password'], [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
        };

        $router = new Router($this->container);

        $router->registerDependencies();

        $router->get('/', 'Home', 'action');
        $router->get('/products', 'Products', 'action');
        $router->post('/product/{id}', 'Product', 'action', true);
        $router->get('/users', 'Users', 'action', true);
        $router->get('/user/{id}', 'Users', 'action');

        $this->router = $router;
    }

    public function testRouteMatchGet() {
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['REQUEST_URI'] = '/';

        try {
            $route = $this->router->match();
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals('Home', $route->getClassName());
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
    }

    public function testRouteMatchPostSecureFails() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/product/4321';

        try {
            $route = $this->router->match();
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals(Error401Controller::class, $route->getClassName());
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
    }

    public function testRouteMatchPostSecure() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/product/4321';

        /** @var JWT $jwt */
        $jwt = $this->container['jwt'];
        $token = $jwt->encode('tests', 'demo', 'test', time()+800);
        $this->assertNotEmpty($token);
        $_SERVER['HTTP_AUTHORIZATION'] = 'bearer ' . $token;

        try {
            $route = $this->router->match();
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals('Product', $route->getClassName());
            $this->assertGreaterThan(0, $route->getUserId());
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
    }

    public function testRouteMatchWrongMethod() {
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['REQUEST_URI'] = '/product/4321';

        try {
            $route = $this->router->match();
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals(Error404Controller::class, $route->getClassName());
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
    }

    public function testRouteMatchInvalidRoute() {
        $this->router->get('/invalid/{id', 'Invalid', 'action');

        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['REQUEST_URI'] = '/invalid/4321';

        try {
            $route = $this->router->match();
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals(Error500Controller::class, $route->getClassName());
        } catch (CLIRequestException $e) {
            $this->assertTrue(false);
        }
    }
}