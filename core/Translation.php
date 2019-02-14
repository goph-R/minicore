<?php

class Translation {

    /**
     * @var Request
     */
    private $request;
    private $folders;
    private $data;
    private $defaultLocale;
    private $allLocales;
    private $namespace = '';
    private $locale;

    public function __construct(Framework $framework) {
        $this->request = $framework->get('request');
        $config = $framework->get('config');
        $this->locale = $config->get('translation.default');
        $allString = $config->get('translation.all', $this->defaultLocale);
        $all = explode(',', $allString);
        $this->allLocales = array_map(function ($e) { return trim($e); }, $all);
    }

    public function add($namespace, $folder) {
        $this->data[$namespace] = false;
        $this->folders[$namespace] = $folder;
    }
    
    public function getAllLocales() {
        return $this->allLocales;
    }

    public function hasMultiLocales() {
        return count($this->allLocales) > 1;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function get($namespace, $name, $params=[]) {
        $result = '#'.$namespace.'.'.$name.'#';
        if (!isset($this->folders[$namespace]) || !isset($this->data[$namespace])) {
            return $result;
        }
        if ($this->data[$namespace] === false) {
            $path = $this->folders[$namespace].'/'.$this->locale.'.ini';
            $iniData = file_exists($path) ? parse_ini_file($path) : [];
            $this->data[$namespace] = $iniData;
        }
        if (isset($this->data[$namespace][$name])) {
            $result = $this->data[$namespace][$name];
        }
        foreach ($params as $name => $value) {
            $result = str_replace('{'.$name.'}', $value, $result);
        }
        return $result;
    }

}