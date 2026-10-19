<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Dispatcher', 'Routing');
App::uses('DispatcherFilter', 'Routing');
App::uses('SecurityHeadersFilter', 'SecurityHeaders.Routing/Filter');

/**
 * Answers every request, as `AssetDispatcher` does for the files of the plugins
 */
class SecurityHeadersTestAssetFilter extends DispatcherFilter
{
    public $priority = 9;

    public function beforeDispatch(CakeEvent $event): CakeResponse
    {
        $event->stopPropagation();

        return $event->data['response'];
    }
}

class SecurityHeadersFilterTest extends CakeTestCase
{
    /**
     * The headers sent with `header()`, in order: [name, value or null]
     *
     * @var array<int, array{0: string, 1: string|null}>
     */
    protected array $sent = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->sent = [];
        SecurityHeadersFilter::$send = function (string $name, ?string $value): void {
            $this->sent[] = [$name, $value];
        };
    }

    public function tearDown(): void
    {
        SecurityHeadersFilter::$send = null;
        Configure::delete('SecurityHeaders');
        parent::tearDown();
    }

    protected function dispatch(CakeRequest $request): CakeResponse
    {
        $response = new CakeResponse();
        (new SecurityHeadersFilter())->beforeDispatch(new CakeEvent('Dispatcher.beforeDispatch', null, compact('request', 'response')));

        return $response;
    }

    public function testAddsTheHeaders(): void
    {
        $response = $this->dispatch(new CakeRequest('/posts'));
        $headers = $response->header();

        $this->assertSame('SAMEORIGIN', $headers['X-Frame-Options']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $headers);
        $this->assertArrayNotHasKey('Strict-Transport-Security', $headers);
        $this->assertContains(['X-Frame-Options', 'SAMEORIGIN'], $this->sent);
        $this->assertSame(['X-Powered-By', null], end($this->sent));
    }

    public function testStrictTransportSecurityOnHttps(): void
    {
        Configure::write('SecurityHeaders.strictTransportSecurity', 'max-age=31536000');
        $_SERVER['HTTPS'] = 'on';
        try {
            $response = $this->dispatch(new CakeRequest('/posts'));
        } finally {
            unset($_SERVER['HTTPS']);
        }

        $this->assertSame('max-age=31536000', $response->header()['Strict-Transport-Security']);
    }

    public function testKeepsPoweredBy(): void
    {
        Configure::write('SecurityHeaders.removePoweredBy', false);
        $this->dispatch(new CakeRequest('/posts'));

        $this->assertNotContains(['X-Powered-By', null], $this->sent);
    }

    /**
     * Configured in `Dispatcher.filters`, after the filter that answers the request: it runs first
     *
     * @return void
     */
    public function testRunsBeforeTheAssetFilter(): void
    {
        Configure::write('Dispatcher.filters', ['SecurityHeadersTestAssetFilter', 'SecurityHeaders.SecurityHeadersFilter']);
        $response = new CakeResponse();
        try {
            (new Dispatcher())->dispatch(new CakeRequest('/security_headers/js/app.js'), $response);
        } finally {
            Configure::delete('Dispatcher.filters');
        }

        $this->assertSame('SAMEORIGIN', $response->header()['X-Frame-Options']);
    }
}
