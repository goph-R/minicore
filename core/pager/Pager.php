<?php

class Pager {
    
    /** @var Router */
    protected $router;
    
    /** @var View */
    protected $view;
    
    protected $route = '';
    protected $pageParam = 'page';
    protected $page = 0;
    protected $limit = 25;
    protected $count;
    protected $max;
    protected $next;
    protected $prev;
    protected $start;
    protected $end;
    protected $hideLeft;
    protected $hideRight;
    
    public function __construct() {
        $framework = Framework::instance();
        $this->router = $framework->get('router');
        $this->view = $framework->get('view');
    }
    
    public function init($page, $limit, $count, $route, $showLimit = 7) {
        $this->page = $page;
        $this->count = $count;
        $this->route = $route;
        $this->max = ceil($this->count / $limit) - 1;
        if ($this->page > $this->max) {
            $this->page = $this->max;
        }
        if ($this->page < 0) {
            $this->page = 0;
        }
        $this->prev = $this->page != 0;
        $this->next = $this->page != $this->max;
        $this->calculateStartAndEnd($showLimit);
        $this->hideRight = $this->end < $this->max - 1;
        $this->hideLeft = $this->start > 1;  
    }
    
    protected function calculateStartAndEnd($showLimit) {
        $limit = floor($showLimit / 2);
        $this->start = $this->page - $limit;
        $add = 0;
        if ($this->start < 0) {
            $add = $limit - $this->page;
            $this->start = 0;
        }
        $this->end = $this->page + $limit + $add;
        $sub = 0;
        if ($this->end > $this->max) {
            $sub = $this->end - $this->max;
            $this->end = $this->max;
        }
        $this->start -= $sub;
        if ($this->start < 0) {
            $this->start = 0;
        }
    }
        
    public function fetch($path=':pager/pager', $params=[]) {
        return $this->view->fetch($path, [
            'pager' => $this,
            'params' => $params
        ]);
    }
    
    public function hasLeftHidden() {
        return $this->hideLeft;
    }
    
    public function hasRightHidden() {
        return $this->hideRight;
    }

    public function getStart() {
        return $this->start;
    }
    
    public function getEnd() {
        return $this->end;
    }
    
    public function getPage() {
        return $this->page;
    }
    
    public function getUrl($page, $params=[]) {
        $params[$this->pageParam] = $page;
        return $this->router->getUrl($this->route, $params);
    }
    
    public function getMax() {
        return $this->max;
    }
    
    public function hasPrev() {
        return $this->prev;
    }
    
    public function hasNext() {
        return $this->next;
    }    

}
