<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The embedded posts of Instagram: the script https://www.instagram.com/embed.js and its frames of
 * www.instagram.com. The embed code has `style` attributes, which need `'unsafe-inline'` in `style-src`
 */
class Instagram implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.instagram.com'],
            'frame-src' => ['https://www.instagram.com'],
            'style-src' => ["'unsafe-inline'"],
        ];
    }
}
