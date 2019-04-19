<?php

namespace DavidMorenoCortina\Router\Response;


class HtmlResponse extends Response {
    public function __construct($body, $httpStatus = 200) {
        parent::__construct($body, $httpStatus);

        $this->contentType = 'text/html';
    }
}