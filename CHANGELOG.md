# CHANGELOG

## 1.0.0 - Unreleased

First release of `pieceofcake2/security-headers`.

- `SecurityHeadersFilter`, a dispatcher filter that adds the security headers to every response of
  the application, including the redirects, the error pages and the files of the plugins and themes
- `Content-Security-Policy` (sent as `Content-Security-Policy-Report-Only` by default),
  `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`,
  `Cross-Origin-Opener-Policy` and `Cross-Origin-Resource-Policy` by default, and `X-Powered-By`
  removed
- `contentSecurityPolicySources` and `SecurityHeadersComponent::addSources()` to add sites to the
  directives of the Content Security Policy, which can also be written as an array of directives
- `Strict-Transport-Security`, `Cache-Control`, `Cross-Origin-Embedder-Policy` and
  `X-XSS-Protection` off by default
- `SecurityHeadersComponent` to change or disable a header in an action
- Presets of the sources of Google Tag Manager, Google Analytics, Google Ads, Floodlight and Google
  Merchant Center, and a nonce of each request for their inline scripts, with
  `SecurityHeadersHelper::nonce()`
- Documentation in English and Japanese
