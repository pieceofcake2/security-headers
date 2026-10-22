# CHANGELOG

## Unreleased

- `contentSecurityPolicyStrictDynamic` adds `worker-src` with the sources of `script-src` before the change, when the
  policy has no `worker-src`: the workers used `script-src` with `https:` instead

## 1.2.0

- New setting `contentSecurityPolicyStrictDynamic`: adds `'strict-dynamic'` to `script-src` and replaces its hosts
  with `https:`, for the browsers without it. The scripts that the scripts of the nonce load are allowed from any
  host, and every `<script>` element of the HTML needs the nonce. It turns on the nonce
- New helper `SecurityHeadersHtmlHelper`, an `HtmlHelper` that gives the nonce to the scripts of `script()`,
  `scriptBlock()` and `scriptStart()`: `'Html' => ['className' => 'SecurityHeaders.SecurityHeadersHtml']`
- Composer loads the classes of the plugin (`Controller/`, `Lib/`, `Routing/` and `View/` in the classmap), which no
  longer call `App::uses()`, deprecated in pieceofcake2/cakephp 2.13. The tests also run with the `2.x` branch of
  pieceofcake2/cakephp

## 1.1.0

- `contentSecurityPolicyAllow` takes instances of the classes, e.g. `new GoogleTagManager()`, which the docs now use.
  The names of the classes without arguments, e.g. `GoogleTagManager::class`, are still accepted
- `GoogleAnalyticsWithAds` and `GoogleAds` take the countries of the users, for the domains of Google of their
  countries: `new GoogleAnalyticsWithAds(countries: ['jp'])` adds `https://*.google.co.jp` to `img-src` and
  `connect-src`. `GoogleDomains` has the domains of Google by country, from https://www.google.com/supported_domains

## 1.0.8

- New class of `contentSecurityPolicyAllow`: `GoogleTranslate` (the Translate Element library of Google Translate, of
  the website widget and of the translation of pages in Chrome on iOS)

## 1.0.7

- `MetaPixel` allows `https://connect.facebook.net` in `img-src`: the pixel logs its errors with an image of
  `https://connect.facebook.net/log/error`, which the policy blocked

## 1.0.6

- New class of `contentSecurityPolicyAllow`: `GoogleFonts` (the stylesheets of `fonts.googleapis.com` and the font
  files of `fonts.gstatic.com`)

## 1.0.5

- `MetaPixel` allows `https://www.facebook.com` in `frame-src` and `form-action`: the pixel posts large events with a
  form in a hidden frame to `https://www.facebook.com/tr/`, which the policy blocked

## 1.0.4

- `YahooAds`, the deprecated name of `YahooJapanAds`, is removed

## 1.0.3

- `YahooAds` is renamed `YahooJapanAds`: its tags are those of Yahoo! JAPAN Ads, not of the ads of Yahoo.
  `YahooAds` still works, but is deprecated
- The presets whose code has an inline script, e.g. `GoogleTagManager` and `MetaPixel`, turn on the nonce
  (`NeedsNonce`): `contentSecurityPolicyNonce` is `null` by default, `true` and `false` still turn it on and off
- A class of `contentSecurityPolicyAllow` that needs arguments to be created throws an
  `InvalidArgumentException` with its name, instead of an `ArgumentCountError`
- The docs said that PHP sends `Cache-Control: no-store` with the pages with a session: CakePHP 2 turns it off
  for Internet Explorer 8 and before. They now show how to turn it on with `Session.cacheLimiter`
- The docs explain the nonce for every inline script, and the snippets of Google Tag Manager and Google
  Analytics are with their presets
- With `contentSecurityPolicyNonce`, an element rendered with `requestAction()` gets the nonce of the page: it got
  a nonce of its own, which the policy of the page did not allow, so its inline scripts were blocked
- A value of another type than a setting takes (e.g. an array for a header, `1` for `true`) and an unknown
  directive of the policy, e.g. `scirpt-src`, throw an `InvalidArgumentException`, instead of sending `Array` or a
  directive that the browsers ignore

## 1.0.2

- `TikTok` allows `*.tiktokcdn-us.com`: TikTok serves the files of its embeds from there to the users in the
  United States, and from `*.ttwstatic.com` in Japan, so the embeds were blocked for American visitors
- The presets have a page of their own in the docs, with the sources of each one, where they come from and
  whether the weekly check covers it
- The presets are checked every week and at each release, in Chrome against the services
- The links of the Japanese docs to their sections work

## 1.0.1

- New classes of `contentSecurityPolicyAllow`: `GoogleMaps` (Maps JavaScript API), `GoogleMapsEmbed`, `YouTube`,
  `Facebook` (JavaScript SDK and social plugins), `MetaPixel`, `TikTok`, `TikTokPixel`, `Line` (social plugins),
  `LineTag`, `Instagram`, `X`, `XPixel`, `YahooAds`, `MicrosoftAds`, `AdobeTags`, `AdobeAnalytics` and
  `AdobeWebSdk`

## 1.0.0

First release of `pieceofcake2/security-headers`.

- `SecurityHeadersFilter`, a dispatcher filter that adds the security headers to every response of
  the application, including the redirects, the error pages and the files of the plugins and themes
- `Content-Security-Policy` (sent as `Content-Security-Policy-Report-Only` by default),
  `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`,
  `Cross-Origin-Opener-Policy` and `Cross-Origin-Resource-Policy` by default, and `X-Powered-By`
  removed
- `contentSecurityPolicyAllow` to allow other sites in the Content Security Policy, by class of a
  service or by name, without rewriting the policy, which can also be written as an array of
  directives; `SecurityHeadersComponent::addSources()` for an action
- `Strict-Transport-Security`, `Cache-Control`, `Cross-Origin-Embedder-Policy` and
  `X-XSS-Protection` off by default
- `SecurityHeadersComponent` to change or disable a header in an action
- Classes of the sources of Google Tag Manager, Google Analytics, Google Ads, Floodlight and Google
  Merchant Center, the `Sources` interface for your own ones, and a nonce of each request for their
  inline scripts, with `SecurityHeadersHelper::nonce()`
- Documentation in English and Japanese
