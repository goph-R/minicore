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
        Framework::setInstance($this->frameworkMock);
        $this->router = new Router();
    }
    
    private function setUpConfigWithoutRewrite() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_BASE_URL, null, self::BASE_URL],
            [Router::CONFIG_INDEX, null, self::INDEX],
            [Router::CONFIG_PARAMETER, null, self::PARAMETER]
        ]));        
    }
    
    private function setUpConfigWithRewrite() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_BASE_URL, null, self::BASE_URL],
            [Router::CONFIG_USE_REWRITE, null, true]
        ]));        
    }
    
    private function setUpMultiLocales() {
        $this->translationMock->method('hasMultiLocales')->willReturn(true);
        $this->translationMock->method('getLocale')->willReturn(self::LOCALE);        
    }
    
    private function setUpConfigWithRewriteAndMultiLocales() {
        $this->setUpConfigWithRewrite();
        $this->setUpMultiLocales();
    }
    
    public function testGetBaseUrlReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_BASE_URL, null, self::BASE_URL]
        ]));         
        $this->assertEquals($this->router->getBaseUrl(), self::BASE_URL);
    }
    
    public function testGetIndexReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_INDEX, null, self::INDEX]
        ]));        
        $this->assertEquals($this->router->getIndex(), self::INDEX);
    }
    
    public function testGetParameterReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_PARAMETER, null, self::PARAMETER]
        ]));        
        $this->assertEquals($this->router->getParameter(), self::PARAMETER);
    }
    
    public function testUsingRewriteReturnsTheValueFromConfig() {
        $this->configMock->method('get')->will($this->returnValueMap([
            [Router::CONFIG_USE_REWRITE, null, true]
        ]));        
        $this->assertEquals($this->router->usingRewrite(), true);
    }
    
    public function testGetUrlReturnsBaseUrlWhenRouteParameterIsNullAndUsingRewriteAndTranslationHasMultiLocales() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $this->assertEquals($this->router->getUrl(null), self::BASE_URL);
    }
    
    public function testGetUrlReturnsBaseUrlWithLocaleWhenRouteParameterIsEmptyStringAndUsingRewriteAndTranslationHasMultiLocales() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $this->routeAliasesMock->method('hasAlias')->willReturn(false);
        $this->assertEquals($this->router->getUrl(''), self::BASE_URL.self::LOCALE);
    }
    
    public function testGetUrlReturnsBaseUrlWithUrlParametersWithAmpEscapeWhenUsingRewriteAndHasUrlParameters() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $this->assertEquals($this->router->getUrl(null, ['t1' => 1, 't2' => 2]), self::BASE_URL.'?t1=1&amp;t2=2');
    }
    
    public function testGetUrlReturnsBaseUrlWithUrlParametersWithoutAmpEscapeWhenUsingRewriteAndHasUrlParametersAndAmpUsedInParameters() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $this->assertEquals($this->router->getUrl(null, ['t1' => 1, 't2' => 2], '&'), self::BASE_URL.'?t1=1&t2=2');
    }
    
    public function testGetUrlReturnsBaseUrlWithIndexWithAmpEscapeWhenRouteParameterIsEmptyAndNotUsingRewriteWithMultiLocales() {
        $this->setUpConfigWithoutRewrite();
        $this->setUpMultiLocales();
        $this->assertEquals($this->router->getUrl(''), self::BASE_URL.self::INDEX.'?'.self::PARAMETER.'='.self::LOCALE);
    }    
    
    public function testGetUrlReturnsBaseUrlWithIndexWithAmpEscapeWhenRouteParameterIsSetAndUrlParameterAddedAndNotUsingRewrite() {
        $this->setUpConfigWithoutRewrite();
        $this->assertEquals($this->router->getUrl('test', ['t' => 1]), self::BASE_URL.self::INDEX.'?'.self::PARAMETER.'=test&amp;t=1');
    }
    
    public function testGetUrlReturnsBaseUrlAndAliasWhenAliasExistsAndUsingRewrite() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $this->routeAliasesMock->method('hasPath')->willReturn(true);
        $this->routeAliasesMock->method('getAlias')->willReturn('alias_test');
        $this->assertEquals($this->router->getUrl('test_path'), self::BASE_URL.'alias_test');
    }
    
    public function testSomething() {
        $this->setUpConfigWithRewriteAndMultiLocales();
        $routeMock = $this->createMock(Route::class);
        $this->frameworkMock->method('create')->willReturn($routeMock);
    }
    
}