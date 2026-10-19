<?php
declare(strict_types=1);

App::uses('Component', 'Controller');
App::uses('SecurityHeaders', 'SecurityHeaders.Lib');
App::uses('SecurityHeadersFilter', 'SecurityHeaders.Routing/Filter');

/**
 * Changes the security headers of `SecurityHeadersFilter` in an action:
 *
 * ```
 * // An action shown in a frame of a partner site
 * $this->SecurityHeaders->set('Content-Security-Policy', "frame-ancestors 'self' https://partner.example.com");
 * $this->SecurityHeaders->disable('X-Frame-Options');
 *
 * // An action with a video of YouTube
 * $this->SecurityHeaders->addSources('frame-src', 'https://www.youtube-nocookie.com');
 * ```
 */
class SecurityHeadersComponent extends Component
{
    /**
     * The controller of the request
     *
     * @var \Controller|null
     */
    protected ?Controller $controller = null;

    /**
     * The sources added in this request, by directive
     *
     * @var array<string, array<string>>
     */
    protected array $sources = [];

    /**
     * Keeps the controller
     *
     * @param \Controller $controller Controller
     * @return void
     */
    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Sets a header, instead of the value of the settings
     *
     * @param string $name Header, e.g. `Content-Security-Policy`
     * @param string $value Value
     * @return void
     */
    public function set(string $name, string $value): void
    {
        $this->disable($name);
        $this->controller?->response->header($name, $value);
        $this->send($name, $value);
    }

    /**
     * Adds sources to a directive of the Content Security Policy of the settings, in this response
     *
     * @param string $directive Directive, e.g. `img-src`
     * @param array<string>|string $sources Sources, e.g. `https://images.example.com`
     * @return void
     */
    public function addSources(string $directive, array|string $sources): void
    {
        $directive = strtolower($directive);
        $this->sources[$directive] = [...$this->sources[$directive] ?? [], ...(array)$sources];
        $settings = SecurityHeaders::settings();
        $settings['contentSecurityPolicyAllow'] = [...(array)$settings['contentSecurityPolicyAllow'], self::class => $this->sources];
        $request = $this->controller?->request;
        $nonce = $request?->param(SecurityHeadersFilter::NONCE);
        foreach (SecurityHeaders::headers($settings, $request !== null && $request->is('ssl'), is_string($nonce) ? $nonce : null) as $name => $value) {
            if (str_starts_with($name, 'Content-Security-Policy')) {
                $this->set($name, $value);
            }
        }
    }

    /**
     * Does not send a header in this response
     *
     * @param string $name Header, e.g. `X-Frame-Options`
     * @return void
     */
    public function disable(string $name): void
    {
        if ($this->controller !== null) {
            // CakeResponse can add a header but not remove one
            Closure::bind(static function (CakeResponse $response) use ($name): void {
                foreach (array_keys($response->_headers) as $header) {
                    if (strcasecmp((string)$header, $name) === 0) {
                        unset($response->_headers[$header]);
                    }
                }
            }, null, CakeResponse::class)($this->controller->response);
        }
        $this->send($name, null);
    }

    /**
     * Sends or removes a header with `header()`, as the filter does
     *
     * @param string $name Header
     * @param string|null $value Value, or null to remove it
     * @return void
     */
    private function send(string $name, ?string $value): void
    {
        if (SecurityHeadersFilter::$send !== null) {
            (SecurityHeadersFilter::$send)($name, $value);

            return;
        }
        if (!headers_sent()) {
            $value === null ? header_remove($name) : header($name . ': ' . $value);
        }
    }
}
