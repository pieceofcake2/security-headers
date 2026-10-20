<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Fonts: the stylesheets of fonts.googleapis.com (the `<link>` of the embed code, or `@import`) and the font
 * files of fonts.gstatic.com
 */
class GoogleFonts implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'style-src' => ['https://fonts.googleapis.com'],
            'font-src' => ['https://fonts.gstatic.com'],
        ];
    }
}
