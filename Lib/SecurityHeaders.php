<?php
declare(strict_types=1);

use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\NeedsNonce;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Sources;

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
        // The sources allowed in addition: classes of ContentSecurityPolicy\Sources, or arrays of sources by name, e.g.
        // [GoogleTagManager::class, 'stripe' => ['script-src' => ['https://js.stripe.com']]]
        'contentSecurityPolicyAllow' => [],
        // A nonce of each request added to script-src, for the inline scripts: see nonce(). By default (null), on
        // when a class of contentSecurityPolicyAllow needs it (NeedsNonce)
        'contentSecurityPolicyNonce' => null,
        // 'strict-dynamic' in script-src: the scripts that the scripts of the nonce load are allowed, and the hosts of
        // script-src are replaced with https: for the browsers without 'strict-dynamic'. Turns on the nonce
        'contentSecurityPolicyStrictDynamic' => false,
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
     * The directives of the Content Security Policy, from CSP Level 3 and the specifications that it refers to
     */
    public const DIRECTIVES = [
        'default-src', 'script-src', 'script-src-elem', 'script-src-attr', 'style-src', 'style-src-elem',
        'style-src-attr', 'img-src', 'font-src', 'connect-src', 'media-src', 'object-src', 'frame-src', 'child-src',
        'worker-src', 'manifest-src', 'fenced-frame-src', 'base-uri', 'form-action', 'frame-ancestors', 'sandbox',
        'report-uri', 'report-to', 'upgrade-insecure-requests', 'block-all-mixed-content',
        'require-trusted-types-for', 'trusted-types', 'webrtc',
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
     * @throws \InvalidArgumentException When a setting is unknown or has a value of another type, or a directive of
     *   the policy is unknown
     */
    public static function settings(?array $settings = null): array
    {
        $settings ??= (array)(Configure::read('SecurityHeaders') ?: []);
        $unknown = array_diff(array_keys($settings), array_keys(self::DEFAULTS));
        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf('Unknown setting of SecurityHeaders: %s', implode(', ', $unknown)));
        }
        foreach ($settings as $key => $value) {
            $valid = match ($key) {
                'contentSecurityPolicy' => is_string($value) || is_array($value) || $value === false || $value === null,
                'contentSecurityPolicyAllow' => is_array($value),
                'contentSecurityPolicyNonce' => is_bool($value) || $value === null,
                'contentSecurityPolicyReportOnly', 'contentSecurityPolicyStrictDynamic', 'removePoweredBy' => is_bool($value),
                default => is_string($value) || $value === false || $value === null,
            };
            if (!$valid) {
                throw new InvalidArgumentException(sprintf('Invalid value of SecurityHeaders.%s: %s', $key, get_debug_type($value)));
            }
        }
        if (($settings['contentSecurityPolicyStrictDynamic'] ?? false) === true && ($settings['contentSecurityPolicyNonce'] ?? null) === false) {
            throw new InvalidArgumentException('SecurityHeaders.contentSecurityPolicyStrictDynamic needs the nonce: contentSecurityPolicyNonce is false');
        }
        $policy = $settings['contentSecurityPolicy'] ?? null;
        if (is_string($policy) || is_array($policy)) {
            self::assertDirectives(self::directives($policy), 'contentSecurityPolicy');
        }
        foreach (self::allowed((array)($settings['contentSecurityPolicyAllow'] ?? [])) as $name => $sources) {
            self::assertDirectives($sources, $name);
        }

        return $settings + self::DEFAULTS;
    }

    /**
     * Checks the names of the directives, so that a typo does not leave a directive that the browsers ignore
     *
     * @param array<string, mixed> $directives Sources by directive
     * @param string $of The setting or the name of the sources, for the message
     * @return void
     * @throws \InvalidArgumentException When a directive is unknown
     */
    private static function assertDirectives(array $directives, string $of): void
    {
        $unknown = array_diff(array_map(static fn($directive): string => strtolower((string)$directive), array_keys($directives)), self::DIRECTIVES);
        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf('Unknown directive of SecurityHeaders in %s: %s', $of, implode(', ', $unknown)));
        }
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
                foreach (self::allowed((array)($settings['contentSecurityPolicyAllow'] ?? [])) as $sources) {
                    $directives = self::addSources($directives, $sources);
                }
                if ($nonce !== null && self::usesNonce($settings)) {
                    $strictDynamic = !empty($settings['contentSecurityPolicyStrictDynamic']);
                    $added = ["'nonce-" . $nonce . "'"];
                    if ($strictDynamic) {
                        $added = [...$added, "'strict-dynamic'", 'https:'];
                    }
                    // script-src-elem replaces script-src for the <script> elements
                    $nonced = ['script-src' => $added];
                    if (isset($directives['script-src-elem'])) {
                        $nonced['script-src-elem'] = $added;
                    }
                    $directives = self::addSources($directives, $nonced);
                    if ($strictDynamic) {
                        foreach (array_keys($nonced) as $directive) {
                            $directives[$directive] = self::withoutHosts($directives[$directive]);
                        }
                    }
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
     * Whether the policy has a nonce: on with `contentSecurityPolicyStrictDynamic`, else `contentSecurityPolicyNonce`,
     * or by default whether a class of `contentSecurityPolicyAllow` needs it
     *
     * @param array<string, mixed> $settings Settings of `settings()`
     * @return bool
     */
    public static function usesNonce(array $settings): bool
    {
        $nonce = $settings['contentSecurityPolicyNonce'] ?? null;
        if (!empty($settings['contentSecurityPolicyStrictDynamic'])) {
            return true;
        }
        if (is_bool($nonce)) {
            return $nonce;
        }
        foreach ((array)($settings['contentSecurityPolicyAllow'] ?? []) as $name => $sources) {
            if (is_int($name) && ($sources instanceof NeedsNonce || (is_string($sources) && is_subclass_of($sources, NeedsNonce::class)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The sources allowed in addition, by name
     *
     * @param array<int|string, \PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Sources|string|array<string, string|array<string>>> $allow
     *   Instances or classes of `ContentSecurityPolicy\Sources`, or arrays of sources by directive by name
     * @return array<string, array<string, string|array<string>>>
     * @throws \InvalidArgumentException When a class is unknown, does not implement `ContentSecurityPolicy\Sources` or
     *   cannot be created without arguments
     */
    public static function allowed(array $allow): array
    {
        $allowed = [];
        foreach ($allow as $name => $sources) {
            if (is_int($name) && $sources instanceof Sources) {
                // Two instances of a class, e.g. with other arguments, keep both
                $key = $sources::class;
                $allowed[isset($allowed[$key]) ? $key . '#' . $name : $key] = $sources->sources();
            } elseif (is_int($name) && is_string($sources)) {
                if (!is_subclass_of($sources, Sources::class)) {
                    throw new InvalidArgumentException(sprintf('Not a class of %s in contentSecurityPolicyAllow of SecurityHeaders: %s', Sources::class, $sources));
                }
                $class = new ReflectionClass($sources);
                if (!$class->isInstantiable() || ($class->getConstructor()?->getNumberOfRequiredParameters() ?? 0) > 0) {
                    throw new InvalidArgumentException(sprintf('A class of contentSecurityPolicyAllow of SecurityHeaders must be created without arguments, or be given as an instance: %s', $sources));
                }
                $allowed[$sources] = (new $sources())->sources();
            } elseif (is_string($name) && is_array($sources)) {
                $allowed[$name] = $sources;
            } else {
                throw new InvalidArgumentException(sprintf('Neither an instance, a class nor sources by name in contentSecurityPolicyAllow of SecurityHeaders: %s', is_string($name) ? $name : var_export($sources, true)));
            }
        }

        return $allowed;
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
            $directives[$directive] = array_values(array_unique(array_merge($current, array_values((array)$added))));
        }

        return $directives;
    }

    /**
     * The sources without the hosts, e.g. `https://www.googletagmanager.com`, which the browsers ignore with
     * `'strict-dynamic'`: the keywords, nonces, hashes and schemes, e.g. `https:`, stay
     *
     * @param array<string> $sources Sources of a directive
     * @return array<string>
     */
    private static function withoutHosts(array $sources): array
    {
        return array_values(array_filter(
            $sources,
            static fn(string $source): bool => str_starts_with($source, "'") || preg_match('/^[a-z][a-z0-9+.-]*:$/i', $source) === 1,
        ));
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
