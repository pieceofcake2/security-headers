<?php
declare(strict_types=1);

/**
 * The security headers of the responses of an application, from the settings of `Configure::read('SecurityHeaders')`.
 *
 * A setting is the value of its header, or `false` not to send it. The headers that the web server or the CDN
 * should send, or that break pages easily, are off by default.
 */
class SecurityHeaders
{
    /**
     * The headers of the settings
     */
    public const HEADERS = [
        'contentSecurityPolicy' => 'Content-Security-Policy',
        'xFrameOptions' => 'X-Frame-Options',
        'xContentTypeOptions' => 'X-Content-Type-Options',
        'referrerPolicy' => 'Referrer-Policy',
        'permissionsPolicy' => 'Permissions-Policy',
        'crossOriginOpenerPolicy' => 'Cross-Origin-Opener-Policy',
        'crossOriginResourcePolicy' => 'Cross-Origin-Resource-Policy',
        'strictTransportSecurity' => 'Strict-Transport-Security',
        'cacheControl' => 'Cache-Control',
        'crossOriginEmbedderPolicy' => 'Cross-Origin-Embedder-Policy',
        'xXssProtection' => 'X-XSS-Protection',
    ];

    /**
     * The settings by default
     */
    public const DEFAULTS = [
        // On by default: the headers of the application
        // The directives and their sources, or the policy as a string
        'contentSecurityPolicy' => [
            'default-src' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
        ],
        // The sources added to the directives of the policy, e.g. ['script-src' => ['https://cdn.example.com']]
        'contentSecurityPolicySources' => [],
        // The sources of the services of PRESETS added to the policy, e.g. ['googleTagManager', 'googleAnalytics']
        'contentSecurityPolicyPresets' => [],
        // A nonce of each request added to script-src, for the inline scripts: see nonce()
        'contentSecurityPolicyNonce' => false,
        // The browsers report the violations without blocking them: adapt the policy to the application first
        'contentSecurityPolicyReportOnly' => true,
        'reportUri' => false,
        'xFrameOptions' => 'SAMEORIGIN',
        'xContentTypeOptions' => 'nosniff',
        'referrerPolicy' => 'strict-origin-when-cross-origin',
        'permissionsPolicy' => 'camera=(), microphone=(), geolocation=()',
        'crossOriginOpenerPolicy' => 'same-origin',
        'crossOriginResourcePolicy' => 'same-origin',
        'removePoweredBy' => true,
        // Off by default: for the web server or the CDN, or easily breaking
        'strictTransportSecurity' => false,
        'cacheControl' => false,
        'crossOriginEmbedderPolicy' => false,
        'xXssProtection' => false,
    ];

