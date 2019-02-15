<?php

abstract class Module {

    protected $id;
    protected $framework;

    public function __construct(Framework $framework) {
        $this->framework = $framework;
    }

    public function getId() {
        return $this->id;
    }

    public function init() {}

}