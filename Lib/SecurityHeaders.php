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
        'contentSecurityPolicy' => "default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'",
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

        return $settings + self::DEFAULTS;
    }

    /**
     * The headers to send, by name
     *
     * @param array<string, mixed> $settings Settings of `settings()`
     * @param bool $https Whether the request is of HTTPS: `Strict-Transport-Security` is sent on HTTPS only
     * @return array<string, string>
     */
    public static function headers(array $settings, bool $https): array
    {
        $headers = [];
        foreach (self::HEADERS as $key => $name) {
            $value = $settings[$key] ?? false;
            if ($value === false || $value === '') {
                continue;
            }
            $value = (string)$value;
            if ($key === 'strictTransportSecurity' && !$https) {
                continue;
            }
            if ($key === 'contentSecurityPolicy') {
                if (is_string($settings['reportUri'] ?? null) && $settings['reportUri'] !== '') {
                    $value = rtrim($value, '; ') . '; report-uri ' . $settings['reportUri'];
                }
                if (!empty($settings['contentSecurityPolicyReportOnly'])) {
                    $name = 'Content-Security-Policy-Report-Only';
                }
            }
            $headers[$name] = $value;
        }

        return $headers;
    }
}
