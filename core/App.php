<?php

abstract class App {
    
    const CONFIG_STATIC_URL = 'app.static_url';
    const CONFIG_MEDIA_URL = 'app.media_url';
    const CONFIG_MEDIA_FOLDER = 'app.media_folder';

    /** @var Logger */
    protected $logger;

    /** @var Router */
    protected $router;
    
    /** @var RouteAliases */
    protected $routeAliases;

    /** @var Config */
    protected $config;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Translation */
    protected $translation;

    /** @var View */
    protected $view;
    
    /** @var Helper */
    protected $helper;

    /** @var Module[] */
    protected $modules = [];

    protected $routePath;

    /** @var RequestFilter[] */
    protected $requestFilters = [];

    public function __construct($env='dev', $configPath='config.ini.php') {
        $framework = Framework::instance();
        $framework->add([
            'config'        => ['Config', $env, $configPath],
            'logger'        => 'Logger',
            'database'      => ['Database', 'default'],
            'request'       => 'Request',
            'response'      => 'Response',
            'router'        => 'Router',
            'routeAliases'  => 'RouteAliases',
            'view'          => 'View',
            'helper'        => 'Helper',
            'translation'   => 'Translation',
            'mailer'        => 'Mailer',
            'userSession'   => 'UserSession'
        ]);
    }

    public function init() {
        $framework = Framework::instance();
        $this->config = $framework->get('config');
        $this->logger = $framework->get('logger');
        try {
            $this->initInstances();
            $this->initRoutePath();
            $this->initLocale();
            $this->initModules();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function run() {
        try {
            if (!$this->runRequestFilters()) {
                $this->callRoute();
            }
            $this->response->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
 
    public function addModule($moduleClass) {
        $framework = Framework::instance();
        $module = $framework->create($moduleClass);
        $this->modules[$module->getId()] = $module;
    }

    public function hasModule($moduleId) {
        return isset($this->modules[$moduleId]);
    }

    public function addRequestFilter($requestFilterClass) {
        $framework = Framework::instance();
        $requestFilter = $framework->create($requestFilterClass);
        $this->requestFilters[] = $requestFilter;
    }
   
    public function getStaticUrl($path) {
        return $this->getFullUrl(self::CONFIG_STATIC_URL, $path);
    }

    public function getMediaPath($path='') {
        return $this->config->get(self::CONFIG_MEDIA_FOLDER).$path;
    }

    public function getMediaUrl($path) {
        return $this->getFullUrl(self::CONFIG_MEDIA_URL, $path);
    }  
    
    protected function initInstances() {
        $framework = Framework::instance();
        $this->request = $framework->get('request');
        $this->response = $framework->get('response');
        $this->translation = $framework->get('translation');
        $this->translation->add('core', 'core/translations');
        $this->router = $framework->get('router');
        $this->routeAliases = $framework->get('routeAliases');
        $this->helper = $framework->get('helper');
        $this->helper->add('core/helpers/view.php');
        $this->view = $framework->get('view');
        $this->view->addFolder(':app', 'core/templates');
        $this->view->addFolder(':form', 'core/form/templates');
        $this->view->addFolder(':pager', 'core/pager/templates');
    }

    protected function initRoutePath() {
        $routeParameter = $this->router->getParameter();
        $this->routePath = $this->request->get($routeParameter);
        if ($this->routeAliases->hasAlias($this->routePath)) {
            $this->routePath = $this->routeAliases->getPath($this->routePath);
        }    
    }
    
    public function getRoutePath() {
        return $this->routePath;
    }

    protected function initLocale() {
        $this->translation->setLocale($this->getAcceptLocale());
        if (!$this->translation->hasMultiLocales()) {
            return;
        }
        $allLocales = $this->translation->getAllLocales();
        foreach ($allLocales as $locale) {
            $len = strlen($locale);
            $routeStart = substr($this->routePath, 0, $len);
            $startsWithLocale = $this->routePath == $locale || $routeStart.'/' == $locale.'/';
            if (!$startsWithLocale) {
                continue;
            }
            $this->translation->setLocale($locale);
            $this->routePath = substr($this->routePath, $len + 1, strlen($this->routePath) - $len);
            break;
        }
    }

    protected function getAcceptLocale() {
        $result = $this->translation->getLocale();
        $acceptLanguage = $this->request->getServer('HTTP_ACCEPT_LANGUAGE');
        if ($acceptLanguage) {
            $locales = $this->translation->getAllLocales();
            $acceptLocale = strtolower(substr($acceptLanguage, 0, 2));
            if (in_array($acceptLocale, $locales)) {
                $result = $acceptLocale;
            }
        }
        return $result;
    }

    protected function initModules() {
        // TODO: dependency tree
        foreach ($this->modules as $module) {
            $module->init();
        }
    }
    
    protected function getFullUrl($configName, $path) {
        if (substr($path, 0, 1) == '/') {
            $path = $this->router->getBaseUrl().substr($path, 1);
        }
        if (strpos($path, 'https://') === 0 || strpos($path, 'http://') === 0) {
            return $path;
        }
        return $this->config->get($configName).$path;
    }
    
    protected function handleException(Exception $e) {
        $message = $e->getMessage()."\n".$e->getTraceAsString();
        $this->logger->error($message);
        if ($this->config->getEnv() == 'dev') {
            $framework = Framework::instance();
            $framework->finish(str_replace("\n", "<br>", $message));
        }
    }
    
    protected function runRequestFilters() {
        foreach ($this->requestFilters as $requestFilter) {
            if ($requestFilter->run()) {
                return true;
            }
        }
        return false;
    }

    protected function callRoute() {
        $route = $this->router->get($this->routePath, $this->request->getMethod());
        if ($route) {
            $route->call();
        } else {
            $framework = Framework::instance();
            $framework->error(404);
        }
    }

}
