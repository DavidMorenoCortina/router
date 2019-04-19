<?php

namespace DavidMorenoCortina\Router;


use DavidMorenoCortina\Router\Exception\InvalidRouteException;

class Route {
    /**
     * @var int $method
     */
    protected $method;

    /**
     * @var string $route
     */
    protected $route;

    /**
     * @var string $classname
     */
    protected $classname;

    /**
     * @var string $action
     */
    protected $action;

    /**
     * @var bool $isSecure
     */
    protected $isSecure;

    /**
     * @var array $params
     */
    protected $params = [];

    /**
     * @var string $msg
     */
    protected $msg = '';

    /**
     * @var int $userId
     */
    protected $userId = 0;

    public function __construct(int $method, string $route, string $classname, string $action, bool $isSecure = false) {
        $this->method = $method;
        $this->route = $route;
        $this->classname = $classname;
        $this->action = $action;
        $this->isSecure = $isSecure;
    }

    public function isMethodMatch(int $requestMethod) :bool {
        return $this->method === $requestMethod;
    }

    /**
     * @param string $path
     * @return bool
     * @throws InvalidRouteException
     */
    public function isMatch(string $path) :bool {
        $isParam = false;

        $paramName = '';
        $paramValue = '';

        $len = mb_strlen($this->route);
        $lenRequest = mb_strlen($path);
        $requestPos = 0;
        for($routePos=0; $routePos<$len; $routePos++) {
            if($requestPos === $lenRequest && !$isParam){
                return false;
            }

            if($this->route[$routePos] === '/'){
                if($isParam){
                    throw new InvalidRouteException('"' . $this->route . '" is invalid');
                }
                $requestPos++;
            }elseif($this->route[$routePos] === '{'){
                $isParam = true;
                $paramName = '';
                $paramValue = '';
                while($requestPos < $lenRequest){
                    if($path[$requestPos] === '/'){
                        break;
                    }
                    $paramValue .= $path[$requestPos];

                    $requestPos++;
                }

            }elseif($this->route[$routePos] === '}'){
                if(!$isParam){
                    throw new InvalidRouteException('"' . $this->route . '" is invalid');
                }
                $this->params[$paramName] = $paramValue;

                $isParam = false;
            }elseif($isParam) {
                $paramName .= $this->route[$routePos];
            }else{
                if($this->route[$routePos] !== $path[$requestPos]){
                    return false;
                }
                $requestPos++;
            }
        }

        if($isParam){
            throw new InvalidRouteException('"' . $this->route . '" is invalid');
        }

        return $requestPos === $lenRequest;
    }

    /**
     * @return array
     */
    public function getParams() :array {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getClassName() :string {
        return $this->classname;
    }

    /**
     * @return string
     */
    public function getAction() :string {
        return $this->action;
    }

    /**
     * @return bool
     */
    public function isSecure() :bool {
        return $this->isSecure;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg) {
        $this->msg = $msg;
    }

    /**
     * @return string
     */
    public function getMsg() :string {
        return $this->msg;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId) {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId() :int {
        return $this->userId;
    }
}