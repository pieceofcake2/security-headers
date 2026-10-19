<?php

App::uses('SecurityHeaders', 'SecurityHeaders.Lib');

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
            'contentSecurityPolicy' => "default-src 'self';",
            'contentSecurityPolicyReportOnly' => false,
            'reportUri' => '/csp-reports',
        ]), true);

        $this->assertSame("default-src 'self'; report-uri /csp-reports", $headers['Content-Security-Policy']);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
    }

    public function testContentSecurityPolicyOff(): void
    {
        $headers = SecurityHeaders::headers(SecurityHeaders::settings(['contentSecurityPolicy' => false]), true);

        $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
    }
}
