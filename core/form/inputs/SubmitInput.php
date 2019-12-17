<?php

class SubmitInput extends Input {

    public function __construct(Framework $framework, $name, $defaultValue = '') {
        parent::__construct($framework, $name, $defaultValue);
        $this->required = false;
        $this->bind = false;
    }

    public function fetch() {
        $result = '<button type="submit"';
        $result .= $this->getClassHtml();
        $result .= ' id="'.$this->getId().'">';
        $result .= $this->getValue();
        $result .= '</button>';
        return $result;
    }

}