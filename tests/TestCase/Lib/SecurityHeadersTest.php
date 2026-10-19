<?php

use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Sources;

App::uses('SecurityHeaders', 'SecurityHeaders.Lib');

class SecurityHeadersTestStripe implements Sources
{
    public function sources(): array
    {
        return ['script-src' => ['https://js.stripe.com'], 'frame-src' => 'https://js.stripe.com'];
    }
}

class SecurityHeadersTest extends CakeTestCase
{
    public function tearDown(): void
    {
        Configure::delete('SecurityHeaders');
        parent::tearDown();
    }

    public function testDefaults(): void
    {
        $this->assertSame([
            'Content-Security-Policy-Report-Only' => "default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'",
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
        ], SecurityHeaders::headers(SecurityHeaders::settings(), true));
    }

    public function testSettingsOfConfigure(): void
    {
        Configure::write('SecurityHeaders', [
            'xFrameOptions' => 'DENY',
            'permissionsPolicy' => false,
            'crossOriginEmbedderPolicy' => 'require-corp',
        ]);
        $headers = SecurityHeaders::headers(SecurityHeaders::settings(), false);

        $this->assertSame('DENY', $headers['X-Frame-Options']);
        $this->assertArrayNotHasKey('Permissions-Policy', $headers);
        $this->assertSame('require-corp', $headers['Cross-Origin-Embedder-Policy']);
    }

