<?php

class Logger {

    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;

    private static $levelMap = [
        'info'    => self::INFO,
        'warning' => self::WARNING,
        'error'   => self::ERROR
    ];

    protected $config;
    protected $level;
    protected $path;
    protected $dateFormat;

    public function __construct(Framework $framework) {
        $config = $framework->get('config');
        $this->level = @self::$levelMap[$config->get('logger.level')];
        $this->path = $config->get('logger.path');
        $this->dateFormat = $config->get('logger.dateFormat', 'Y-m-d H:i:s');
    }

    public function info($message) {
        if ($this->level <= Logger::INFO) {
            $this->log('INFO', $message);
        }
    }

    public function warning($message) {
        if ($this->level <= Logger::WARNING) {
            $this->log('WARNING', $message);
        }
    }

    public function error($message) {
        if ($this->level <= Logger::ERROR) {
            $this->log('ERROR', $message);
        }
    }

    protected function log($label, $message) {
        $text = date($this->dateFormat).' ['.$label.'] '.$message."\n";
        $dir = dirname($this->path);
        if (!file_exists($dir)) {
            mkdir($dir, 0x755, true);
        }
        $result = file_put_contents($this->path, $text, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            throw new RuntimeException("Can't write to ".$this->path);
        }
    }

}