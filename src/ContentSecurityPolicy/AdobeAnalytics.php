<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Adobe Analytics (AppMeasurement) with the Experience Cloud Identity Service, from
 * https://experienceleague.adobe.com/en/docs/id-service/using/reference/csp
 *
 * Add your own tracking server if it is on a domain of yours (a CNAME)
 */
class AdobeAnalytics implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://*.demdex.net', 'https://cm.everesttech.net', 'https://assets.adobedtm.com'],
            'img-src' => ['https://*.omtrdc.net', 'https://*.2o7.net', 'https://*.demdex.net', 'https://cm.everesttech.net', 'https://assets.adobedtm.com'],
            'connect-src' => ['https://*.omtrdc.net', 'https://*.2o7.net', 'https://*.demdex.net', 'https://cm.everesttech.net', 'https://assets.adobedtm.com'],
            'frame-src' => ['https://*.demdex.net'],
        ];
    }
}
