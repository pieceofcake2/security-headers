# Security Headers Plugin Documentation

The Security Headers plugin adds the security headers to every response of an application with a
dispatcher filter: the pages, the redirects, the error pages, the downloads and the files of the
plugins and themes. They tell the browsers to restrict what a page can do: which scripts it runs,
whether other sites can show it in a frame, and what it sends to other sites.

The headers that the web server or the CDN should send, and the ones that break pages easily, are
off by default. The Content Security Policy is sent in report-only mode by default.

- [Installation](#installation)
- [Settings](#settings)
- [Adding Sites to the Content Security Policy](#adding-sites-to-the-content-security-policy)
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
    'contentSecurityPolicySources' => [
        'script-src' => ['https://cdn.example.com'],
    ],
]);
```

On by default:

| Setting | Header | Default | What it does |
| --- | --- | --- | --- |
| `contentSecurityPolicy` | `Content-Security-Policy` | `default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'` | The sources of the scripts, styles, images, frames and forms of the page. It is the main defense against XSS. An array of the directives and their sources, or the policy as a string. |
| `contentSecurityPolicySources` | | `[]` | The sources added to the directives of the policy, e.g. other sites. See [Adding Sites to the Content Security Policy](#adding-sites-to-the-content-security-policy). |
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

## Adding Sites to the Content Security Policy

To allow the scripts, images or frames of other sites, add them with `contentSecurityPolicySources`,
by directive. The policy keeps its other directives and sources:

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicySources' => [
        'script-src' => ['https://cdn.example.com'],
        'img-src' => ['https://images.example.com', 'data:'],
        'frame-src' => ['https://www.youtube-nocookie.com'],
    ],
]);
// default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none';
// script-src 'self' https://cdn.example.com; img-src 'self' https://images.example.com data:;
// frame-src 'self' https://www.youtube-nocookie.com
```

A directive that the policy does not have starts from the sources of the one that the browsers use
instead, as `default-src` for `script-src`, so that `'self'` stays allowed. `'none'` is removed when
you add sources to a directive.

To write the whole policy, set `contentSecurityPolicy`, as an array or as a string:

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
   what is reported. Move inline scripts to files, or [add the sites](#adding-sites-to-the-content-security-policy)
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
  the settings, as `contentSecurityPolicySources` does.
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
