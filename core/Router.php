<?php

class Router {

    /** @var Config */
    private $config;

    private $routes = [];

    public function __construct(Framework $framework) {
        $this->config = $framework->get('config');
    }

    public function addRoute($signature, $callable, $method='GET') {
       $this->routes[$signature.$method] = new Route($signature, $callable, $method);
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
        if (!$useRewrite && $path !== null) {
            $prefix .= $this->config->get('router.index');
            if ($path) {
                $prefix .= '?' . $this->getParameter() . '=';
            }
        }
        if ($params) {
            $paramsString = http_build_query($params, '', $amp);
            $paramsSeparator = $useRewrite ? '?' : $amp;
        }
        return $prefix.$path.$paramsSeparator.$paramsString;
    }

}
