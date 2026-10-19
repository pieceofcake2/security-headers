<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The UET tag of Microsoft Advertising: its script https://bat.bing.com/bat.js and its events sent to bat.bing.com
 * and bat.bing.net
 */
class MicrosoftAds implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://bat.bing.com'],
            'img-src' => ['https://bat.bing.com', 'https://bat.bing.net'],
            'connect-src' => ['https://bat.bing.com', 'https://bat.bing.net'],
        ];
    }
}
