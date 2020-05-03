<?php

abstract class RequestFilter {

    protected $request;

    public function __construct() {
        $framework = Framework::instance();
        $this->request = $framework->get('request');
    }

    abstract public function run();

}