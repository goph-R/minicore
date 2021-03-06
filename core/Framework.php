<?php

class Framework {

    /** @var Instance[] */
    private $instances = [];
    
    /** @var Framework */
    private static $instance;

    private $files = [];
    private $classChanges = [];
    private $cacheFolder = "";

    public static function dispatch($appClass, $rootPaths=['core', 'app', 'modules'], $cacheFolder=true) {
        self::setInstance(new Framework());
        $that = self::instance();
        if ($cacheFolder === true) {
            $that->cacheFolder = getcwd().'/app/cache/';
        } else {
            $that->cacheFolder = $cacheFolder;
        }
        $that->run($appClass, $rootPaths);
    }
    
    public static function setInstance($instance) {
        self::$instance = $instance;
    }
    
    /**
     * @return Framework
     */
    public static function instance() {
        return self::$instance;
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
    
    protected function getFilesCachePath() {
        return $this->cacheFolder.'files.cache';
    }
    
    protected function loadFilesFromCache() {
        $path = $this->getFilesCachePath();
        if (file_exists($path)) {
            $this->files = unserialize(file_get_contents($path));
            return true;
        }
        return false;
    }
    
    public function saveFilesToCache() {
        $dirPath = dirname($this->getFilesCachePath());
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
        file_put_contents($this->getFilesCachePath(), serialize($this->files));
    }

    public function initClasses($rootPaths) {
        spl_autoload_register([$this, 'loadClass']);
        if ($this->cacheFolder && $this->loadFilesFromCache()) {
            return;
        }
        foreach ($rootPaths as $rootPath) {
            $directory = new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $fileIterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($fileIterator as $file) {
                if (substr($file->getFilename(), -4) == '.php' && $file->isReadable()) {
                    $this->files[$file->getFilename()] = $file->getPathname();
                }
            }
        }
        if ($this->cacheFolder) {
            $this->saveFilesToCache();
        }
    }

    public function loadClass($class) {
        $filename = $class.'.php';
        foreach ($this->files as $name => $path) {
            if ($name == $filename) {
                require_once $path;
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
            $path = $config->get('error.static_folder').$code.'.html';
            if (!file_exists($path)) {
                $content = "Couldn't find error page for ".$code;
            } else {
                $content = file_get_contents($path);
            }
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
    
    public function changeClass($original, $new) {
        $this->classChanges[$original] = $new;
    }

    public function create($class, $args=[]) {
        if (is_array($class)) {
            $tmp = $class;
            $class = array_shift($tmp);
            $args = $tmp;
        }
        if (isset($this->classChanges[$class])) {
            $class = $this->classChanges[$class];
        }
        $instance = new Instance($class, $args);
        return $instance->create();
    }
    
    public function redirect($path, $params=[]) {
        if (substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://') {
            $url = $path;
        } else {
            /** @var Router $router */
            $router = $this->get('router');
            $url = $router->getUrl($path, $params, '&');
        }
        header('Location: '.$url);
        $this->finish();
    }

    private function addInstance($name, $class, $args=[]) {
        $this->instances[$name] = new Instance($class, $args);
    }

}
