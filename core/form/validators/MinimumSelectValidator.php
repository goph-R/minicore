<?php

class MinimumSelectValidator extends Validator {
    
    private $minimumCount;
    
    public function __construct(Framework $framework, $minimumCount) {
        parent::__construct($framework);
        $this->minimumCount = $minimumCount;
        $this->message = $this->translation->get('validator', 'must_select_minimum');
        $this->message = str_replace('{min}', $minimumCount, $this->message);
    }
    

    public function doValidate($value) {
        return is_array($value) && count($value) >= $this->minimumCount;
    }    
    
}
