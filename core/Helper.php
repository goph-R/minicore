<?php

class Helper {

    public function __construct(Framework $framework) {
        // how to do this without globals? (to access framework instance from helper functions, no singleton please)
        $GLOBALS['framework'] = $framework;
    }

    public function add($path) {
        require_once $path;
    }

}