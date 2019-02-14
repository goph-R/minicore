<?php

class TextareaInput extends Input {

    protected $type = 'text';    
    protected $placeholder = '';

    public function __construct(Framework $framework, $name, $defaultValue = '') {
        parent::__construct($framework, $name, $defaultValue);
        $this->trimValue = false;
    }

    public function fetch() {
        $result = '<textarea';
        $result .= ' id="'.$this->getId().'"';
        $result .= ' name="'.$this->form->getName().'['.$this->getName().']"';
        if ($this->placeholder) {
            $result .= ' placeholder="'.$this->view->escape($this->placeholder).'"';
        }
        $result .= $this->getClassHtml();
        $result .= '>'.$this->getValue().'</textarea>';
        return $result;
    }

}