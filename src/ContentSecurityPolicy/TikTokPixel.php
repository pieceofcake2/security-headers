<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The TikTok Pixel: its script of analytics.tiktok.com and its events, sent to analytics.tiktok.com and
 * analytics-ipv6.tiktokw.us
 */
class TikTokPixel implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://analytics.tiktok.com'],
            'img-src' => ['https://analytics.tiktok.com'],
            'connect-src' => ['https://analytics.tiktok.com', 'https://analytics-ipv6.tiktokw.us'],
        ];
    }
}
