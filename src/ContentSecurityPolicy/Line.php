<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The social plugins of LINE, e.g. the "Share" and "Add friend" buttons: the script of www.line-website.com and
 * the frames of social-plugins.line.me. The embed code has a `style` attribute and the script sets styles, which
 * need `'unsafe-inline'` in `style-src`
 */
class Line implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.line-website.com'],
            'frame-src' => ['https://social-plugins.line.me'],
            'style-src' => ["'unsafe-inline'"],
        ];
    }
}
