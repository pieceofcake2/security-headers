# Presets

The presets are the classes of the plugin, in `PieceOfCake2\SecurityHeaders\ContentSecurityPolicy`, with the
sources that a service needs in the Content Security Policy. List those that your pages use in
`contentSecurityPolicyAllow` (see [Allowing Other Sites](README.md#allowing-other-sites)):

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\YouTube;

Configure::write('SecurityHeaders', [
    'contentSecurityPolicyAllow' => [GoogleTagManager::class, YouTube::class],
]);
```

- The sources come from the guides of the services when they publish one, and otherwise from their embed codes
  and base codes, opened with the enforced policy in Chrome.
- **Checked every week:** the presets marked so are opened in Chrome with their policy, against the services, every
  week and at each release; the check fails when the browser reports a violation. The others need an account of
  the service to run.
- The embed and the conversion tag of a service are separate presets, e.g. `Facebook` for the Page plugin and
  `MetaPixel` for the conversions of the ads: list only those that your pages use.
- The scripts are in `script-src` instead of `script-src-elem` of the guides of Google, so that the browsers
  without `script-src-elem`, such as Safari before 15.4, allow them too.
- The services change their hosts from time to time. If the console of the browser reports a host that a preset
  does not have, add it to `contentSecurityPolicyAllow` with a name, and tell us in an issue.

- [Tag Managers and Analytics](#tag-managers-and-analytics): [`GoogleTagManager`](#googletagmanager), [`GoogleTagManagerPreview`](#googletagmanagerpreview), [`GoogleAnalytics`](#googleanalytics), [`GoogleAnalyticsWithAds`](#googleanalyticswithads), [`AdobeTags`](#adobetags), [`AdobeAnalytics`](#adobeanalytics), [`AdobeWebSdk`](#adobewebsdk)
- [Conversion Tags (CV) of the Ads](#conversion-tags): [`GoogleAds`](#googleads), [`Floodlight`](#floodlight), [`MerchantCenter`](#merchantcenter), [`MetaPixel`](#metapixel), [`TikTokPixel`](#tiktokpixel), [`XPixel`](#xpixel), [`LineTag`](#linetag), [`YahooAds`](#yahooads), [`MicrosoftAds`](#microsoftads)
- [Embedded Contents](#embeds): [`GoogleMaps`](#googlemaps), [`GoogleMapsEmbed`](#googlemapsembed), [`YouTube`](#youtube), [`Facebook`](#facebook), [`Instagram`](#instagram), [`X`](#x), [`TikTok`](#tiktok), [`Line`](#line)

## Tag Managers and Analytics {#tag-managers-and-analytics}

### `GoogleTagManager` {#googletagmanager}

The container of Google Tag Manager.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp). **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://www.google.com` |

Its inline snippet needs the nonce: see [Google Tag Manager and Google Analytics](README.md#google-tag-manager-and-google-analytics).

### `GoogleTagManagerPreview` {#googletagmanagerpreview}

The preview mode of Google Tag Manager.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` `https://tagmanager.google.com` |
| `style-src` | `https://www.googletagmanager.com` `https://tagmanager.google.com` `https://fonts.googleapis.com` |
| `img-src` | `https://www.googletagmanager.com` `https://ssl.gstatic.com` `https://www.gstatic.com` |
| `font-src` | `https://fonts.gstatic.com` `data:` |

### `GoogleAnalytics` {#googleanalytics}

Google Analytics, without the features of Google Ads.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp). **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` |

### `GoogleAnalyticsWithAds` {#googleanalyticswithads}

Google Analytics, with the features of Google Ads (Google Signals, remarketing).

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` `https://*.g.doubleclick.net` |
| `connect-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` `https://*.g.doubleclick.net` `https://pagead2.googlesyndication.com` |
| `frame-src` | `https://www.googletagmanager.com` |

Add the domain of the country of your users, e.g. `https://*.google.co.jp` to `img-src` and `connect-src`: a policy cannot have a wildcard at the end of a host.

### `AdobeTags` {#adobetags}

The library of Adobe Experience Platform Tags (formerly Adobe Launch) on the host of Adobe.

From [Adobe's guide](https://experienceleague.adobe.com/en/docs/experience-platform/tags/client-side/content-security-policy).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://assets.adobedtm.com` |

Load the library asynchronously. Add the classes of its extensions, e.g. `AdobeAnalytics`, and the nonce for its custom code: set a data element of the nonce in the Core extension.

### `AdobeAnalytics` {#adobeanalytics}

Adobe Analytics (AppMeasurement) with the Experience Cloud Identity Service.

From [Adobe's guide](https://experienceleague.adobe.com/en/docs/id-service/using/reference/csp).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `img-src` | `https://*.omtrdc.net` `https://*.2o7.net` `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `connect-src` | `https://*.omtrdc.net` `https://*.2o7.net` `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `frame-src` | `https://*.demdex.net` |

Add your tracking server if it is on a domain of yours (a CNAME).

### `AdobeWebSdk` {#adobewebsdk}

The Web SDK of Adobe Experience Platform; `*.demdex.net` is for the migration of the visitors (`idMigrationEnabled`).

From [Adobe's guide](https://experienceleague.adobe.com/en/docs/experience-platform/web-sdk/use-cases/configuring-a-csp).

| Directive | Sources |
| --- | --- |
| `connect-src` | `https://*.adobedc.net` `https://*.demdex.net` |

Add your edge domain if `edgeDomain` is a domain of yours (a CNAME).

## Conversion Tags (CV) of the Ads {#conversion-tags}

### `GoogleAds` {#googleads}

Google Ads: the conversions, the remarketing, the conversion linker and the user data.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://www.google.com` |
| `img-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://googleads.g.doubleclick.net` `https://pagead2.googlesyndication.com` `https://www.google.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://googleads.g.doubleclick.net` `https://pagead2.googlesyndication.com` `https://www.google.com` `https://ad.doubleclick.net` |
| `frame-src` | `https://www.googletagmanager.com` |

Add the domain of the country of your users, e.g. `https://www.google.co.jp` to `img-src` and `connect-src`.

### `Floodlight` {#floodlight}

Floodlight, with its custom scripts.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp).

| Directive | Sources |
| --- | --- |
| `img-src` | `https://www.googletagmanager.com` `https://ad.doubleclick.net` `https://ade.googlesyndication.com` `https://adservice.google.com` |
| `connect-src` | `https://pagead2.googlesyndication.com` `https://www.google.com` `https://www.googleadservices.com` `https://ad.doubleclick.net` |
| `frame-src` | `https://www.googletagmanager.com` `https://*.fls.doubleclick.net` |

### `MerchantCenter` {#merchantcenter}

Google Merchant Center.

From [Google's guide](https://developers.google.com/tag-platform/security/guides/csp).

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://*.merchant-center-analytics.goog` |
| `connect-src` | `https://*.merchant-center-analytics.goog` |

### `MetaPixel` {#metapixel}

The Meta Pixel (Facebook Pixel), for the ads of Facebook and Instagram.

From [the base code of Meta](https://developers.facebook.com/docs/meta-pixel/get-started). **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://connect.facebook.net` |
| `img-src` | `https://www.facebook.com` |
| `connect-src` | `https://www.facebook.com` |

### `TikTokPixel` {#tiktokpixel}

The TikTok Pixel.

From the base code of TikTok. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://analytics.tiktok.com` |
| `img-src` | `https://analytics.tiktok.com` |
| `connect-src` | `https://analytics.tiktok.com` `https://analytics-ipv6.tiktokw.us` |

### `XPixel` {#xpixel}

The X Pixel (formerly Twitter Pixel) of X Ads.

From the base code of X. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://static.ads-twitter.com` |
| `img-src` | `https://analytics.twitter.com` `https://t.co` |
| `connect-src` | `https://analytics.twitter.com` `https://t.co` |

### `LineTag` {#linetag}

The LINE Tag of LINE Ads.

From the base code of LINE. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://d.line-scdn.net` `https://tag.flvcdn.net` |
| `img-src` | `https://tr.line.me` |
| `connect-src` | `https://tr.line.me` |

### `YahooAds` {#yahooads}

The conversion tags and the site retargeting tags of Yahoo! JAPAN Ads, of the search ads and the display ads, with the site general tag (ytag.js).

From [the announcement of Yahoo! JAPAN Ads](https://www.lycbiz.com/jp/news/yahoo-ads/20230215) and the tags. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://s.yimg.jp` `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` |
| `img-src` | `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` `https://apm.yahoo.co.jp` |
| `connect-src` | `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` `https://apm.yahoo.co.jp` `https://dm.slim02.jp` |

The tags of the search ads load their scripts from and send their events to `b9x.yahoo.co.jp`; those of the display ads send theirs to `apm.yahoo.co.jp`, and the site general tag some to `dm.slim02.jp`.

### `MicrosoftAds` {#microsoftads}

The UET tag of Microsoft Advertising.

From the base code of Microsoft. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://bat.bing.com` |
| `img-src` | `https://bat.bing.com` `https://bat.bing.net` |
| `connect-src` | `https://bat.bing.com` `https://bat.bing.net` |

## Embedded Contents {#embeds}

### `GoogleMaps` {#googlemaps}

The Maps JavaScript API of Google Maps.

From [the guide of the Maps JavaScript API](https://developers.google.com/maps/documentation/javascript/content-security-policy) (the allowlist policy). **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `'unsafe-eval'` `https://*.googleapis.com` `https://*.gstatic.com` `https://*.google.com` `https://*.ggpht.com` `https://*.googleusercontent.com` `blob:` |
| `img-src` | `https://*.googleapis.com` `https://*.gstatic.com` `https://*.google.com` `https://*.googleusercontent.com` `data:` |
| `frame-src` | `https://*.google.com` |
| `connect-src` | `https://*.googleapis.com` `https://*.google.com` `https://*.gstatic.com` `data:` `blob:` |
| `font-src` | `https://fonts.gstatic.com` |
| `style-src` | `'unsafe-inline'` `https://fonts.googleapis.com` |
| `worker-src` | `blob:` |

It adds `'unsafe-eval'` to `script-src` and `'unsafe-inline'` to `style-src`, as the guide. The `'unsafe-inline'` of `script-src` of the guide is left out: the inline script, e.g. the callback, needs the nonce. If you load the API from the domain of a country, e.g. `maps.google.fr`, add it.

### `GoogleMapsEmbed` {#googlemapsembed}

A map of Google Maps embedded with an iframe: the Maps Embed API, or "Share" > "Embed a map".

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `frame-src` | `https://www.google.com` |

### `YouTube` {#youtube}

The embedded players of YouTube, of `www.youtube.com` and `www.youtube-nocookie.com` (the privacy-enhanced mode), the IFrame Player API and the thumbnails of `i.ytimg.com`.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.youtube.com` |
| `img-src` | `https://i.ytimg.com` |
| `frame-src` | `https://www.youtube.com` `https://www.youtube-nocookie.com` |

### `Facebook` {#facebook}

The JavaScript SDK of Facebook and its social plugins: the Page plugin, the embedded posts, the Like button, the comments.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://connect.facebook.net` |
| `frame-src` | `https://www.facebook.com` |
| `style-src` | `'unsafe-inline'` |

The SDK adds `<style>` elements, so it adds `'unsafe-inline'` to `style-src`. The plugins are not drawn in a headless Chrome, so the check covers the SDK only.

### `Instagram` {#instagram}

The embedded posts of Instagram.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.instagram.com` |
| `frame-src` | `https://www.instagram.com` |
| `style-src` | `'unsafe-inline'` |

The embed code has `style` attributes, so it adds `'unsafe-inline'` to `style-src`. The posts are not drawn in a headless Chrome, so the check covers the script only.

### `X` {#x}

The embedded posts, timelines and buttons of X (formerly Twitter); `syndication.twitter.com` is for the timelines.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://platform.twitter.com` |
| `frame-src` | `https://platform.twitter.com` `https://syndication.twitter.com` |
| `style-src` | `'unsafe-inline'` |

The script adds `<style>` elements, so it adds `'unsafe-inline'` to `style-src`. The posts are not drawn in a headless Chrome, so the check covers the script only.

### `TikTok` {#tiktok}

The embedded videos and profiles of TikTok.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.tiktok.com` `https://*.ttwstatic.com` `https://*.tiktokcdn-us.com` |
| `style-src` | `'unsafe-inline'` `https://*.ttwstatic.com` `https://*.tiktokcdn-us.com` |
| `frame-src` | `https://www.tiktok.com` |
| `connect-src` | `https://www.tiktok.com` |
| `img-src` | `data:` |

Its files come from `*.ttwstatic.com` in Japan and from `*.tiktokcdn-us.com` in the United States: TikTok picks the CDN by the country of the user. The embed code has a `style` attribute, so it adds `'unsafe-inline'` to `style-src`.

### `Line` {#line}

The social plugins of LINE: the "Share" and "Add friend" buttons.

From the embed code. **Checked every week.**

| Directive | Sources |
| --- | --- |
| `script-src` | `https://www.line-website.com` |
| `frame-src` | `https://social-plugins.line.me` |
| `style-src` | `'unsafe-inline'` |

The embed code has a `style` attribute and the script sets styles, so it adds `'unsafe-inline'` to `style-src`.
