<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Analytics, without the features of Google Ads, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 */
class GoogleAnalytics implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com'],
        ];
    }
}
