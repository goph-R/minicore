<?php

class SelectInput extends Input {

    private $options = [];

    public function __construct(Framework $framework, $name, $defaultValue='', $options=[]) {
        parent::__construct($framework, $name, $defaultValue);
        $this->options = $options;
    }

    public function fetch() {
        $result = '<select';
        $result .= ' id="'.$this->getId().'"';
        $result .= ' name="'.$this->form->getName().'['.$this->getName().']"';
        $result .= $this->getClassHtml();
        $result .= '>';
        $result .= $this->fetchRecursive($this->options, null);
        $result .= '</select>';
        return $result;
    }

    private function fetchRecursive($options, $groupName) {
        $result = $groupName ? '<optgroup label="'.$this->view->escape($groupName).'">' : '';
        foreach ($options as $optionValue => $optionText) {
            if (!is_array($optionText)) {
                $selected = $optionValue == $this->getValue() ? ' selected="selected"' : '';
                $value = $this->view->escape($optionValue);
                $result .= '<option value="'.$value.'"'.$selected.'>'.$optionText.'</option>';
            } else {
                $result .= $this->fetchRecursive($optionText['options'], $optionText['label']);
            }
        }
        $result .= $groupName ? '</optgroup>' : '';
        return $result;
    }

}