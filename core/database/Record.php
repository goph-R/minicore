<?php

abstract class Record {

    /** @var Database */
    protected $db;

    /** @var Framework */
    protected $framework;
    
    /** @var Translation */
    protected $translation;

    protected $dbInstanceName = 'database';
    protected $tableName = null;
    protected $primaryKeyName = 'id';
    protected $autoId = true;
    protected $modifiedArray = [];
    protected $newRecord = true;
    protected $referenceList = [];
    protected $localizedList = [];
    protected $localizedTexts = [];

    private static $protectedNames = [
        'db',
        'framework',
        'translation',
        'dbInstanceName',
        'tableName',
        'primaryKeyName',
        'autoId',
        'modifiedArray',
        'newRecord',
        'referenceList',
        'localizedList',
        'localizedTexts',
        'protectedNames'
    ];

    public function __construct(Framework $framework, $dbInstanceName=null) {
        $this->framework = $framework;
        $this->translation = $framework->get('translation');
        $this->db = $framework->get($dbInstanceName == null ? $this->dbInstanceName : $dbInstanceName);
    }

    public function getTableName() {
        return $this->tableName;
    }

    protected function isNameProtected($name) {
        return in_array($name, self::$protectedNames);
    }
    
    protected function isReference($name) {
        return in_array($name, $this->referenceList);
    }
    
    protected function isLocalized($name) {
        return in_array($name, $this->localizedList);
    }
    
    public function hasLocalizedText() {
        return !empty($this->localizedList);
    }

    protected function throwPropertyException($message, $name) {
        $methodString = get_class($this).'::'.$name;
        throw new RuntimeException($message.': '.$methodString);
    }
    
    public function columnExists($name) {
        return property_exists($this, $name) && !$this->isNameProtected($name);
    }

    protected function checkIsPropertyAccessible($name) {
        if (!$this->columnExists($name)) {
            $this->throwPropertyException('Tried to access a non-existing or protected property', $name);
        }
    }

    private function getPropertyFromMethod($name) {
        $tmp = strtolower(preg_replace('/[A-Z]+/' ,'_$0', $name));
        $propertyName = substr($tmp, 4, strlen($tmp) - 4);
        return $propertyName;
    }
    
    private function getMethodFromProperty($name) { // TODO: better solution?
        $result = '';
        $nextUpper = true;
        for ($i = 0; $i < strlen($name); $i++) {
            if ($nextUpper) {
                $result .= strtoupper($name[$i]);
                $nextUpper = false;
            } else if ($name[$i] == '_') {
                $nextUpper = true;
            } else {
                $result .= $name[$i];
            }
        }
        return $result;
    }

    public function __call($name, $args) {
        $method = substr($name, 0, 3);
        if ($method != 'get' && $method != 'set') {
            $this->throwPropertyException('Called an undefined method', $name);
        }
        $propertyName = $this->getPropertyFromMethod($name);
        if ($method == 'get') {
            return $this->getPropertyValue($propertyName);
        }
        $value = isset($args[0]) ? $args[0] : null; 
        $this->setPropertyValue($propertyName, $value);
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

    public function setPropertyValue($name, $value) {
        $this->checkIsPropertyAccessible($name);
        $this->modifiedArray[] = $name;
        $this->$name = $value;
    }

    public function getPropertyValue($name) {
        $this->checkIsPropertyAccessible($name);
        return $this->$name;
    }
    
    public function get($name) {
        $methodName = 'get'.$this->getMethodFromProperty($name);
        if (method_exists($this, $methodName)) {
            return call_user_func([$this, $methodName]);
        }
        return $this->getPropertyValue($name);
    }
    
    public function set($name, $value) {
        $methodName = 'set'.$this->getMethodFromProperty($name);
        if (method_exists($this, $methodName)) {
            call_user_func_array([$this, $methodName], [$value]);
            $this->modifiedArray[] = $name;
        } else {
            $this->setPropertyValue($name, $value);
        }        
    }

    public function setArray($array, $allowed=[]) {
        foreach ($array as $name => $value) {
            if ($allowed && !in_array($name, $allowed)) {
                continue;
            }            
            $this->set($name, $value);
        }
    }
    
    protected function canSave($name) {
        return !$this->isNameProtected($name) && !$this->isReference($name) && !$this->isLocalized($name);
    }

    public function getArray() {
        $vars = get_object_vars($this);
        $result = [];
        foreach (array_keys($vars) as $name) {
            if ($this->canSave($name)) {
                $result[$name] = $this->get($name);
            }
        }
        return $result;
    }

    public function getModifiedArray() {
        $vars = get_object_vars($this);
        $result = [];
        foreach (array_keys($vars) as $name) {
            if ($this->canSave($name) && in_array($name, $this->modifiedArray)) {
                $result[$name] = $this->get($name);
            }
        }
        return $result;
    }

    public function save(array $localizedData=[]) {
        if ($this->isNew()) {
            $this->db->insert($this->tableName, $this->getArray());
            if ($this->autoId) {
                $pkName = $this->getPrimaryKeyName();
                $this->$pkName = $this->db->lastInsertId();
            }
        } else {
            $modifiedArray = $this->getModifiedArray();
            if ($modifiedArray) {
                $this->db->update(
                    $this->tableName, $this->getModifiedArray(),
                    $this->getPrimaryKeyName().' = :pk', [':pk' => $this->getPrimaryKeyValue()]
                );
            }
        }
        if ($localizedData && $this->localizedList) {
            $this->saveLocalized($localizedData);
        }
        $this->setAsOld();
        $this->modifiedArray = [];
    }
    
    protected function saveLocalized(array $localizedData) {
        if ($this->isNew()) {
            $saveData = $this->getSaveDataForLocalized($localizedData);
            foreach ($this->translation->getAllLocales() as $locale) {
                $this->db->insert($this->tableName.'_text', $saveData[$locale]);
            }
        } else {
            $saveData = $this->getSaveDataForLocalized($localizedData, false);
            foreach ($this->translation->getAllLocales() as $locale) {
                $params = ['locale' => $locale, 'text_id' => $this->getId()];
                $this->db->update(
                    $this->tableName.'_text', $saveData[$locale],
                    'text_id = :text_id AND locale = :locale', $params
                );
            }            
        }
    }
    
    protected function getSaveDataForLocalized(array $localizedData, $forInsert=true) {
        $saveData = [];
        foreach ($this->translation->getAllLocales() as $locale) {
            $saveData[$locale] = [];
            if ($forInsert) {
                $saveData[$locale] = [
                    'text_id' => $this->getId(),
                    'locale' => $locale
                ];
            }
            foreach ($this->localizedList as $localized) {
                $key = $localized.'_'.$locale;
                if (!isset($localizedData[$key])) {
                    continue;
                }
                $saveData[$locale][$localized] = $localizedData[$key];
            }
        }
        return $saveData;
    }
    
    public function findLocalizedTexts() {
        $result = [];
        $query = "SELECT * FROM {$this->tableName}_text WHERE text_id = :id";
        $rows = $this->db->fetchAllArray($query, [':id' => $this->getId()]);
        foreach ($rows as $row) {
            $result[$row['locale']] = $row;
        }
        return $result;
    }
    
    public function getLocalizedTexts() {
        if (!$this->localizedTexts) {
            $this->localizedTexts = $this->findLocalizedTexts();
        }
        return $this->localizedTexts;
    }

    public function getLocalizedText($locale, $name) {
        $texts = $this->getLocalizedTexts();
        return isset($texts[$locale][$name]) ? $texts[$locale][$name] : '';
    }

}