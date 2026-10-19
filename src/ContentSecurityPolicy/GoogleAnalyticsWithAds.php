<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Analytics, with the features of Google Ads, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 *
 * The features of Google Ads also use the domain of the country of the user: add yours to
 * `contentSecurityPolicyAllow`, e.g. `https://*.google.co.jp` to `img-src` and `connect-src`.
 */
class GoogleAnalyticsWithAds implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net', 'https://pagead2.googlesyndication.com'],
            'frame-src' => ['https://www.googletagmanager.com'],
        ];
    }
}
