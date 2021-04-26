<?php

class UserSession {

    const SESSION_ID = 'id';

    const CONFIG_SALT = 'session.salt';

    /** @var Request */
    private $request;

    /** @var Config */
    private $config;

    private $permissions;

    public function __construct() {
        session_start();
        $framework = Framework::instance();
        $this->request = $framework->get('request');
        $this->config = $framework->get('config');
    }

    public function get($name, $defaultValue=null) {
        $key = 'user.'.$name;
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    public function set($name, $value) {
        $key = 'user.'.$name;
        $_SESSION[$key] = $value;
    }
    
    public function getId() {
        return $this->get(self::SESSION_ID);
    }
    
    public function setId($value) {
        $this->set(self::SESSION_ID, $value);
    }

    public function guid() {
        // based on: https://www.uuidgenerator.net/dev-corner/php
        // Generate 16 bytes (128 bits) of random data
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }


    public function getHash() {
        if (!$this->config->get(self::CONFIG_SALT)) {
            throw new RuntimeException("'".self::CONFIG_SALT."' has no value in configuration.");
        }
        $salt = $this->config->get(self::CONFIG_SALT);
        return md5($salt.$this->guid().$this->request->getIp());
    }

    public function setLoggedIn($in) {
        $this->set('hash', $in ? $this->getHash() : '');
    }

    public function isLoggedIn() {
        return $this->get('hash') == $this->getHash();
    }

    public function destroy() {
        session_destroy();
    }

    public function setFlash($name, $message) {
        $this->set('flash.'.$name, $message);
    }

    public function hasFlash($name) {
        return $this->get('flash.'.$name, '') ? true : false;
    }

    public function getFlash($name) {
        $result = $this->get('flash.'.$name, '');
        $this->set('flash.'.$name, null);
        return $result;
    }

    public function addPermission($name) {
        $this->permissions[] = $name;
    }

    public function hasPermission($name) {
        return in_array($name, $this->permissions);
    }

}
