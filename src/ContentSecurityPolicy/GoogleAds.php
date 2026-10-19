<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Ads: the conversions, the remarketing, the conversion linker and the user data, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 *
 * Google Ads also uses the domain of the country of the user: add yours to `contentSecurityPolicyAllow`,
 * e.g. `https://www.google.co.jp` to `img-src` and `connect-src`.
 */
class GoogleAds implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://www.google.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://googleads.g.doubleclick.net', 'https://pagead2.googlesyndication.com', 'https://www.google.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://www.googleadservices.com', 'https://googleads.g.doubleclick.net', 'https://pagead2.googlesyndication.com', 'https://www.google.com', 'https://ad.doubleclick.net'],
            'frame-src' => ['https://www.googletagmanager.com'],
        ];
    }
}
