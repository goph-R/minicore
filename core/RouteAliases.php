<?php

class RouteAliases {

    protected $dbInstanceName = 'database';
    protected $tableName = 'route_alias';
    protected $recordClass = 'RouteAlias';

    /** @var Database */
    private $db;

    private $byAlias = [];
    private $byPath = [];

    public function __construct(Framework $framework) {
        $this->db = $framework->get($this->dbInstanceName);
    }

    public function hasAlias($alias) {
        if (isset($this->byAlias[$alias])) {
            return true;
        }
        $result = $this->findByAlias($alias);
        if (!$result) {
            return false;
        }
        $this->add($result);
        return true;
    }

    public function getPath($alias) {
        return $this->byAlias[$alias]->getPath();
    }

    public function hasPath($path) {
        if (isset($this->byPath[$path])) {
            return true;
        }
        $result = $this->findByPath($path);
        if (!$result) {
            return false;
        }
        $this->add($result);
        return true;
    }

    public function getAlias($path) {
        return $this->byPath[$path]->getAlias();
    }

    private function findByAlias($alias) {
        return $this->db->fetch(
            $this->recordClass,
            "SELECT alias, path FROM {$this->tableName} WHERE alias = :alias LIMIT 1",
            [':alias' => $alias]
        );
    }

    private function findByPath($path) {
        return $this->db->fetch(
            $this->recordClass,
            "SELECT alias, path FROM {$this->tableName} WHERE path = :path LIMIT 1",
            [':path' => $path]
        );
    }

    private function add($result) {
        $this->byAlias[$result->getAlias()] = $result;
        $this->byPath[$result->getPath()] = $result;
    }

}