<?php

class SeparatorInput extends Input {
    
    public function __construct(Framework $framework, $name, $defaultValue='') {
        parent::__construct($framework, $name, $defaultValue);
        $this->required = false;
        $this->bind = false;
    }

    public function fetch() {
        return $this->defaultValue;
    }

}