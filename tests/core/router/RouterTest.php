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
            [Router::CONFIG_BASE_URL, null, RouterTest::BASE_URL],
            [Router::CONFIG_USE_REWRITE, null, true]
        ]));        
        $this->translationMock->method('hasMultiLocales')->willReturn(true);
        $this->translationMock->method('getLocale')->willReturn(RouterTest::LOCALE);
    }
    
    public function testGetBaseUrlReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_BASE_URL, null, RouterTest::BASE_URL]
        ]));         
        $this->assertEquals($this->router->getBaseUrl(), RouterTest::BASE_URL);
    }
    
    public function testGetIndexReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_INDEX, null, RouterTest::INDEX]
        ]));        
        $this->assertEquals($this->router->getIndex(), RouterTest::INDEX);
    }
    
    public function testGetParameterReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_PARAMETER, null, RouterTest::PARAMETER]
        ]));        
        $this->assertEquals($this->router->getParameter(), RouterTest::PARAMETER);
    }
    
    public function testUsingRewriteReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_USE_REWRITE, null, true]
        ]));        
        $this->assertEquals($this->router->usingRewrite(), true);
    }
    
    public function testGetUrlReturnsBaseUrlWhenRouteParameterIsNullAndUsingRewriteAndTranslationHasMultiLocales() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->assertEquals($this->router->getUrl(null), RouterTest::BASE_URL);
    }
    
    public function testGetUrlReturnsBaseUrlWithLocaleWhenRouteParameterIsEmptyStringAndUsingRewriteAndTranslationHasMultiLocales() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->routeAliasesMock->method('hasAlias')->willReturn(false);
        $this->assertEquals($this->router->getUrl(''), RouterTest::BASE_URL.RouterTest::LOCALE.'/');
    }
    
    public function testGetUrlReturnsBaseUrlWithUrlParametersWithAmpEscapeWhenUsingRewriteAndHasUrlParameters() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->assertEquals($this->router->getUrl(null, ['t1' => 1, 't2' => 2]), RouterTest::BASE_URL.'?t1=1&amp;t2=2');
    }
    
    public function testGetUrlReturnsBaseUrlWithUrlParametersWithoutAmpEscapeWhenUsingRewriteAndHasUrlParametersAndAmpUsedInParameters() {
        $this->setUpBaseUrlAndRewriteWithMultiLocales();
        $this->assertEquals($this->router->getUrl(null, ['t1' => 1, 't2' => 2], '&'), RouterTest::BASE_URL.'?t1=1&t2=2');
    }
    
    public function testGetUrlReturnsBaseUrlWithIndexWithAmpEscapeWhenRouteParameterIsSetAndUrlParameterAddedAndNotUsingRewrite() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_BASE_URL, null, RouterTest::BASE_URL],
            [Router::CONFIG_INDEX, null, RouterTest::INDEX],
            [Router::CONFIG_PARAMETER, null, RouterTest::PARAMETER]
        ]));         
        $this->assertEquals($this->router->getUrl('test', ['t' => 1]), RouterTest::BASE_URL.RouterTest::INDEX.'?'.RouterTest::PARAMETER.'=test&amp;t=1');
    }
    
}