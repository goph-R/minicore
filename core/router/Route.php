<?php

class Route {

    /** @var Framework */
    private $framework;

    private $path;
    private $httpMethods;
    private $partCount;
    private $parts;
    private $controllerClass;
    private $controllerMethod;
    private $parameters;

    public function __construct(Framework $framework, $path, $controllerClass, $controllerMethod, $httpMethods) {
        $this->framework = $framework;
        $this->path = $path;
        $this->controllerClass = $controllerClass;
        $this->controllerMethod = $controllerMethod;
        $this->parts = explode('/', $path);
        $this->partCount = count($this->parts);
        $this->httpMethods = $httpMethods;
    }

    public function match($path, $httpMethod) {
        if (!in_array($httpMethod, $this->httpMethods)) {
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
        $controller = $this->framework->get($this->controllerClass);
        if (!method_exists($controller, $this->controllerMethod)) {
            throw new RuntimeException('The method '.get_class($controller).'::'.$this->controllerMethod." doesn't exist.");
        }
        call_user_func_array([$controller, $this->controllerMethod], $this->parameters);
    }

}

