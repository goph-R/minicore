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
    const LOCALE = 'en';
    
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
    
    private function setUpBaseUrl() {
        $this->configMock->method('get')->will($this->returnValueMap([
            ['router.base_url', null, RouterTest::BASE_URL],
            ['router.use_rewrite', null, true]
        ]));        
    }
    
    private function setUpMultiLocales() {
        $this->translationMock->method('hasMultiLocales')->willReturn(true);
        $this->translationMock->method('getLocale')->willReturn(RouterTest::LOCALE);
    }
    
    private function setUpNoAliases() {
        $this->routeAliasesMock->method('hasAlias')->willReturn(false);
    }
    
    public function testGetBaseUrlReturnsTheValueFromConfig() {
        $this->setUpBaseUrl();
        $this->assertEquals($this->router->getBaseUrl(), RouterTest::BASE_URL);
    }
    
    public function testGetUrlReturnsBaseUrlWhenParameterIsNullAndTranslationHasMultiLocales() {
        $this->setUpBaseUrl();
        $this->setUpMultiLocales();
        $this->assertEquals($this->router->getUrl(null), RouterTest::BASE_URL);
    }
    
    public function testGetUrlReturnsBaseUrlWithLocaleWhenParameterIsEmptyStringAndTranslationHasMultiLocales() {
        $this->setUpBaseUrl();
        $this->setUpMultiLocales();
        $this->setUpNoAliases();
        $this->assertEquals($this->router->getUrl(''), RouterTest::BASE_URL.RouterTest::LOCALE.'/');
    }
}