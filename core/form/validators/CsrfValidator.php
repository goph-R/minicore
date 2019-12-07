<?php

class CsrfValidator extends Validator {
    
    /** @var UserSession */
    private $userSession;
    private $userSessionName;
    
    /** @var Form */
    private $form;
    private $inputName;

    public function __construct(Framework $framework, $userSessionName, Form $form, $inputName) {
        parent::__construct($framework);
        $this->message = $this->translation->get('validator', 'not_valid_csrf');
        $this->userSession = $framework->get('userSession');
        $this->userSessionName = $userSessionName;
        $this->form = $form;
        $this->inputName = $inputName;
    }

    protected function doValidate($value) {
        return $this->form->getValue($this->inputName) == $this->userSession->get($this->userSessionName);
    }

}
