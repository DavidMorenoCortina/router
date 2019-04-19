<?php

namespace DavidMorenoCortina\Router\Controllers;


use DavidMorenoCortina\Router\Response\HtmlResponse;

class Error404Controller extends BaseController {
    public function action() {
        return new HtmlResponse($this->getMsg(), 404);
    }
}