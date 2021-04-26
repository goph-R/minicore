<?php

class Config {

    private $env;
    private $data = [];

    public function __construct($env, $path) {
        $this->env = $env;
        $this->load($path);
    }

    public function load($path) {

        // parse ini
        if (!file_exists($path)) {
            throw new RuntimeException("Couldn't load config: $path");
        }
        $iniData = parse_ini_file($path, true);

        // include config
        if (isset($iniData['include'])) {
            foreach ($iniData['include'] as $name => $path) {
                $this->load($path);
            }
            unset($iniData['include']);
        }

        // copy the data
        foreach ($iniData as $env => $data) {
            if (!isset($this->data[$env])) {
                $this->data[$env] = [];
            }
            foreach ($iniData[$env] as $name => $value) {
                $this->data[$env][$name] = $value;
            }
        }

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
