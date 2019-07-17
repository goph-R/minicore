<?php

class Router {

    /** @var Config */
    private $config;

    /** @var DbRouteAliases */
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

    public function getUrl($path=null, $params=[], $amp='&amp;') {
        $paramsSeparator = '';
        $paramsString = '';
        $useRewrite = $this->config->get('router.use_rewrite', false);
        $prefix = $this->config->get('router.base_url');
        if ($this->translation->hasMultiLocales() && $path !== null) {
            $path = $this->translation->getLocale().'/'.$path;
        }
        if (!$useRewrite && $path !== null) {
            $prefix .= $this->config->get('router.index');
            if ($path) {
                $prefix .= '?'.$this->getParameter().'=';
            }
        }
        if ($params) {
            $paramsString = http_build_query($params, '', $amp);
            $paramsSeparator = $useRewrite ? '?' : $amp;
        }
        if ($this->aliases->hasPath($path)) {
            $path = $this->aliases->getAlias($path);
        }
        $result = $prefix.$path.$paramsSeparator.$paramsString;
        return $result;
    }

}
