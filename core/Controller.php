<?php

abstract class Controller {

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

    public function __construct() {
        $framework = Framework::instance();
        $this->config = $framework->get('config');
        $this->request = $framework->get('request');
        $this->response = $framework->get('response');
        $this->router = $framework->get('router');
        $this->view = $framework->get('view');
        $this->translation = $framework->get('translation');
        $this->userSession = $framework->get('userSession');
    }

    public function render($path, $vars=[]) {
        $content = $this->view->fetchWithLayout($path, $vars);
        $this->response->setContent($content);
    }

    public function json($data) {
        $this->response->setContent(json_encode($data));
    }

    public function error($code, $content='') {
        $framework = Framework::instance();
        $framework->error($code, $content);
    }

    public function redirect($path=null, $params=[]) {
        $framework = Framework::instance();
        $framework->redirect($path, $params);
    }

}
