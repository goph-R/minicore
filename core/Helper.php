<?php

class Helper {

    private $baseDir;

    public function __construct() {
        $framework = Framework::instance();
        $app = $framework->get('app');
        $this->baseDir = $app->getPath();
    }

    public function add($path, $relativePath="") {
        if (!$relativePath) {
            $p = $this->baseDir.$path;
        } else {
            $p = dirname($relativePath)."/".$path;
        }
        if (!file_exists($p)) {
            throw new RuntimeException("File not found for include: ".$p);
        }
        include_once $p;
    }

}