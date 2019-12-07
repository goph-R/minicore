<?php

class Form {

    /** @var Framework */
    protected $framework;

    /** @var View */
    protected $view;

    /** @var Request */
    protected $request;

    /** @var Translation */
    protected $translation;

    /** @var Input[] */
    protected $inputs = [];

    /** @var Validator[][] */
    protected $validators = [];

    /** @var Validator[] */
    protected $postValidators = [];

    protected $order = [];
    protected $errors = [];
    protected $name = '';

    public function __construct(Framework $framework, $name='form') {
        $this->framework = $framework;
        $this->request = $framework->get('request');
        $this->view = $framework->get('view');
        $this->translation = $framework->get('translation');
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    private function getText($data) {
        if (is_array($data) && count($data) == 2) {
            return $this->translation->get($data[0], $data[1]);
        }
        return $data;
    }

    public function addInput($label, $input, $description='') {
        $i = $this->framework->create($input);
        $name = $i->getName();
        if (!in_array($name, $this->order)) {
            $this->order[] = $name;
        }        
        $this->inputs[$name] = $i;
        $i->setForm($this);
        $i->setLabel($this->getText($label));
        $i->setDescription($this->getText($description));
        return $i;
    }

    public function removeInput($name) {
        $this->checkInputExistance($name);
        unset($this->inputs[$name]);
        if (isset($this->validators[$name])) {
            unset($this->validators[$name]);
        }
    }

    public function getInput($name) {
        $this->checkInputExistance($name);
        return $this->inputs[$name];
    }

    public function getValues() {
        $result = [];
        foreach ($this->inputs as $input) {
            $result[$input->getName()] = $input->getValue();
        }
        return $result;
    }

    public function getInputs() {
        $result = [];
        foreach ($this->order as $name) {
            $result[] = $this->inputs[$name];
        }
        return $result;
    }
    
    public function hasInput($inputName) {
        return isset($this->inputs[$inputName]);
    }
    
    public function checkInputExistance($inputName) {
        if (!$this->hasInput($inputName)) {
            throw new RuntimeException("Input doesn't exist: $inputName");
        }
    }

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($error) {
        $this->errors[] = $error;
    }

    public function addValidator($inputName, $validator) {
        $v = $this->framework->create($validator);
        $this->checkInputExistance($inputName);
        if (!isset($this->validators[$inputName])) {
            $this->validators[$inputName] = [];
        }
        $this->validators[$inputName][] = $v;
    }

    public function addPostValidator($validator) {
        $this->postValidators[] = $validator;
    }

    public function getValue($inputName) {
        $this->checkInputExistance($inputName);
        return $this->inputs[$inputName]->getValue();
    }

    public function setValue($inputName, $value) {
        $this->checkInputExistance($inputName);
        $this->inputs[$inputName]->setValue($value);
    }
    
    public function setRequired($inputName, $required) {
        $this->checkInputExistance($inputName);
        $this->inputs[$inputName]->setRequired($required);
    }

    public function bind() {
        $this->errors = [];
        $values = $this->request->get($this->getName());
        foreach ($this->inputs as $input) {
            $name = $input->getName();
            $value = isset($values[$name]) ? $values[$name] : null;
            $input->setValue($value);
        }
    }

    public function processInput() {
        if ($this->request->getMethod() != 'POST') {
            return false;
        }
        $this->bind();
        return $this->validate();
    }

    public function validate() {
        $result = $this->validateInputs();
        if ($result) {
            $result = $this->postValidate();
        }
        return $result;
    }

    private function validateInputs() {
        $result = true;        
        foreach ($this->inputs as $inputName => $input) {
            if (!$input->isRequired() && $input->isEmpty()) {
                continue;
            }
            if ($input->isRequired() && $input->isEmpty()) {
                $error = $this->translation->get('validator', 'cant_be_empty');
                $input->setError($error);
                $result = false;
            } else if (isset($this->validators[$inputName])) {
                $validatorList = $this->validators[$inputName];
                $result &= $this->validateInput($input, $validatorList);
            }
        }
        return $result;
    }

    /**
     * @param Input $input
     * @param Validator[] $validators
     * @return bool
     */
    private function validateInput($input, $validators) {
        foreach ($validators as $validator) {
            $result = $validator->validate($input->getLabel(), $input->getValue());
            if (!$result) {
                $input->setError($validator->getMessage());
                return false;
            }                
        }        
        return true;
    }

    private function postValidate() {
        $result = true;
        foreach ($this->postValidators as $validator) {
            $subResult = $validator->validate('', null);
            if (!$subResult) {
                $this->errors[] = $validator->getMessage();
                $result = false;
            }
        }
        return $result;
    }

    public function fetchHead() {
        foreach ($this->inputs as $input) {
            foreach ($input->getStyles() as $style => $media) {
                $this->view->addStyle($style, $media);
            }
            foreach ($input->getScripts() as $script) {
                $this->view->addScript($script);
            }
        }
    }

    public function fetch($path = ':form/form') {
        $this->fetchHead();
        $result = $this->view->fetch($path, ['form' => $this]);
        return $result;
    }

}
