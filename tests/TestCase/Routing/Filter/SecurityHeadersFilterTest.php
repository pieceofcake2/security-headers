<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Dispatcher', 'Routing');
App::uses('DispatcherFilter', 'Routing');
App::uses('Router', 'Routing');
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

    public function testNonce(): void
    {
        Configure::write('SecurityHeaders.contentSecurityPolicyNonce', true);
        $request = new CakeRequest('/posts');
        $response = $this->dispatch($request);
        $nonce = $request->param(SecurityHeadersFilter::NONCE);

        $this->assertIsString($nonce);
        $this->assertStringEndsWith("; script-src 'self' 'nonce-" . $nonce . "'", $response->header()['Content-Security-Policy-Report-Only']);
    }

    public function testNoNonce(): void
    {
        $request = new CakeRequest('/posts');
        $this->dispatch($request);

        $this->assertFalse($request->param(SecurityHeadersFilter::NONCE));
    }

    /**
     * A request of requestAction() uses the nonce of the main request, and gets no header: its response is not
     * sent to the browser
     *
     * @return void
     */
    public function testRequestAction(): void
    {
        Configure::write('SecurityHeaders.contentSecurityPolicyNonce', true);
        $main = new CakeRequest('/posts');
        $main->addParams([SecurityHeadersFilter::NONCE => 'main']);
        Router::setRequestInfo($main);
        $request = new CakeRequest('/posts/sidebar');
        $response = new CakeResponse();
        try {
            (new SecurityHeadersFilter())->beforeDispatch(new CakeEvent('Dispatcher.beforeDispatch', null, [
                'request' => $request,
                'response' => $response,
                'additionalParams' => ['requested' => 1, 'return' => 1],
            ]));
        } finally {
            Router::popRequest();
        }

        $this->assertSame('main', $request->param(SecurityHeadersFilter::NONCE));
        $this->assertSame([], $response->header());
        $this->assertSame([], $this->sent);
    }

    /**
     * Through requestAction(), the nonce of the element is that of the page
     *
     * @return void
     */
    public function testRequestActionThroughTheDispatcher(): void
    {
        Configure::write('SecurityHeaders.contentSecurityPolicyNonce', true);
        Configure::write('Dispatcher.filters', ['SecurityHeadersTestAssetFilter', 'SecurityHeaders.SecurityHeadersFilter']);
        $main = new CakeRequest('/posts');
        $response = new CakeResponse();
        $request = new CakeRequest('/posts/sidebar');
        try {
            (new Dispatcher())->dispatch($main, $response);
            Router::setRequestInfo($main);
            (new Dispatcher())->dispatch($request, new CakeResponse(), ['requested' => 1, 'return' => 1]);
        } finally {
            Router::popRequest();
            Configure::delete('Dispatcher.filters');
        }
        $nonce = $main->param(SecurityHeadersFilter::NONCE);

        $this->assertIsString($nonce);
        $this->assertSame($nonce, $request->param(SecurityHeadersFilter::NONCE));
        $this->assertStringContainsString("'nonce-" . $nonce . "'", $response->header()['Content-Security-Policy-Report-Only']);
    }
}
