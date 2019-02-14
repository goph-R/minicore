<?php

class Request {

    private $data;
    private $cookies;
    private $method;
    private $server;
    private $headers;

    public function __construct(Framework $framework) {
        $this->data = $_REQUEST;
        $this->cookies = $_COOKIE;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->server = $_SERVER;
        $this->headers = getallheaders();
        $this->processJsonData();
    }

    private function processJsonData() {
        if ($this->getHeader('Content-Type') != 'application/json') {
            return;
        }
        $json = file_get_contents('php://input');
        if (!$json) {
            return;
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return;
        }
        foreach ($data as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function get($name, $defaultValue=null) {
        return isset($this->data[$name]) ? $this->data[$name] : $defaultValue;
    }

    public function set($name, $value) {
        $this->data[$name] = $value;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getHeader($name, $defaultValue=null) {
        return isset($this->headers[$name]) ? $this->headers[$name] : $defaultValue;
    }

    public function getServer($name, $defaultValue=null) {
        return isset($this->server[$name]) ? $this->server[$name] : $defaultValue;
    }

    public function getCookie($name, $defaultValue=null) {
        return isset($this->cookies[$name]) ? $this->cookies[$name] : $defaultValue;
    }

    public function setCookie($name, $value) {
        $this->cookies[$name] = $value;
    }

    public function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}