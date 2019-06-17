<?php

abstract class Record {

    /** @var Database */
    protected $db;

    /** @var Framework */
    protected $framework;

    protected $dbInstanceName = 'database';
    protected $tableName = null;
    protected $primaryKeyName = 'id';
    protected $autoId = true;
    protected $modifiedArray = [];
    protected $newRecord = true;

    private static $protectedNames = [
        'db',
        'framework',
        'dbInstanceName',
        'tableName',
        'primaryKeyName',
        'autoId',
        'modifiedArray',
        'newRecord',
        'protectedNames'
    ];

    public function __construct(Framework $framework, $dbInstanceName=null) {
        $this->framework = $framework;
        $this->db = $framework->get($dbInstanceName == null ? $this->dbInstanceName : $dbInstanceName);
    }

    public function getTableName() {
        return $this->tableName;
    }

    protected function isNameProtected($name) {
        return in_array($name, self::$protectedNames);
    }

    private function throwPropertyException($message, $name) {
        $methodString = get_class($this).'::'.$name;
        throw new RuntimeException($message.': '.$methodString);
    }

    private function checkIsPropertyAccessible($name) {
        if (!property_exists($this, $name)) {
            $this->throwPropertyException('Tried to access a non-existing property', $name);
        }
        if ($this->isNameProtected($name)) {
            $this->throwPropertyException('Tried to access a protected property', $name);
        }
    }

    private function getCamelCasePropertyName($name) {
        $tmp = strtolower(preg_replace('/[A-Z]+/' ,'_$0', $name));
        $propertyName = substr($tmp, 4, strlen($tmp) - 4);
        return $propertyName;
    }

    public function __call($name, $args) {
        $method = substr($name, 0, 3);
        if ($method != 'get' && $method != 'set') {
            $this->throwPropertyException('Called an undefined method', $name);
        }
        $propertyName = $this->getCamelCasePropertyName($name);
        if ($method == 'get') {
            return $this->get($propertyName);
        }
        if (!isset($args[0])) {
            $this->throwPropertyException('Tried to set without a value', $name);
        }
        $this->set($propertyName, $args[0]);
        return null;
    }

    public function getPrimaryKeyName() {
        return $this->primaryKeyName;
    }

    public function getPrimaryKeyValue() {
        $pkName = $this->primaryKeyName;
        return $this->$pkName;
    }

    public function isNew() {
        return $this->newRecord;
    }

    public function setAsOld() {
        $this->newRecord = false;
    }

    public function set($name, $value) {
        $this->checkIsPropertyAccessible($name);
        $this->modifiedArray[] = $name;
        $this->$name = $value;
    }

    public function get($name) {
        $this->checkIsPropertyAccessible($name);
        return $this->$name;
    }

    public function setArray($array, $allowed=[]) {
        foreach ($array as $name => $value) {
            if ($allowed && !in_array($name, $allowed)) {
                continue;
            }
            $this->checkIsPropertyAccessible($name);
            $this->$name = $value;
            $this->modifiedArray[] = $name;
        }
    }

    public function getArray() {
        $vars = get_object_vars($this);
        $result = [];
        foreach ($vars as $name => $value) {
            if (!$this->isNameProtected($name)) {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    public function getModifiedArray() {
        $vars = get_object_vars($this);
        $result = [];
        foreach ($vars as $name => $value) {
            if (!$this->isNameProtected($name) && in_array($name, $this->modifiedArray)) {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    public function save() {
        if ($this->isNew()) {
            $this->db->insert($this->tableName, $this->getArray());
            if ($this->autoId) {
                $pkName = $this->primaryKeyName;
                $this->$pkName = $this->db->lastInsertId();
            }
        } else {
            $this->db->update(
                $this->tableName, $this->getModifiedArray(),
                $this->getPrimaryKeyName().' = :pk', [':pk' => $this->getPrimaryKeyValue()]
            );
        }
        $this->modifiedArray = [];
    }

}