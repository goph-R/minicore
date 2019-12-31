<?php

class Database {

    private $name;

    /** @var Framework */
    private $framework;

    /** @var PDO */
    private $pdo = null;

    /** @var Logger */
    private $logger;

    private $connected = false;

    private $className;
    private $objectParams;

    public function __construct(Framework $framework, $name) {
        $this->framework = $framework;
        $this->name = $name;
        $this->logger = $framework->get('logger');
    }

    private function connect() {
        if ($this->connected) {
            return;
        }
        $config = $this->framework->get('config');
        $dsn = $config->get('database.'.$this->name.'.dsn');
        $user = $config->get('database.'.$this->name.'.user');
        $password = $config->get('database.'.$this->name.'.password');
        $this->pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->connected = true;
        $this->query("USE ".$config->get('database.'.$this->name.'.name'));
        $this->query("SET NAMES 'utf8'");
    }

    public function query($query, $params=[]) {
        $this->connect();
        $paramsJson = $params ? "\nParameters: ".json_encode($params) : '';
        try {
            $this->logger->info("Executing query: \n$query$paramsJson");
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
        } catch (RuntimeException $e) {
            $this->logger->error("SQL query error!\nThe query was:\n$query$paramsJson");
            throw $e;
        }
        return $stmt;
    }

    public function fetchArray($query, $params=[]) {
        $stmt = $this->query($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        return $result;
    }

    public function fetchAllArray($query, $params=[]) {
        $stmt = $this->query($query, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $result;
    }

    public function fetchColumn($query, $params=[], $index=0) {
        $stmt = $this->query($query, $params);
        $result = $stmt->fetchColumn($index);
        $stmt = null;
        return $result;
    }
    
    public function fetch($classData, $query, $params=[]) {
        $result = $this->fetchAll($classData, $query, $params);
        return isset($result[0]) ? $result[0] : null;
    }

    public function fetchAll($classData, $query, $params=[]) {
        $this->processClassData($classData);
        $stmt = $this->query($query, $params);
        /** @var Record[] $result */
        $result = $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->className, $this->objectParams);
        foreach ($result as $r) {
            $r->setAsOld();
        }
        $stmt = null;
        return $result;
    }

    private function processClassData($classData) {
        $this->className = $classData;
        $this->objectParams = [];
        if (is_array($classData)) {
            $this->className = array_shift($classData);
            $this->objectParams = $classData;
        }
        array_unshift($this->objectParams, $this->framework);
    }

    public function lastInsertId($name=null) {
        return $this->pdo->lastInsertId($name);
    }

    public function insert($tableName, $data) {
        $params = [];
        $names = [];
        foreach ($data as $name => $value) {
            $names[] = $name;
            $params[':'.$name] = $value;
        }
        $namesString = join(', ', $names);
        $paramsString = join(', ', array_keys($params));
        $sql = "INSERT INTO $tableName ($namesString) VALUES ($paramsString)";
        $this->query($sql, $params);
    }

    public function update($tableName, $data, $condition='', $conditionParams=[]) {
        $params = [];
        $pairs = [];
        foreach ($data as $name => $value) {
            $pairs[] = $name.' = :'.$name;
            $params[':'.$name] = $value;
        }
        $params = array_merge($params, $conditionParams);
        $pairsString = join(', ', $pairs);
        $sql = "UPDATE $tableName SET $pairsString WHERE $condition";
        $this->query($sql, $params);
    }
    
    public function getInConditionAndParams($values, $name='in') {
        $params = [];
        $in = "";
        foreach ($values as $i => $item) {
            $key = ":".$name.$i;
            $in .= "$key,";
            $params[$key] = $item;
        }
        $condition = rtrim($in, ",");
        return ['condition' => $condition, 'params' => $params];
    }    

}