<?php

class Framework {

    /** @var Instance[] */
    private $instances = [];

    /** @var array FileInfo[] */
    private $files = [];

    public static function dispatch($appClass, $rootPaths=['core', 'app', 'modules']) {
        $framework = new Framework();
        $framework->run($appClass, $rootPaths);
    }

    public function run($appClass, $rootPaths=['core', 'app', 'modules']) {
        set_error_handler([$this, 'handleError']);
        $this->initClasses($rootPaths);
        $this->add(['app' => $appClass]);
        /** @var App $app */
        $app = $this->get('app');
        $app->init();
        $app->run();
    }

    public function initClasses($rootPaths) {
        foreach ($rootPaths as $rootPath) {
            $directory = new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $fileIterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($fileIterator as $file) {
                if (substr($file->getFilename(), -4) == '.php' && $file->isReadable()) {
                    $this->files[] = $file;
                }
            }
        }
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass($class) {
        $filename = $class.'.php';
        foreach ($this->files as $file) {
            if ($file->getFilename() == $filename) {
                require_once $file->getPathname();
                break;
            }
        }
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        $this->logError($errstr." (Code: ".$errno.")\nFile: ".$errfile."\nLine: ".$errline."\n");
    }

    public function logError($message) {
        /** @var Logger $logger */
        $logger = $this->get('logger');
        $logger->error($message);
    }

    public function error($code, $content='') {
        if (!$content) {
            /** @var Config $config */
            $config = $this->get('config');
            $path = $config->get('error.static_folder') . $code . '.html';
            if (!file_exists($path)) {
                throw new RuntimeException("Couldn't find error page for " . $code);
            }
            $content = file_get_contents($path);
        }
        http_response_code($code);
        $this->finish($content);
    }

    public function finish($content='') {
        die($content);
    }

    public function add($instanceConfigs) {
        foreach ($instanceConfigs as $name => $data) {
            if (is_array($data)) {
                $class = array_shift($data);
                $this->addInstance($name, $class, $data);
            } else {
                $this->addInstance($name, $data);
            }
        }
    }

    public function get($name, $args=[]) {
        if (!isset($this->instances[$name])) {
            throw new RuntimeException("Error while trying to get instance '$name': doesn't exist.");
        }
        $result = $this->instances[$name]->create($args);
        return $result;
    }

    public function create($class, $args=[]) {
        if (is_array($class)) {
            $tmp = $class;
            $class = array_shift($tmp);
            $args = $tmp;
        }
        $instance = new Instance($this, $class, $args);
        return $instance->create();
    }

    private function addInstance($name, $class, $args=[]) {
        $this->instances[$name] = new Instance($this, $class, $args);
    }

}
