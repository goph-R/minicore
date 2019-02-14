<?php

class CheckboxGroupInput extends Input {

    protected $defaultValue = [];

    private $checks;
    private $labels;

    public function __construct(Framework $framework, $name, $labelsByValues=[], $checks=[]) {
        parent::__construct($framework, $name, array_keys($labelsByValues));
        $this->checks = $checks;
        $this->labels = $labelsByValues;
        $this->trimValue = false;
        $this->required = false;
    }

    public function setValue($value) {
        parent::setValue($value);
        if (!is_array($value)) {
            $value = [];
        }
        $this->checks = [];
        foreach ($this->defaultValue as $defaultValue) {
            if (in_array($defaultValue, $value)) {
                $this->checks[] = $defaultValue;
            }
        }
        
    }

    public function fetch() { 
        $result = '<div class="checkbox-group">';
        foreach ($this->defaultValue as $defaultValue) {
            $id = $this->getId().'_'.$this->escapeName($defaultValue);
            $inputName = $this->form->getName().'['.$this->getName().'][]';
            $result .= '<div class="checkbox-group-row">';
            $result .= '<input type="checkbox" id="'.$id.'" name="'.$inputName.'"';
            $result .= ' value="'.$this->view->escape($defaultValue).'"';
            $result .= $this->getClassHtml();
            if (in_array($defaultValue, $this->checks)) {
                $result .= ' checked="checked"';
            }
            $result .= '>';            
            if ($this->labels[$defaultValue]) {
                $result .= '<label for="'.$id.'">'.$this->labels[$defaultValue].'</label>';
            }
            $result .= '</div>';
        }
        $result .= '</div>';
        return $result;
    }

}

