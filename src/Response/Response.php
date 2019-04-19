<?php

namespace DavidMorenoCortina\Router\Response;


abstract class Response {
    const EOL = "\r\n";

    /**
     * @var string $body
     */
    protected $body;

    /**
     * @var int $httpStatus
     */
    protected $httpStatus;

    protected $contentType = '';

    public function __construct($body, $httpStatus = 200) {
        $this->body = $body;
        $this->httpStatus = $httpStatus;
    }

    /**
     * @param bool $silent
     * @return string
     */
    public function send($silent = false) :string {
        $output = '';
        if($silent){
            $output = 'HTTP/1.1 ' . $this->httpStatus . self::EOL;
            $output .= 'CONTENT_TYPE: ' . $this->contentType . self::EOL . self::EOL;

            $output .= $this->getBody();
        }else{
            // stop PHP sending a Content-Type automatically
            ini_set('default_mimetype', '');

            http_response_code($this->httpStatus);
            header('CONTENT_TYPE: ' . $this->contentType);
            echo $this->getBody();
        }
        return $output;
    }

    /**
     * @return string
     */
    public function getBody() :string {
        return $this->body;
    }
}