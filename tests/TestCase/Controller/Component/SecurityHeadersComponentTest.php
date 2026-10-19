<?php

App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('SecurityHeadersComponent', 'SecurityHeaders.Controller/Component');
App::uses('SecurityHeadersFilter', 'SecurityHeaders.Routing/Filter');

class SecurityHeadersComponentTest extends CakeTestCase
{
    /**
     * The headers sent with `header()`, in order: [name, value or null]
     *
     * @var array<int, array{0: string, 1: string|null}>
     */
    protected array $sent = [];

    protected Controller $Controller;

    protected SecurityHeadersComponent $SecurityHeaders;

    public function setUp(): void
    {
        parent::setUp();
        $this->sent = [];
        SecurityHeadersFilter::$send = function (string $name, ?string $value): void {
            $this->sent[] = [$name, $value];
        };
        $response = new CakeResponse();
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('Content-Security-Policy', "default-src 'self'");
        $this->Controller = new Controller(new CakeRequest('/posts'), $response);
        $this->SecurityHeaders = new SecurityHeadersComponent(new ComponentCollection());
        $this->SecurityHeaders->initialize($this->Controller);
    }

    public function tearDown(): void
    {
        SecurityHeadersFilter::$send = null;
        parent::tearDown();
    }

    public function testDisable(): void
    {
        $this->SecurityHeaders->disable('x-frame-options');

        $this->assertArrayNotHasKey('X-Frame-Options', $this->Controller->response->header());
        $this->assertArrayHasKey('Content-Security-Policy', $this->Controller->response->header());
        $this->assertSame([['x-frame-options', null]], $this->sent);
    }

    public function testSet(): void
    {
        $this->SecurityHeaders->set('content-security-policy', "frame-ancestors 'self' https://partner.example.com");

        $this->assertSame(['X-Frame-Options' => 'SAMEORIGIN', 'content-security-policy' => "frame-ancestors 'self' https://partner.example.com"], $this->Controller->response->header());
        $this->assertSame([['content-security-policy', null], ['content-security-policy', "frame-ancestors 'self' https://partner.example.com"]], $this->sent);
    }
}
