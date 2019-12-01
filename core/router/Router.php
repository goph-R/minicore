<?php

class Router {

    /** @var Config */
    private $config;

    /** @var RouteAliases */
    private $aliases;

    /** @var Translation */
    private $translation;

    /** @var Framework */
    private $framework;

    private $routes = [];

    public function __construct(Framework $framework) {
        $this->framework = $framework;
        $this->config = $framework->get('config');
        $this->aliases = $framework->get('routeAliases');
        $this->translation = $framework->get('translation');
    }

    public function addRoute($path, $controllerClass, $controllerMethod, $httpMethods=['GET']) {
        $result = new Route($this->framework, $path, $controllerClass, $controllerMethod, $httpMethods);
        $this->routes[$path] = $result;
        return $result;
    }

    public function add($data) {
        foreach ($data as $d) {
            $this->addRoute($d[0], $d[1], $d[2], isset($d[3]) ? $d[3] : ['GET']);
        }
    }

    /**
     * @param string $path
     * @param string $method
     * @return Route
     */
    public function get($path, $method) {
        if ($this->aliases->hasAlias($path)) {
            $path = $this->aliases->getPath($path);
        }
        foreach ($this->routes as $route) {
            if ($route->match($path, $method)) {
                return $route;
            }
        }
        return null;
    }

    public function getParameter() {
        return $this->config->get('router.parameter', 'route');
    }
    
    public function usingRewrite() {
        return $this->config->get('router.use_rewrite', false);
    }
    
    public function getBaseUrl() {
        return $this->config->get('router.base_url');
    }
    
    public function getIndex() {
        return $this->config->get('router.index');
    }
    
    public function getUrl($path=null, $params=[], $amp='&amp;') {
        $paramsSeparator = '';
        $paramsString = '';
        if ($params) {
            $paramsString = http_build_query($params, '', $amp);
            $paramsSeparator = $this->usingRewrite() ? '?' : $amp;
        }
        $prefix = $this->getPrefix($path);
        $pathWithLocale = $this->getPathWithLocale($path);
        $pathAlias = $this->getPathAlias($pathWithLocale);
        $result = $prefix.$pathAlias.$paramsSeparator.$paramsString;
        return $result;
    }
        
    private function getPrefix($path) {
        $result = $this->getBaseUrl();
        if (!$this->usingRewrite() && $path !== null) {
            $result .= $this->getIndex();
            if ($path) {
                $result .= '?'.$this->getParameter().'=';
            }
        }
        return $result;
    }
    
    private function getPathWithLocale($path) {
        $result = $path;
        if ($this->translation->hasMultiLocales() && $path !== null) {
            $result = $this->translation->getLocale().'/'.$path;
        }
        return $result;
    }
    
    private function getPathAlias($path) {
        $result = $path;
        if ($this->aliases->hasPath($path)) {
            $result = $this->aliases->getAlias($path);
        }
        return $result;        
    }

}
