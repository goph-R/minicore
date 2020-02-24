<?php

function static_url($path) {
    $app = $GLOBALS['framework']->get('app');
    return $app->getStaticUrl($path);
}

function route_url($path=null, $params=[], $amp='&amp;') {
    $router = $GLOBALS['framework']->get('router');
    return $router->getUrl($path, $params, $amp);
}

function css($src, $media='all') {
    return '<link rel="stylesheet" type="text/css" href="'.static_url($src).'" media="'.$media.'">'."\n";
}

function script($src) {
    return '<script src="'.static_url($src).'" type="text/javascript"></script>'."\n";
}

function use_css($src, $media='all') {
    $view = $GLOBALS['framework']->get('view');
    $view->addStyle($src, $media);
}

function use_script($src) {
    $view = $GLOBALS['framework']->get('view');
    $view->addScript($src);
}

function fetch_scripts() {
    $view = $GLOBALS['framework']->get('view');
    $result = '';
    foreach ($view->getScripts() as $script) {
        $result .= script($script);
    }
    if ($view->hasBlock('scripts')) {
        $result .= $view->fetchBlock('scripts');
    }
    return $result;
}

function fetch_styles() {
    $view = $GLOBALS['framework']->get('view');
    $result = '';
    foreach ($view->getStyles() as $style) {
        $result .= css($style['path'], $style['media']);
    }
    return $result;
}

function fetch_content($contentPath, $vars=[]) {
    $view = $GLOBALS['framework']->get('view');
    return $view->fetch($contentPath, $vars);
}

function use_layout($path) {
    $view = $GLOBALS['framework']->get('view');
    $view->useLayout($path);
}

function start_block($name) {
    $view = $GLOBALS['framework']->get('view');
    $view->startBlock($name);
}

function append_block($name) {
    $view = $GLOBALS['framework']->get('view');
    $view->appendBlock($name);
}

function end_block() {
    $view = $GLOBALS['framework']->get('view');
    $view->endBlock();
}

function fetch_block($name) {
    $view = $GLOBALS['framework']->get('view');
    return $view->fetchBlock($name);
}

function esc($value) {
    return htmlspecialchars($value);
}

function use_translation($namespace) {
    $translation = $GLOBALS['framework']->get('translation');
    $translation->setNamespace($namespace);
}

function t($name, $params=[]) {
    $translation = $GLOBALS['framework']->get('translation');
    return $translation->get($translation->getNamespace(), $name, $params);
}

function text($namespace, $name, $params=[]) {
    $translation = $GLOBALS['framework']->get('translation');
    return $translation->get($namespace, $name, $params);
}

function date_view($date) {
    if (!$date) {
        return '';
    }
    $time = strtotime($date);
    return str_replace(' ', '&nbsp;', date('Y-m-d H:i', $time));
}