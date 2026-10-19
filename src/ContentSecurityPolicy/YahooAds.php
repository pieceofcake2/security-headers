<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The conversion tags and the site retargeting tags of Yahoo! JAPAN Ads (Yahoo!広告), of the search ads and the
 * display ads, with the site general tag (ytag.js) of s.yimg.jp. The tags of the search ads load their scripts from
 * and send their events to the domains of https://www.lycbiz.com/jp/news/yahoo-ads/20230215; those of the display
 * ads send theirs to apm.yahoo.co.jp, and the site general tag sends some to dm.slim02.jp
 */
class YahooAds implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://s.yimg.jp', 'https://b91.yahoo.co.jp', 'https://b97.yahoo.co.jp', 'https://b98.yahoo.co.jp', 'https://b99.yahoo.co.jp'],
            'img-src' => ['https://b91.yahoo.co.jp', 'https://b97.yahoo.co.jp', 'https://b98.yahoo.co.jp', 'https://b99.yahoo.co.jp', 'https://apm.yahoo.co.jp'],
            'connect-src' => ['https://b91.yahoo.co.jp', 'https://b97.yahoo.co.jp', 'https://b98.yahoo.co.jp', 'https://b99.yahoo.co.jp', 'https://apm.yahoo.co.jp', 'https://dm.slim02.jp'],
        ];
    }
}
