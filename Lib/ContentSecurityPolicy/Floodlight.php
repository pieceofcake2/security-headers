<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Floodlight, from https://developers.google.com/tag-platform/security/guides/csp
 */
class Floodlight implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'img-src' => ['https://www.googletagmanager.com', 'https://ad.doubleclick.net', 'https://ade.googlesyndication.com', 'https://adservice.google.com'],
            'connect-src' => ['https://pagead2.googlesyndication.com', 'https://www.google.com', 'https://www.googleadservices.com', 'https://ad.doubleclick.net'],
            'frame-src' => ['https://www.googletagmanager.com', 'https://*.fls.doubleclick.net'],
        ];
    }
}
