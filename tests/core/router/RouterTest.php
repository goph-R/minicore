<?php

require_once "core/router/Route.php";
require_once "core/router/RouteAliases.php";
require_once "core/router/Router.php";
require_once "core/Config.php";
require_once "core/Framework.php";
require_once "core/Translation.php";

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase {
    
    const BASE_URL = 'https://testing.url/';
    const LOCALE = 'te';
    const INDEX = 'index.test.php';
    const PARAMETER = 'route_test';
    
    private $configMock;
    private $translationMock;
    private $routeAliasesMock;
    private $router;

    protected function setUp() {
        $this->configMock = $this->createMock(Config::class);
        $this->translationMock = $this->createMock(Translation::class);
        $this->routeAliasesMock = $this->createMock(RouteAliases::class);
        $this->frameworkMock = $this->createMock(Framework::class);        
        $this->frameworkMock->method('get')->will($this->returnValueMap([
            ['config', [], $this->configMock],
            ['translation', [], $this->translationMock],
            ['routeAliases', [], $this->routeAliasesMock],
        ]));
        $this->router = new Router($this->frameworkMock);
    }
    
    private function setUpBaseUrlAndRewriteWithMultiLocales() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.base_url', null, RouterTest::BASE_URL],
            ['router.use_rewrite', null, true]
        ]));        
        $this->translationMock->method('hasMultiLocales')->willReturn(true);
        $this->translationMock->method('getLocale')->willReturn(RouterTest::LOCALE);
    }
    
    public function testGetBaseUrlReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.base_url', null, RouterTest::BASE_URL]
        ]));        
        $this->assertEquals($this->router->getBaseUrl(), RouterTest::BASE_URL);
    }
    
    public function testGetIndexReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.index', null, RouterTest::INDEX]
        ]));        
        $this->assertEquals($this->router->getIndex(), RouterTest::INDEX);
    }
    
    public function testGetParameterReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.parameter', 'route', RouterTest::PARAMETER]
        ]));        
        $this->assertEquals($this->router->getParameter(), RouterTest::PARAMETER);
    }
    
    public function testUsingRewriteReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.use_rewrite', false, true]
        ]));        
        $this->assertEquals($this->router->usingRewrite(), true);
    }
    
    public function testGetUrlReturnsBaseUrlWhenUsingRewriteParameterIsNullAndTranslationHasMultiLocales() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->assertEquals($this->router->getUrl(null), RouterTest::BASE_URL);
    }
    
    public function testGetUrlReturnsBaseUrlWithLocaleWhenUsingRewriteAndParameterIsEmptyStringAndTranslationHasMultiLocales() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->routeAliasesMock->method('hasAlias')->willReturn(false);
        $this->assertEquals($this->router->getUrl(''), RouterTest::BASE_URL.RouterTest::LOCALE.'/');
    }
    
    
}