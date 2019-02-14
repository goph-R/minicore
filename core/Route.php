<?php

class Route {

    private $signature;
    private $method;
    private $partCount;
    private $parts;
    private $callable;
    private $parameters;

    public function __construct($signature, Callable $callable, $method) {
        $this->signature = $signature;
        $this->callable = $callable;
        $this->parts = explode('/', $signature);
        $this->partCount = count($this->parts);
        $this->method = $method;
    }

    public function match($path, $method) {
        if ($method != $this->method) {
            return false;
        }
        $parts = explode('/', $path);
        if ($this->partCount != count($parts)) {
            return false;
        }
        $this->parameters = [];
        foreach ($this->parts as $i => $part) {
            if ($part == '?') {
                $this->parameters[] = $parts[$i];
            } else if ($part != $parts[$i]) {
                return false;
            }
        }
        return true;
    }

    public function call() {
        call_user_func_array($this->callable, $this->parameters);
    }

}

