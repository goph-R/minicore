<?php

abstract class App {

    /** @var Logger */
    protected $logger;

    /** @var Framework */
    protected $framework;
    
    /** @var Router */
    protected $router;

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

    protected $routePath;

    public function __construct(Framework $framework, $env='dev', $configPath='config.ini.php') {
        $this->framework = $framework;
        $framework->add([
            'config'      => ['Config', $env, $configPath],
            'logger'      => 'Logger',
            'database'    => ['Database', 'default'],
            'request'     => 'Request',
            'response'    => 'Response',
            'router'      => 'Router',
            'view'        => 'View',
            'helper'      => 'Helper',
            'translation' => 'Translation',
            'mailer'      => 'Mailer',
            'user'        => 'UserSession'
        ]);
    }

    public function init() {
        // While we don't have Config and Logger, can't handle the exception properly
        $this->config = $this->framework->get('config');
        $this->logger = $this->framework->get('logger');
        try {
            $this->router = $this->framework->get('router');
            $this->request = $this->framework->get('request');
            $this->response = $this->framework->get('response');
            $this->translation = $this->framework->get('translation');
            $this->translation->add('validator', 'core/form/validators/translations');
            $helper = $this->framework->get('helper');
            $helper->add('core/helpers/view.php');
            $this->view = $this->framework->get('view');
            $this->view->addFolder(':app', 'core/templates');
            $this->view->addFolder(':form', 'core/form/templates');
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    protected function handleException(Exception $e) {
        $message = $e->getMessage()."\n".$e->getTraceAsString();
        $this->logger->error($message);
        if ($this->config->getEnv() == 'dev') {
            $this->framework->finish(str_replace("\n", "<br>", $message));
        }
    }

    public function run() {
        try {
            $this->initRoutePath();
            $this->initLocale();
            $this->callRoute();
            $this->response->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function initRoutePath() {
        $routeParameter = $this->router->getParameter();
        $this->routePath = $this->request->get($routeParameter);
    }

    private function initLocale() {
        $this->translation->setLocale($this->getAcceptLocale());
        if (!$this->translation->hasMultiLocales()) {
            return;
        }
        $allLocales = $this->translation->getAllLocales();
        foreach ($allLocales as $locale) {
            $len = strlen($locale);
            $routeStart = substr($this->routePath, 0, $len);
            $startsWithLocale = $this->routePath == $locale || $routeStart.'/' == $locale.'/';
            if ($startsWithLocale) {
                $this->translation->setLocale($locale);
                $this->routePath = substr($this->routePath, $len + 1, strlen($this->routePath) - $len);
                break;
            }
        }
    }

    private function getAcceptLocale() {
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

    protected function callRoute() {
        $route = $this->router->get($this->routePath, $this->request->getMethod());
        if ($route) {
            $route->call();
        } else {
            $this->framework->error(404);
        }
    }

    public function getStaticUrl($path) {
        $this->logger->info(strpos($path, 'https://'));
        if (strpos($path, 'https://') === 0 || strpos($path, 'http://') === 0) {
            return $path;
        }
        return $this->config->get('app.static_url').$path;
    }

    public function getMediaPath($path='') {
        return $this->config->get('app.media_folder').$path;
    }

    public function getMediaUrl($path) {
        return $this->config->get('app.media_url').$path;
    }

}