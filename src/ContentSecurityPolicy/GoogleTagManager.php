<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The container of Google Tag Manager, from https://developers.google.com/tag-platform/security/guides/csp
 *
 * The scripts are in `script-src` instead of `script-src-elem` of the guide, for the browsers without
 * `script-src-elem`, such as Safari before 15.4.
 */
class GoogleTagManager implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.googletagmanager.com'],
            'img-src' => ['https://www.googletagmanager.com'],
            'connect-src' => ['https://www.googletagmanager.com', 'https://www.google.com'],
        ];
    }
}
