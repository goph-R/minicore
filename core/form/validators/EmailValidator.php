<?php

class EmailValidator extends Validator {

    public function __construct(Framework $framework) {
        parent::__construct($framework);
        $this->message = $this->translation->get('validator', 'not_valid_email');
    }

    protected function doValidate($value) {
        // TODO: for international email addresses
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

}