<?php

class TextInput extends Input {

    protected $type = 'text';    
    protected $placeholder = '';
    
    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
    }

    public function fetch() {
        $result = '<input type="'.$this->type.'"';
        $result .= ' id="'.$this->getId().'"';
        $result .= ' name="'.$this->form->getName().'['.$this->getName().']"';
        $result .= ' value="'.$this->view->escape($this->getValue()).'"';
        if ($this->placeholder) {
            $result .= ' placeholder="'.$this->view->escape($this->placeholder).'"';
        }
        $result .= $this->getClassHtml();
        $result .= '>';
        return $result;
    }

}