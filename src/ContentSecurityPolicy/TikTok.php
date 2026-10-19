<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The embedded videos and profiles of TikTok: the script https://www.tiktok.com/embed.js, its files of
 * *.ttwstatic.com and its frames of www.tiktok.com. The embed code has a `style` attribute and the script adds
 * styles, which need `'unsafe-inline'` in `style-src`
 */
class TikTok implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.tiktok.com', 'https://*.ttwstatic.com'],
            'style-src' => ["'unsafe-inline'", 'https://*.ttwstatic.com'],
            'frame-src' => ['https://www.tiktok.com'],
            'connect-src' => ['https://www.tiktok.com'],
            'img-src' => ['data:'],
        ];
    }
}
