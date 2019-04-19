<?php

namespace DavidMorenoCortina\Router\Controllers;


use DavidMorenoCortina\Router\Response\HtmlResponse;

class Error500Controller extends BaseController {
    public function action() {
        return new HtmlResponse($this->getMsg(), 500);
    }
}