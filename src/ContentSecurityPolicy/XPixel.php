<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The X Pixel (formerly Twitter Pixel), the conversion tag of X Ads: its script of static.ads-twitter.com and its
 * events sent to analytics.twitter.com and t.co
 */
class XPixel implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://static.ads-twitter.com'],
            'img-src' => ['https://analytics.twitter.com', 'https://t.co'],
            'connect-src' => ['https://analytics.twitter.com', 'https://t.co'],
        ];
    }
}
