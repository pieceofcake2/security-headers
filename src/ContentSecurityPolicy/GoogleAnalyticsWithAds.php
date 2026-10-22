<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Analytics, with the features of Google Ads, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 *
 * The features of Google Ads also use the domain of the country of the user: give the countries of your users, e.g.
 * `new GoogleAnalyticsWithAds(countries: ['jp'])` adds `https://*.google.co.jp` to `img-src` and `connect-src`.
 * The users of the other countries use theirs, which the policy blocks
 */
class GoogleAnalyticsWithAds implements NeedsNonce
{
    /**
     * @param array<string> $countries Countries of the users, for their domains of Google, e.g. `['jp']`
     *   (`GoogleDomains`)
     * @throws \InvalidArgumentException When Google has no domain for a country
     */
    public function __construct(private array $countries = [])
    {
        foreach ($countries as $country) {
            GoogleDomains::domain($country);
        }
    }

    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        $countries = array_map(static fn(string $country): string => 'https://*.' . GoogleDomains::domain($country), $this->countries);

        return [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net', ...$countries],
            'connect-src' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com', 'https://*.g.doubleclick.net', 'https://pagead2.googlesyndication.com', ...$countries],
            'frame-src' => ['https://www.googletagmanager.com'],
        ];
    }
}
