<?php

class Instance {

    private $class;
    private $args;
    private $instance = null;

    public function __construct($class, $args) {
        $this->class = $class;
        $this->args = $args;
    }

    public function create($args=[]) {
        if ($this->isCreated()) {
            return $this->instance;
        }
        $reflect = new ReflectionClass($this->class);
        $allArgs = array_merge($this->args, $args);
        $this->instance = $reflect->newInstanceArgs($allArgs);
        return $this->instance;
    }

    public function isCreated() {
        return $this->instance ? true : false;
    }

    public function getClass() {
        return get_class($this->instance);
    }

}