<?php

abstract class Controller {

    /** @var Framework */
    protected $framework;

    /** @var Config */
    protected $config;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Router */
    protected $router;

    /** @var View */
    protected $view;

    /** @var Translation */
    protected $translation;

    /** @var UserSession */
    protected $userSession;

    public function __construct(Framework $framework) {
        $this->framework = $framework;
        $this->config = $this->framework->get('config');
        $this->request = $this->framework->get('request');
        $this->response = $this->framework->get('response');
        $this->router = $this->framework->get('router');
        $this->view = $this->framework->get('view');
        $this->translation = $this->framework->get('translation');
        $this->userSession = $this->framework->get('userSession');
    }

    public function render($path, $vars=[]) {
        $content = $this->view->fetchWithLayout($path, $vars);
        $this->response->setContent($content);
    }

    public function json($data) {
        $this->response->setContent(json_encode($data));
    }

    public function error($code, $content='') {
        $this->framework->error($code, $content);
    }

    public function redirect($path=null, $params=[]) {
        if (substr($path, 0, 4) == 'http') {
            $url = $path;
        } else {
            $url = $this->router->getUrl($path, $params, '&');
        }
        header('Location: '.$url);
        $this->framework->finish();
    }

}
