<?php

namespace DavidMorenoCortina\Router;


use DavidMorenoCortina\DependencyContainer\Container;
use DavidMorenoCortina\JWT\Exception\InvalidJWTException;
use DavidMorenoCortina\JWT\Exception\RSAException;
use DavidMorenoCortina\JWT\Exception\UserException;
use DavidMorenoCortina\JWT\JWT;
use DavidMorenoCortina\JWT\Model\RSARepository;
use DavidMorenoCortina\JWT\Model\UserRepository;
use DavidMorenoCortina\JWT\Validator\UserValidator;
use DavidMorenoCortina\Router\Controllers\Error401Controller;
use DavidMorenoCortina\Router\Controllers\Error404Controller;
use DavidMorenoCortina\Router\Controllers\Error500Controller;
use DavidMorenoCortina\Router\Exception\CLIRequestException;
use DavidMorenoCortina\Router\Exception\InvalidRouteException;
use PDO;

class Router {
    /**
     * @var Container $container
     */
    protected $container;

    /** @var Route[] $routes */
    protected $routes = [];

    public function __construct(Container $container) {
        $this->container = $container;

        $router = $this;

        $this->container['router'] = function() use($router){
            return $router;
        };
    }

    public function registerDependencies() :void {
        $this->container['request'] = function(Container $container){
            if(array_key_exists('inputStream', $container['settings'])){
                $inputStream = $container['settings']['inputStream'];
            }else{
                $inputStream = null;
            }

            return new HttpRequest($inputStream);
        };

        $this->container['jwt'] = function(Container $container) {
            /** @var PDO $conn */
            $conn = $container['pdo'];

            $userRepository = new UserRepository($conn);
            $userValidator = new UserValidator($userRepository);
            $rsaRepository = new RSARepository($conn);
            return new JWT($userValidator, $rsaRepository);
        };
    }

    /**
     * @param string $route
     * @param string $classname
     * @param string $action
     * @param bool $isSecure
     */
    public function get(string $route, string $classname, string $action, bool $isSecure = false) {
        $this->routes[] = new Route(HttpRequest::HTTP_GET, $route, $classname, $action, $isSecure);
    }

    /**
     * @param string $route
     * @param string $classname
     * @param string $action
     * @param bool $isSecure
     */
    public function post(string $route, string $classname, string $action, bool $isSecure = false) {
        $this->routes[] = new Route(HttpRequest::HTTP_POST, $route, $classname, $action, $isSecure);
    }

    /**
     * @param string $route
     * @param string $classname
     * @param string $action
     * @param bool $isSecure
     */
    public function put(string $route, string $classname, string $action, bool $isSecure = false) {
        $this->routes[] = new Route(HttpRequest::HTTP_PUT, $route, $classname, $action, $isSecure);
    }

    /**
     * @param string $route
     * @param string $classname
     * @param string $action
     * @param bool $isSecure
     */
    public function delete(string $route, string $classname, string $action, bool $isSecure = false) {
        $this->routes[] = new Route(HttpRequest::HTTP_DELETE, $route, $classname, $action, $isSecure);
    }

    /**
     * @return Route
     * @throws CLIRequestException
     */
    public function match() :Route {
        /** @var HttpRequest $request */
        $request = $this->container['request'];
        $settings = $this->container['settings'];
        $jwtKeyName = $settings['jwtKeyName'];

        foreach ($this->routes as $route){
            if($route->isMethodMatch($request->getMethod())){
                try {
                    if($route->isMatch($request->getRoutePath())){
                        if($route->isSecure()) {
                            /** @var JWT $jwt */
                            $jwt = $this->container['jwt'];
                            $token = $request->getToken();
                            if(empty($token)){
                                return $this->getError401Route();
                            }
                            $userId = $jwt->decode($token, $jwtKeyName);
                            $route->setUserId($userId);
                        }

                        return $route;
                    }
                } catch (InvalidRouteException $e) {
                    return $this->getError500Route($e->getMessage());
                } catch (InvalidJWTException $e) {
                    return $this->getError500Route($e->getMessage());
                } catch (RSAException $e) {
                    return $this->getError500Route($e->getMessage());
                } catch (UserException $e) {
                    return $this->getError401Route();
                }
            }
        }
        return $this->getError404Route();
    }

    protected function getError500Route(string $msg) :Route {
        $route = new Route(0, '', Error500Controller::class, 'action');
        $route->setMsg($msg);
        return $route;
    }

    protected function getError401Route() :Route {
        $route = new Route(0, '', Error401Controller::class, 'action');
        return $route;
    }

    protected function getError404Route() :Route {
        $route = new Route(0, '', Error404Controller::class, 'action');
        return $route;
    }
}