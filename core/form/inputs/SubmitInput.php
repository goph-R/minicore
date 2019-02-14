<?php

class SubmitInput extends Input {

    public function __construct(Framework $framework, $name, $defaultValue = '') {
        parent::__construct($framework, $name, $defaultValue);
        $this->setRequired(false);
    }

    public function fetch() {
        $result = '<input type="submit"';
        $result .= ' id="'.$this->getId().'"';
        $result .= ' name="'.$this->form->getName().'['.$this->getName().']"';
        $result .= ' value="'.$this->view->escape($this->getValue()).'"';
        $result .= $this->getClassHtml();
        $result .= '>';
        return $result;
    }

}