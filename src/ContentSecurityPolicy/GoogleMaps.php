<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The Maps JavaScript API of Google Maps, from the allowlist policy of
 * https://developers.google.com/maps/documentation/javascript/content-security-policy
 *
 * The API needs `'unsafe-eval'` in `script-src` and `'unsafe-inline'` in `style-src`, as in the guide. Its inline
 * script, e.g. the callback, needs the nonce: the `'unsafe-inline'` of `script-src` of the guide is left out. Add
 * the domains of the countries if you load the API from one, e.g. https://maps.google.fr
 */
class GoogleMaps implements NeedsNonce
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ["'unsafe-eval'", 'https://*.googleapis.com', 'https://*.gstatic.com', 'https://*.google.com', 'https://*.ggpht.com', 'https://*.googleusercontent.com', 'blob:'],
            'img-src' => ['https://*.googleapis.com', 'https://*.gstatic.com', 'https://*.google.com', 'https://*.googleusercontent.com', 'data:'],
            'frame-src' => ['https://*.google.com'],
            'connect-src' => ['https://*.googleapis.com', 'https://*.google.com', 'https://*.gstatic.com', 'data:', 'blob:'],
            'font-src' => ['https://fonts.gstatic.com'],
            'style-src' => ["'unsafe-inline'", 'https://fonts.googleapis.com'],
            'worker-src' => ['blob:'],
        ];
    }
}
