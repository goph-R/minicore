<?php

abstract class Module {

    /** @var App */
    protected $app;
    protected $id;

    public function getId() {
        return $this->id;
    }

    public function init() {
        $this->app = Framework::instance()->get('app');
    }

    public function getFolder($suffix="") {
        return $this->app->getModulesFolder().$this->id."/".$suffix;
    }

    public function getUrl($suffix="") {
        return $this->app->getModulesUrl().$this->id."/".$suffix;
    }


}