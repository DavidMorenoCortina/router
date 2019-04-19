<?php

namespace DavidMorenoCortina\Router\Response;


class JsonResponse extends Response {
    public function __construct($body, $httpStatus = 200) {
        parent::__construct($body, $httpStatus);

        $this->contentType = 'application/json';
    }

    /**
     * @return string
     */
    public function getBody(): string {
        return json_encode($this->body);
    }
}