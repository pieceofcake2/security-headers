<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * A map of Google Maps embedded with an iframe: the Maps Embed API (https://www.google.com/maps/embed/v1/...) or the
 * HTML of "Share" > "Embed a map" (https://www.google.com/maps/embed?pb=...)
 */
class GoogleMapsEmbed implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'frame-src' => ['https://www.google.com'],
        ];
    }
}
