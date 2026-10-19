# Security Headers Plugin Documentation

The Security Headers plugin adds the security headers to every response of an application with a
dispatcher filter: the pages, the redirects, the error pages, the downloads and the files of the
plugins and themes. They tell the browsers to restrict what a page can do: which scripts it runs,
whether other sites can show it in a frame, and what it sends to other sites.

The headers that the web server or the CDN should send, and the ones that break pages easily, are
off by default. The Content Security Policy is sent in report-only mode by default.

- [Installation](#installation)
- [Settings](#settings)
- [Allowing Other Sites](#allowing-other-sites)
- [Google Tag Manager and Google Analytics](#google-tag-manager-and-google-analytics)
- [Headers for the Web Server or the CDN](#headers-for-the-web-server-or-the-cdn)
- [Enforcing the Content Security Policy](#enforcing-the-content-security-policy)
- [Changing the Headers in an Action](#changing-the-headers-in-an-action)
- [Notes](#notes)
- [Without the Plugin](#without-the-plugin)

## Installation

Requirements: PHP 8.0+, [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) 2.12+.

```bash
composer require pieceofcake2/security-headers
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory by
`composer/installers`. Set `installer-paths` in your application's `composer.json`:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

Load the plugin and add `SecurityHeaders.SecurityHeadersFilter` to the dispatcher filters in
`config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`). If you already use
`CakePlugin::loadAll()`, loading the plugin is not necessary:

```php
CakePlugin::load('SecurityHeaders');

Configure::write('Dispatcher.filters', [
    'AssetDispatcher',
    'CacheDispatcher',
    'SecurityHeaders.SecurityHeadersFilter',
]);
```

The filter runs before the other filters (its priority is 1, `AssetDispatcher` and
`CacheDispatcher` have 9), wherever it is in the list, so the files of the plugins and the cached
views get the headers too.

The files in `webroot/` do not reach CakePHP: the web server sends them, with its own headers.

## Settings

Change the settings with `Configure::write('SecurityHeaders', [...])` in `config/bootstrap.php`. A
setting is the value of its header, or `false` not to send it. The settings that you do not write
keep their defaults. An unknown setting throws an `InvalidArgumentException`, so that a typo does
not leave a header with its default silently.

```php
Configure::write('SecurityHeaders', [
    'xFrameOptions' => 'DENY',
    'contentSecurityPolicyAllow' => [
        'cdn' => ['script-src' => ['https://cdn.example.com']],
    ],
]);
```

On by default:

| Setting | Header | Default | What it does |
| --- | --- | --- | --- |
| `contentSecurityPolicy` | `Content-Security-Policy` | `default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'` | The sources of the scripts, styles, images, frames and forms of the page. It is the main defense against XSS. An array of the directives and their sources, or the policy as a string. |
| `contentSecurityPolicyAllow` | | `[]` | The other sites allowed by the policy: the classes of the services, e.g. `GoogleTagManager::class`, and the sources by directive with a name. See [Allowing Other Sites](#allowing-other-sites). |
| `contentSecurityPolicyNonce` | | `false` | Adds a new nonce of each request to `script-src`, for the inline scripts that have it in their `nonce` attribute. See [Google Tag Manager and Google Analytics](#google-tag-manager-and-google-analytics). |
| `contentSecurityPolicyReportOnly` | | `true` | Sends the policy as `Content-Security-Policy-Report-Only`: the browsers report the violations in the console (and to `reportUri`) without blocking them. See [Enforcing the Content Security Policy](#enforcing-the-content-security-policy). |
| `reportUri` | | `false` | A URL that receives the violation reports, added to the policy as `report-uri`. |
| `xFrameOptions` | `X-Frame-Options` | `SAMEORIGIN` | Whether other sites can show the page in a frame (clickjacking). `frame-ancestors` of the policy replaces it in modern browsers; this one is for the old ones. |
| `xContentTypeOptions` | `X-Content-Type-Options` | `nosniff` | The browsers do not guess the type of a response, e.g. run an uploaded text file as a script. |
| `referrerPolicy` | `Referrer-Policy` | `strict-origin-when-cross-origin` | Other sites get only the origin of the page in `Referer`, not its path and query, which can contain IDs or tokens. |
| `permissionsPolicy` | `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | The browser features that the page and its frames cannot use. |
| `crossOriginOpenerPolicy` | `Cross-Origin-Opener-Policy` | `same-origin` | A window of another site that the page opens, or that opens the page, cannot access it with `window.opener`. |
| `crossOriginResourcePolicy` | `Cross-Origin-Resource-Policy` | `same-origin` | Other sites cannot load the responses, e.g. the images or the JSON, with `<img>` or `<script>`. |
| `removePoweredBy` | `X-Powered-By` | `true` | Removes the PHP version that PHP adds when `expose_php` is on. |

Off by default:

| Setting | Header | Example | Why it is off |
| --- | --- | --- | --- |
| `strictTransportSecurity` | `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | For the web server or the CDN. See [below](#headers-for-the-web-server-or-the-cdn). |
| `cacheControl` | `Cache-Control` | `no-store` | For the web server or the CDN, and for the actions: PHP sends `no-store, no-cache, must-revalidate` for the pages with a session (the default `session.cache_limiter`), and the actions set their own with `$this->response->cache()` or `disableCache()`. |
| `crossOriginEmbedderPolicy` | `Cross-Origin-Embedder-Policy` | `require-corp` | Breaks the images, scripts and frames of other sites that do not send `Cross-Origin-Resource-Policy` or CORS headers. Only needed for `SharedArrayBuffer` and precise timers. |
| `xXssProtection` | `X-XSS-Protection` | `0` | Obsolete: the modern browsers ignore it, and the filter of the old ones could be abused. If a scanner asks for it, send `0`. |

The plugin does not remove `Server`, which the web server sends: turn it off in the web server
(`server_tokens off;` in nginx, `ServerTokens Prod` and `ServerSignature Off` in Apache).

## Allowing Other Sites

To allow the scripts, images or frames of other sites, list them in `contentSecurityPolicyAllow`: the
classes of the services, and the sources by directive with a name of your choice. The policy keeps
its other directives and sources:

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleAnalytics;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;

Configure::write('SecurityHeaders', [
    'contentSecurityPolicyAllow' => [
        GoogleTagManager::class,
        GoogleAnalytics::class,
        'cdn' => [
            'script-src' => ['https://cdn.example.com'],
        ],
        'youtube' => [
            'frame-src' => ['https://www.youtube-nocookie.com'],
        ],
    ],
]);
```

A directive that the policy does not have starts from the sources of the one that the browsers use
instead, as `default-src` for `script-src`, so that `'self'` stays allowed. `'none'` is removed when
you add sources to a directive. With the default policy, the sites of `cdn` and `youtube` above give:

```text
default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none';
script-src 'self' https://cdn.example.com; frame-src 'self' https://www.youtube-nocookie.com
```

The classes of the plugin, in `PieceOfCake2\SecurityHeaders\ContentSecurityPolicy`, are the sources
that the services need. Those of Google's tags are from
[Google's guide](https://developers.google.com/tag-platform/security/guides/csp), `GoogleMaps` from
[the guide of the Maps JavaScript API](https://developers.google.com/maps/documentation/javascript/content-security-policy),
those of Adobe from [Adobe's guides](https://experienceleague.adobe.com/en/docs/experience-platform/tags/client-side/content-security-policy),
and the others from the embed codes of the services, checked with an enforced policy in Chrome:

**Tag managers and analytics**

| Class | For |
| --- | --- |
| `GoogleTagManager` | The container of Google Tag Manager |
| `GoogleTagManagerPreview` | The preview mode of Google Tag Manager |
| `GoogleAnalytics` | Google Analytics, without the features of Google Ads |
| `GoogleAnalyticsWithAds` | Google Analytics, with the features of Google Ads (Google Signals, remarketing) |
| `AdobeTags` | The library of Adobe Experience Platform Tags (formerly Adobe Launch) |
| `AdobeAnalytics` | Adobe Analytics (AppMeasurement) with the Experience Cloud Identity Service |
| `AdobeWebSdk` | The Web SDK of Adobe Experience Platform |

**Conversion tags (CV) of the ads**

| Class | For |
| --- | --- |
| `GoogleAds` | Google Ads: the conversions, the remarketing, the conversion linker and the user data |
| `Floodlight` | Floodlight, with its custom scripts |
| `MerchantCenter` | Google Merchant Center |
| `MetaPixel` | The Meta Pixel (Facebook Pixel), also for Instagram ads |
| `TikTokPixel` | The TikTok Pixel |
| `XPixel` | The X Pixel (formerly Twitter Pixel) |
| `LineTag` | The LINE Tag of LINE Ads |

**Embedded contents**

| Class | For |
| --- | --- |
| `GoogleMaps` | The Maps JavaScript API of Google Maps |
| `GoogleMapsEmbed` | A map of Google Maps embedded with an iframe (Maps Embed API, or "Share" > "Embed a map") |
| `YouTube` | The embedded players of YouTube (also `youtube-nocookie.com`), the IFrame Player API and the thumbnails |
| `Facebook` | The JavaScript SDK of Facebook and its social plugins (Page plugin, embedded posts, Like button, ...) |
| `Instagram` | The embedded posts of Instagram |
| `X` | The embedded posts, timelines and buttons of X (formerly Twitter) |
| `TikTok` | The embedded videos and profiles of TikTok |
| `Line` | The social plugins of LINE ("Share" and "Add friend" buttons) |

They add the scripts to `script-src` instead of `script-src-elem` of Google's guide, so that the browsers
without `script-src-elem`, such as Safari before 15.4, allow them too. Some services need more than sites:

- `GoogleMaps` adds `'unsafe-eval'` to `script-src` and `'unsafe-inline'` to `style-src`, as Google's guide does.
  Its inline script, e.g. the callback of the API, needs the nonce.
- `Facebook`, `Instagram`, `X`, `TikTok` and `Line` add `'unsafe-inline'` to `style-src`: their scripts add
  styles to the page, and their embed codes have `style` attributes.
- `AdobeTags` is the library only: add the classes of its extensions, e.g. `AdobeAnalytics`, and the nonce for
  its custom code (a data element of the nonce in the Core extension). A tracking server or an edge domain on a
  domain of yours (a CNAME) is added with a name.
- The embeds and the conversion tags of a service are separate classes, e.g. `Facebook` for the Page plugin and
  `MetaPixel` for the conversions of Meta ads: list only those that the pages use.
- The services change their hosts from time to time. If the console of the browser reports a host that a class
  does not have, add it to `contentSecurityPolicyAllow` with a name.

To use the sources of a service in several applications, write a class of your own that implements
`Sources`, e.g. in `src/Lib/`, and list it in the same way:

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Sources;

class StripeSources implements Sources
{
    /**
     * The sources of Stripe.js
     *
     * @return array<string, array<string>>
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://js.stripe.com'],
            'frame-src' => ['https://js.stripe.com'],
        ];
    }
}
```

A class that does not implement `Sources`, or a value without a name, throws an
`InvalidArgumentException`.

To write the whole policy instead, set `contentSecurityPolicy`, as an array or as a string:

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicy' => [
        'default-src' => ["'self'"],
        'img-src' => ["'self'", 'data:'],
        'frame-ancestors' => ["'none'"],
        'form-action' => ["'self'"],
        'base-uri' => ["'self'"],
        'object-src' => ["'none'"],
    ],
]);
```

For some actions only, use [`addSources()` of the component](#changing-the-headers-in-an-action).

## Google Tag Manager and Google Analytics

Google Tag Manager and Google Analytics have an inline script, which needs the nonce:

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicyAllow' => [GoogleTagManager::class, GoogleAnalytics::class],
    'contentSecurityPolicyNonce' => true,
]);
```

Add the nonce to the inline script with `SecurityHeadersHelper`. Load the helper in
`AppController`:

```php
public $helpers = ['SecurityHeaders.SecurityHeaders'];
```

Then add `nonce` to the snippet of Google Tag Manager. This snippet also passes the nonce to the
script that it loads, as in Google's guide:

```php
<!-- In <head> of the layout -->
<script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;var n=d.querySelector('[nonce]');
n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
```

The snippet of Google Analytics without Tag Manager (`gtag.js`) needs it on its inline script too:
`<script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">`. Write the other inline scripts of your
pages in the same way, or with `$this->Html->scriptBlock($script, ['nonce' => $this->SecurityHeaders->nonce()])`.

- **The domains of the countries.** The features of Google Ads use the domain of the country of the
  user, e.g. `www.google.co.jp`, and a policy cannot have a wildcard at the end of a host. Add the
  ones of your users to `contentSecurityPolicyAllow`, e.g.
  `'google-jp' => ['img-src' => ['https://*.google.co.jp'], 'connect-src' => ['https://*.google.co.jp']]`
  for `GoogleAnalyticsWithAds` (`https://www.google.co.jp` for `GoogleAds`).
- **Custom JavaScript variables** of Google Tag Manager need `'unsafe-eval'` in `script-src`. Use
  custom templates instead if you can.
- **Without the nonce**, Google's guide allows the inline scripts with `'unsafe-inline'`, which allows
  the inline scripts of an XSS too. Add it to `contentSecurityPolicyAllow` only if you cannot
  add the nonce.
- **Caches.** A nonce is new for each response. A page cached with its HTML, by `CacheHelper` or by a
  CDN, has the nonce of another response, so its inline scripts are blocked.
- [Tag Assistant](https://tagassistant.google.com) lists the resources that the policy blocks, in
  *Page issues*.

## Headers for the Web Server or the CDN

Some headers belong to the web server, the load balancer or the CDN, and are off by default:

- `Strict-Transport-Security` (HSTS) tells the browsers to use only HTTPS for the site. It must be
  sent on every response, also the files of `webroot/`, and only over HTTPS. Behind a load balancer
  or a CDN that terminates TLS, the application sees HTTP and cannot tell. When you turn it on in the
  plugin, it is sent only when `$request->is('ssl')` is true.
- `Cache-Control` of the static files and the cached pages belongs to the CDN and the web server.

Turn them on in the plugin only if the application is served without a web server or a CDN that
can send them.

## Enforcing the Content Security Policy

The default policy allows only the scripts, styles and images of the site itself: inline
`<script>` blocks, `style` attributes, `onclick` handlers and the files of CDNs are blocked when
it is enforced. Most applications need some changes first, so it is sent as
`Content-Security-Policy-Report-Only` by default:

1. Open the pages of the application with the console of the browser open, and fix or allow
   what is reported. Move inline scripts to files, or [add the sites](#allowing-other-sites)
   that you need.
2. Optionally, collect the reports of the users' browsers with `reportUri`.
3. When nothing is reported any more, enforce the policy:

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicyReportOnly' => false,
]);
```

## Changing the Headers in an Action

Use `SecurityHeadersComponent` to change a header for some actions, e.g. a page that a partner site
shows in a frame:

```php
class WidgetsController extends AppController
{
    public $components = ['SecurityHeaders.SecurityHeaders'];

    /**
     * Shown in a frame of the partner site
     *
     * @return void
     */
    public function embed(): void
    {
        $this->SecurityHeaders->set('Content-Security-Policy', "frame-ancestors 'self' https://partner.example.com");
        $this->SecurityHeaders->disable('X-Frame-Options');
    }

    /**
     * Shows a video of YouTube
     *
     * @return void
     */
    public function video(): void
    {
        $this->SecurityHeaders->addSources('frame-src', 'https://www.youtube-nocookie.com');
    }
}
```

- `addSources($directive, $sources)` adds sources to a directive of the Content Security Policy of
  the settings, as `contentSecurityPolicyAllow` does.
- `set($name, $value)` replaces the header of the settings.
- `disable($name)` does not send the header in this response.

Write the name of the header that the filter sends: in report-only mode, it is
`Content-Security-Policy-Report-Only`.

## Notes

- **Popups of other sites.** With `Cross-Origin-Opener-Policy: same-origin`, a popup that you open
  for a login or a payment on another site cannot reach your page with `window.opener`. If such a
  flow breaks, use `same-origin-allow-popups`.
- **Resources for other sites.** With `Cross-Origin-Resource-Policy: same-origin`, other sites cannot
  load your images or files with `<img>` or `<script>`, e.g. images that you embed in emails or on
  another domain of yours. Use `same-site` or `cross-origin`, for the whole application or for those
  actions with the component. The requests with CORS, e.g. `fetch()` of an API, are not affected.
- **Error pages.** The error pages get a response of their own, so the filter also sends the headers
  with `header()`. A header of the same name that the application or CakePHP sends later replaces it.

## Without the Plugin

To send the headers yourself, add them to the response in `AppController::beforeFilter()`. They
are not sent with the files of the plugins and themes and the views cached by `CacheDispatcher`.

```php
/**
 * Adds the security headers to the response
 *
 * @return void
 */
public function beforeFilter(): void
{
    parent::beforeFilter();
    $this->response->header([
        'Content-Security-Policy' => "default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'",
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ]);
}
```

Or send them from the web server for every response, e.g. in nginx:

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```
