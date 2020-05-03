<?php

class EmailValidator extends Validator {

    public function __construct() {
        parent::__construct();
        $this->message = $this->translation->get('core', 'not_valid_email');
    }

    protected function doValidate($value) {
        // TODO: for international email addresses
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

}