    public function testUnknownSetting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown setting of SecurityHeaders: xFrameOption');
        SecurityHeaders::settings(['xFrameOption' => 'DENY']);
    }

    public function testEmptyValues(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings([
            'xFrameOptions' => null,
            'referrerPolicy' => '',
            'xXssProtection' => '0',
        ]), true);

        $this->assertArrayNotHasKey('X-Frame-Options', $headers);
        $this->assertArrayNotHasKey('Referrer-Policy', $headers);
        $this->assertSame('0', $headers['X-XSS-Protection']);
    }

    public function testStrictTransportSecurityOnHttpsOnly(): void
    {
        $settings = SecurityHeaders::settings(['strictTransportSecurity' => 'max-age=31536000; includeSubDomains']);

        $this->assertSame('max-age=31536000; includeSubDomains', SecurityHeaders::headers($settings, true)['Strict-Transport-Security']);
        $this->assertArrayNotHasKey('Strict-Transport-Security', SecurityHeaders::headers($settings, false));
    }

    public function testContentSecurityPolicyEnforced(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings([
            'contentSecurityPolicy' => ['default-src' => "'self'"],
            'contentSecurityPolicyReportOnly' => false,
            'reportUri' => '/csp-reports',
        ]), true);

        $this->assertSame("default-src 'self'; report-uri /csp-reports", $headers['Content-Security-Policy']);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
    }

    public function testContentSecurityPolicyOff(): void
    {
        foreach ([false, '', []] as $policy) {
            $headers = SecurityHeaders::headers(SecurityHeaders::settings(['contentSecurityPolicy' => $policy]), true);

            $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
            $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
        }
    }

    public function testContentSecurityPolicyAllow(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings([
            'contentSecurityPolicyAllow' => [
                'sites' => [
                    'script-src' => ['https://cdn.example.com'],
                    'img-src' => ['https://images.example.com', 'data:'],
                    'frame-ancestors' => 'https://partner.example.com',
                    'object-src' => ['https://objects.example.com'],
                ],
            ],
        ]), true);

        $this->assertSame(
            "default-src 'self'; frame-ancestors 'self' https://partner.example.com; form-action 'self'; base-uri 'self'; object-src https://objects.example.com; script-src 'self' https://cdn.example.com; img-src 'self' https://images.example.com data:",
            $headers['Content-Security-Policy-Report-Only'],
        );
    }

    /**
     * A directive without one of the policy starts from the one that the browsers use instead
     *
     * @return void
     */
    public function testAddSourcesOfFallbacks(): void
    {
        $directives = SecurityHeaders::addSources(
            ['default-src' => ["'self'"], 'script-src' => ["'self'", 'https://cdn.example.com'], 'child-src' => ["'none'"]],
            ['worker-src' => 'blob:', 'frame-src' => 'https://www.youtube-nocookie.com', 'script-src-elem' => 'https://cdn.example.com', 'sandbox' => []],
        );

        $this->assertSame(['blob:'], $directives['worker-src']);
        $this->assertSame(['https://www.youtube-nocookie.com'], $directives['frame-src']);
        $this->assertSame(["'self'", 'https://cdn.example.com'], $directives['script-src-elem']);
        $this->assertSame([], $directives['sandbox']);
    }

    public function testContentSecurityPolicyOfString(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings([
            'contentSecurityPolicy' => "default-src 'self'; IMG-SRC 'self'  data:;; upgrade-insecure-requests;",
            'contentSecurityPolicyAllow' => ['images' => ['img-src' => 'https://images.example.com']],
        ]), true);

        $this->assertSame(
            "default-src 'self'; img-src 'self' data: https://images.example.com; upgrade-insecure-requests",
            $headers['Content-Security-Policy-Report-Only'],
        );
    }

    public function testContentSecurityPolicyAllowClasses(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings([
            'contentSecurityPolicy' => ['default-src' => ["'self'"]],
            'contentSecurityPolicyAllow' => [
                GoogleTagManager::class,
                SecurityHeadersTestStripe::class,
                'api' => ['connect-src' => 'https://api.example.com'],
            ],
        ]), true);

        $this->assertSame(
            "default-src 'self'; script-src 'self' https://www.googletagmanager.com https://js.stripe.com; img-src 'self' https://www.googletagmanager.com; connect-src 'self' https://www.googletagmanager.com https://www.google.com https://api.example.com; frame-src 'self' https://js.stripe.com",
            $headers['Content-Security-Policy-Report-Only'],
        );
    }

    public function testEveryClassOfThePlugin(): void
    {
        $classes = array_map(static fn(string $file): string => 'PieceOfCake2\\SecurityHeaders\\ContentSecurityPolicy\\' . basename($file, '.php'), glob(dirname(__DIR__, 3) . '/Lib/ContentSecurityPolicy/*.php') ?: []);
        $classes = array_values(array_filter($classes, static fn(string $class): bool => $class !== Sources::class));

        $this->assertCount(14, $classes);
        foreach ($classes as $class) {
            $policy = SecurityHeaders::headers(SecurityHeaders::settings(['contentSecurityPolicyAllow' => [$class]]), true)['Content-Security-Policy-Report-Only'];

            $this->assertStringStartsWith("default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'; ", $policy, $class);
            $this->assertStringNotContainsString('script-src-elem', $policy, $class);
        }
    }

    public function testNotAClassOfSources(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a class of PieceOfCake2\\SecurityHeaders\\ContentSecurityPolicy\\Sources in contentSecurityPolicyAllow of SecurityHeaders: GoogleTagManger');
        SecurityHeaders::settings(['contentSecurityPolicyAllow' => [GoogleTagManager::class, 'GoogleTagManger']]);
    }

    public function testNeitherAClassNorSources(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Neither a class nor sources by name in contentSecurityPolicyAllow of SecurityHeaders: stripe');
        SecurityHeaders::settings(['contentSecurityPolicyAllow' => ['stripe' => 'https://js.stripe.com']]);
    }

    public function testNonce(): void
    {
        $settings = SecurityHeaders::settings([
            'contentSecurityPolicy' => ['default-src' => ["'self'"]],
            'contentSecurityPolicyReportOnly' => false,
            'contentSecurityPolicyNonce' => true,
        ]);

        $this->assertSame("default-src 'self'; script-src 'self' 'nonce-abc'", SecurityHeaders::headers($settings, true, 'abc')['Content-Security-Policy']);
        $this->assertSame("default-src 'self'", SecurityHeaders::headers($settings, true)['Content-Security-Policy']);
        $settings['contentSecurityPolicyAllow'] = ['cdn' => ['script-src-elem' => 'https://cdn.example.com']];
        $this->assertSame(
            "default-src 'self'; script-src-elem 'self' https://cdn.example.com 'nonce-abc'; script-src 'self' 'nonce-abc'",
            SecurityHeaders::headers($settings, true, 'abc')['Content-Security-Policy'],
        );
        $settings['contentSecurityPolicyNonce'] = false;
        $this->assertStringNotContainsString('nonce', SecurityHeaders::headers($settings, true, 'abc')['Content-Security-Policy']);
    }

    public function testNewNonce(): void
    {
        $nonce = SecurityHeaders::nonce();

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]{22}==$/', $nonce);
        $this->assertNotSame($nonce, SecurityHeaders::nonce());
    }
}