    /**
     * The sources that the services need, by directive. From
     * https://developers.google.com/tag-platform/security/guides/csp, with `script-src` instead of `script-src-elem`
     * for the browsers without `script-src-elem`.
     */
    public const PRESETS = [
        // The container of Google Tag Manager. Its inline snippet needs the nonce, see contentSecurityPolicyNonce
        'googleTagManager' => [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://www.google.com'],
        ],
        // The preview mode of Google Tag Manager
        'googleTagManagerPreview' => [
            'script-src' => ['https://www.googletagmanager.com', 'https://tagmanager.google.com'],
            'style-src' => ['https://www.googletagmanager.com', 'https://tagmanager.google.com', 'https://fonts.googleapis.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://ssl.gstatic.com', 'https://www.gstatic.com'],
            'font-src' => ['https://fonts.gstatic.com', 'data:'],
        ],
        // Google Analytics, without the features of Google Ads
        'googleAnalytics' => [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com'],
        ],
        // Google Analytics, with the features of Google Ads. Add the domains of the countries, e.g. https://*.google.co.jp
        'googleAnalyticsWithAds' => [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net', 'https://pagead2.googlesyndication.com'],
            'frame-src' => ['https://www.googletagmanager.com'],
        ],
        // Google Ads: the conversions, the remarketing, the conversion linker and the user data. Add the domains
        // of the countries, e.g. https://www.google.co.jp
        'googleAds' => [
            'script-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://www.google.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://googleads.g.doubleclick.net', 'https://pagead2.googlesyndication.com', 'https://www.google.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://googleads.g.doubleclick.net', 'https://pagead2.googlesyndication.com', 'https://www.google.com', 'https://ad.doubleclick.net'],
            'frame-src' => ['https://www.googletagmanager.com'],
        ],
        // Floodlight
        'floodlight' => [
            'img-src' => ['https://www.googletagmanager.com', 'https://ad.doubleclick.net', 'https://ade.googlesyndication.com', 'https://adservice.google.com'],
            'connect-src' => ['https://pagead2.googlesyndication.com', 'https://www.google.com', 'https://www.googleadservices.com', 'https://ad.doubleclick.net'],
            'frame-src' => ['https://www.googletagmanager.com', 'https://*.fls.doubleclick.net'],
        ],
        // Google Merchant Center
        'merchantCenter' => [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://*.merchant-center-analytics.goog'],
            'connect-src' => ['https://*.merchant-center-analytics.goog'],
        ],
    ];

    /**
     * The directives that the browsers replace with another one when the policy does not have them
     */
    public const FALLBACKS = [
        'script-src-elem' => ['script-src', 'default-src'],
        'script-src-attr' => ['script-src', 'default-src'],
        'style-src-elem' => ['style-src', 'default-src'],
        'style-src-attr' => ['style-src', 'default-src'],
        'frame-src' => ['child-src', 'default-src'],
        'worker-src' => ['child-src', 'script-src', 'default-src'],
        'child-src' => ['default-src'],
        'connect-src' => ['default-src'],
        'font-src' => ['default-src'],
        'img-src' => ['default-src'],
        'manifest-src' => ['default-src'],
        'media-src' => ['default-src'],
        'object-src' => ['default-src'],
        'script-src' => ['default-src'],
        'style-src' => ['default-src'],
    ];

    /**
     * The settings: the defaults with those of `Configure::read('SecurityHeaders')`
     *
     * @param array<string, mixed>|null $settings Settings, default: those of Configure
     * @return array<string, mixed>
     * @throws \InvalidArgumentException When a setting is unknown
     */
    public static function settings(?array $settings = null): array
    {
        $settings ??= (array)(Configure::read('SecurityHeaders') ?: []);
        $unknown = array_diff(array_keys($settings), array_keys(self::DEFAULTS));
        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf('Unknown setting of SecurityHeaders: %s', implode(', ', $unknown)));
        }
        $unknown = array_diff((array)($settings['contentSecurityPolicyPresets'] ?? []), array_keys(self::PRESETS));
        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf('Unknown preset of SecurityHeaders: %s', implode(', ', $unknown)));
        }

        return $settings + self::DEFAULTS;
    }

    /**
     * The headers to send, by name
     *
     * @param array<string, mixed> $settings Settings of `settings()`
     * @param bool $https Whether the request is of HTTPS: `Strict-Transport-Security` is sent on HTTPS only
     * @param string|null $nonce The nonce of the request, when `contentSecurityPolicyNonce` is on
     * @return array<string, string>
     */
    public static function headers(array $settings, bool $https, ?string $nonce = null): array
    {
        $headers = [];
        foreach (self::HEADERS as $key => $name) {
            $value = $settings[$key] ?? false;
            if ($value === false || $value === '' || $value === []) {
                continue;
            }
            if ($key === 'strictTransportSecurity' && !$https) {
                continue;
            }
            if ($key === 'contentSecurityPolicy') {
                $directives = self::directives($value);
                foreach ((array)($settings['contentSecurityPolicyPresets'] ?? []) as $preset) {
                    $directives = self::addSources($directives, self::PRESETS[$preset]);
                }
                $directives = self::addSources($directives, (array)($settings['contentSecurityPolicySources'] ?? []));
                if ($nonce !== null && !empty($settings['contentSecurityPolicyNonce'])) {
                    // script-src-elem replaces script-src for the <script> elements
                    $nonced = ['script-src' => "'nonce-" . $nonce . "'"];
                    if (isset($directives['script-src-elem'])) {
                        $nonced['script-src-elem'] = $nonced['script-src'];
                    }
                    $directives = self::addSources($directives, $nonced);
                }
                if (is_string($settings['reportUri'] ?? null) && $settings['reportUri'] !== '') {
                    $directives['report-uri'] = [$settings['reportUri']];
                }
                $value = self::policy($directives);
                if (!empty($settings['contentSecurityPolicyReportOnly'])) {
                    $name = 'Content-Security-Policy-Report-Only';
                }
            }
            $headers[$name] = (string)$value;
        }

        return $headers;
    }

    /**
     * A new nonce, for the `nonce` attributes of the inline scripts of a response
     *
     * @return string
     */
    public static function nonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * The directives of a policy, from a string or an array of directives
     *
     * @param array<string, string|array<string>>|string $policy Policy, e.g. `default-src 'self'; img-src 'self' data:`
     * @return array<string, array<string>> Sources by directive
     */
    public static function directives(array|string $policy): array
    {
        if (is_string($policy)) {
            $policy = array_reduce(explode(';', $policy), static function (array $directives, string $directive): array {
                $tokens = preg_split('/\s+/', trim($directive), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if ($tokens !== []) {
                    $directives[strtolower(array_shift($tokens))] ??= $tokens;
                }

                return $directives;
            }, []);
        }

        return array_map(static fn($sources): array => array_values((array)$sources), $policy);
    }

    /**
     * Adds sources to the directives. A directive that the policy does not have starts from the sources of the
     * directive that the browsers use instead, e.g. `default-src` for `script-src`, so that adding a site to it does
     * not disallow the others.
     *
     * @param array<string, array<string>> $directives Sources by directive
     * @param array<string, string|array<string>> $sources Sources to add by directive
     * @return array<string, array<string>>
     */
    public static function addSources(array $directives, array $sources): array
    {
        foreach ($sources as $directive => $added) {
            $directive = strtolower((string)$directive);
            if (!isset($directives[$directive])) {
                $directives[$directive] = [];
                foreach (self::FALLBACKS[$directive] ?? [] as $fallback) {
                    if (isset($directives[$fallback])) {
                        $directives[$directive] = $directives[$fallback];
                        break;
                    }
                }
            }
            $current = array_values(array_diff($directives[$directive], ["'none'"]));
            $directives[$directive] = array_values(array_unique([...$current, ...(array)$added]));
        }

        return $directives;
    }

    /**
     * The policy of the directives
     *
     * @param array<string, array<string>> $directives Sources by directive
     * @return string
     */
    public static function policy(array $directives): string
    {
        $policy = [];
        foreach ($directives as $directive => $sources) {
            $policy[] = trim($directive . ' ' . implode(' ', $sources));
        }

        return implode('; ', $policy);
    }
}
