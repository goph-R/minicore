<?php

class Config {

    private $env;
    private $data = [];

    public function __construct(Framework $framework, $env, $path) {
        $this->env = $env;
        $this->load($path);
    }

    public function load($path) {
        if (!file_exists($path)) {
            throw new RuntimeException("Couldn't load config: $path");
        }
        $this->data = array_merge($this->data, parse_ini_file($path, true));
    }

    public function get($name, $defaultValue=null) {
        if (isset($this->data[$this->env]) && isset($this->data[$this->env][$name])) {
            return $this->data[$this->env][$name];
        }
        return isset($this->data['all'][$name]) ? $this->data['all'][$name] : $defaultValue;
    }

    public function getEnv() {
        return $this->env;
    }

}
