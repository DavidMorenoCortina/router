<?php

namespace DavidMorenoCortina\Router;


use DavidMorenoCortina\Router\Exception\CLIRequestException;

class HttpRequest {
    const HTTP_GET = 1;
    const HTTP_POST = 2;
    const HTTP_PUT = 3;
    const HTTP_DELETE = 4;

    /** @var string $method */
    protected $method;

    /** @var string $routePath */
    protected $routePath;

    /** @var string $inputStream */
    protected $inputStream;

    /** @var array $body */
    protected $body = null;

    public function __construct($inputStream = 'php://input') {
        $this->inputStream = $inputStream;

        if(array_key_exists('REQUEST_METHOD', $_SERVER)){
            $method = mb_strtoupper($_SERVER['REQUEST_METHOD']);
        }else{
            $method = '';
        }
        switch($method){
            case 'POST':
                $this->method = self::HTTP_POST;
                break;
            case 'PUT':
                $this->method = self::HTTP_PUT;
                break;
            case 'DELETE':
                $this->method = self::HTTP_DELETE;
                break;
            default:
                $this->method = self::HTTP_GET;
        }

        if(array_key_exists('REQUEST_URI', $_SERVER)){
            if(array_key_exists('QUERY_STRING', $_SERVER)){
                $n = mb_strlen($_SERVER['QUERY_STRING']);
                $max = mb_strlen($_SERVER['REQUEST_URI']) - $n - 1;
                if($n > 0 || $_SERVER['REQUEST_URI'][$max] === '?') {
                    $path = mb_substr($_SERVER['REQUEST_URI'], 0, $max);
                }else{
                    $path = $_SERVER['REQUEST_URI'];
                }
            }else{
                $path = $_SERVER['REQUEST_URI'];
            }

            if(mb_strlen($path) > 2083){
                $this->routePath = null;
            }else{
                $this->routePath = mb_strtolower($path);
            }
        }else{
            $this->routePath = null;
        }
    }

    /**
     * @return int
     */
    public function getMethod() :int {
        return $this->method;
    }

    /**
     * @return string
     * @throws CLIRequestException
     */
    public function getRoutePath() :string {
        if(is_null($this->routePath)){
            throw new CLIRequestException();
        }
        return $this->routePath;
    }

    /**
     * @return array
     */
    public function getBody() :array {
        if(is_null($this->body)) {
            if ((
                (
                    array_key_exists('CONTENT_TYPE', $_SERVER)
                    && strcmp($_SERVER['CONTENT_TYPE'], 'application/json') === 0
                )
                || (
                    array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)
                    && strcmp($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') === 0
                ))
                && (
                    self::HTTP_POST === $this->method
                    || self::HTTP_PUT === $this->method
                )
            ) {
                $bodyRequest = file_get_contents($this->inputStream);
                $this->body = json_decode($bodyRequest, true);
                if (empty($this->body)) {
                    $this->body = [];
                }
            } else {
                $this->body = [];
            }
        }

        return $this->body;
    }

    /**
     * @return string
     */
    public function getToken() :string {
        if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
            $type = substr($token, 0, strlen('bearer '));
            if($type !== false && strcmp($type, 'bearer ') === 0){
                return substr($token, strlen('bearer '));
            }
        }
        return '';
    }
}