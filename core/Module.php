<?php

abstract class Module {

    protected $id;

    public function getId() {
        return $this->id;
    }

    public function getFolder() {
        return Framework::instance()->get('app')->getModulesFolder().$this->id."/";
    }

    public function init() {}

}