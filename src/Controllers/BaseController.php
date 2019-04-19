<?php

namespace DavidMorenoCortina\Router\Controllers;


use DavidMorenoCortina\DependencyContainer\Container;
use DavidMorenoCortina\Router\HttpRequest;

abstract class BaseController {
    /**
     * @var int $userId
     */
    protected $userId;

    /**
     * @var string $msg
     */
    protected $msg = '';

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var array $params
     */
    protected $params = [];

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @return int
     */
    public function getUserId(): int {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getMsg(): string {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg): void {
        $this->msg = $msg;
    }

    /**
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void {
        $this->params = $params;
    }

    protected function getRequest() :HttpRequest {
        return $this->container['request'];
    }
}