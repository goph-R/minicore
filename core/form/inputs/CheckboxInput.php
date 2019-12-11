<?php

class CheckboxInput extends Input {

    private $checked;
    private $text;

    public function __construct(Framework $framework, $name, $defaultValue='', $label='', $checked=false) {
        parent::__construct($framework, $name, $defaultValue);
        $this->checked = $checked;
        if (is_array($label) && count($label) == 2) {
            $t = $framework->get('translation');
            $this->text = $t->get($label[0], $label[1]);
        } else {
            $this->text = $label;
        }
        $this->required = false;
    }

    public function setValue($value) {
        parent::setValue($value);        
        $this->checked = $value == $this->defaultValue;
    }

    public function fetch() {
        $result = '';
        if ($this->text) {
            $result = '<label'.$this->getClassHtml().'>';
        }
        $result .= '<input type="checkbox"';
        $result .= ' id="'.$this->getId().'"';
        $result .= ' name="'.$this->form->getName().'['.$this->getName().']"';
        $result .= ' value="'.$this->view->escape($this->defaultValue).'"';
        $result .= $this->getClassHtml();
        if ($this->checked) {
            $result .= ' checked="checked"';
        }
        $result .= '>';
        if ($this->text) {
            $result .= "\n".$this->text.'</label>';
        }
        return $result;
    }

}