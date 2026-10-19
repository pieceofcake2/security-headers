<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The preview mode of Google Tag Manager, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 */
class GoogleTagManagerPreview implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.googletagmanager.com', 'https://tagmanager.google.com'],
            'style-src' => ['https://www.googletagmanager.com', 'https://tagmanager.google.com', 'https://fonts.googleapis.com'],
            'img-src' => ['https://www.googletagmanager.com', 'https://ssl.gstatic.com', 'https://www.gstatic.com'],
            'font-src' => ['https://fonts.gstatic.com', 'data:'],
        ];
    }
}
