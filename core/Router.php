<?php

class Router {

    /** @var Config */
    private $config;

    /** @var DbRouteAliases */
    private $aliases;

    /** @var Translation */
    private $translation;

    private $routes = [];

    public function __construct(Framework $framework) {
        $this->config = $framework->get('config');
        $this->aliases = $framework->get('routeAliases');
        $this->translation = $framework->get('translation');
    }

    public function addRoute($signature, $callable, $method='GET') {
        $result = new Route($signature, $callable, $method);
        $this->routes[$signature.$method] = $result;
        return $result;
    }

    public function add($data) {
        foreach ($data as $d) {
            if (isset($d[2])) {
                $this->addRoute($d[0], $d[1], $d[2]);
            } else {
                $this->addRoute($d[0], $d[1]);
            }
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
