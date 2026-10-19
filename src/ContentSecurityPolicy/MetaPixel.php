<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The Meta Pixel (Facebook Pixel): its script of connect.facebook.net and its events sent to www.facebook.com, from
 * the base code of https://developers.facebook.com/docs/meta-pixel/get-started
 */
class MetaPixel implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://connect.facebook.net'],
            'img-src' => ['https://www.facebook.com'],
            'connect-src' => ['https://www.facebook.com'],
        ];
    }
}
