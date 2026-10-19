<?php
declare(strict_types=1);

App::uses('DispatcherFilter', 'Routing');
App::uses('SecurityHeaders', 'SecurityHeaders.Lib');

/**
 * Adds the security headers to every response of the application: the pages, the redirects, the downloads and the
 * files of the plugins and themes. It runs before the other filters, e.g. `AssetDispatcher`, and adds them to the
 * response of the request.
 *
 * The error pages get a response of their own, so the headers are also sent with `header()`: a header of the same
 * name that the application or CakePHP sends later replaces it. The files of `webroot/` do not reach CakePHP: the
 * web server sends their headers.
 *
 * ```
 * Configure::write('Dispatcher.filters', ['AssetDispatcher', 'CacheDispatcher', 'SecurityHeaders.SecurityHeadersFilter']);
 * ```
 */
class SecurityHeadersFilter extends DispatcherFilter
{
    /**
     * Before `AssetDispatcher` and `CacheDispatcher` (9)
     *
     * @var int
     */
    public $priority = 1;

    /**
     * The parameter of the request with the nonce of the Content Security Policy
     *
     * @var string
     */
    public const NONCE = 'securityHeadersNonce';

    /**
     * Sends a header, or removes one when the value is null: `header()` by default
     *
     * @var (\Closure(string, string|null): void)|null
     */
    public static ?Closure $send = null;

    /**
     * Adds the security headers to the response of the request
     *
     * @param \CakeEvent $event Event, with the request and the response
     * @return null The dispatching continues
     */
    public function beforeDispatch(CakeEvent $event)
    {
        /** @var \CakeRequest $request */
        $request = $event->data['request'];
        /** @var \CakeResponse $response */
        $response = $event->data['response'];
        $settings = SecurityHeaders::settings();
        $send = self::$send ?? static function (string $name, ?string $value): void {
            if (headers_sent()) {
                return;
            }
            $value === null ? header_remove($name) : header($name . ': ' . $value);
        };

        $nonce = null;
        if (!empty($settings['contentSecurityPolicyNonce'])) {
            $nonce = SecurityHeaders::nonce();
            $request->addParams([self::NONCE => $nonce]);
        }

        foreach (SecurityHeaders::headers($settings, $request->is('ssl'), $nonce) as $name => $value) {
            $response->header($name, $value);
            $send($name, $value);
        }
        if (!empty($settings['removePoweredBy'])) {
            $send('X-Powered-By', null);
        }

        return null;
    }
}
