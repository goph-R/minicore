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
        $allArgs = array_merge($this->args, $args);
        try {
            $reflect = new ReflectionClass($this->class);
            $this->instance = $reflect->newInstanceArgs($allArgs);
        }
        catch (ReflectionException $e)
        {
            throw new RuntimeException("Couldn't create instance: ".$this->class.", arguments: ".json_encode($allArgs));
        }
        return $this->instance;
    }

    public function isCreated() {
        return $this->instance ? true : false;
    }

    public function getClass() {
        return get_class($this->instance);
    }

}