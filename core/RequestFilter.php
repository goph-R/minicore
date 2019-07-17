<?php

abstract class RequestFilter {

    protected $request;

    public function __construct(Framework $framework) {
        $this->request = $framework->get('request');
    }

    abstract public function run();

}