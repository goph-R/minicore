<?php

abstract class Module {

    protected $id;

    public function getId() {
        return $this->id;
    }

    public function init() {}

}