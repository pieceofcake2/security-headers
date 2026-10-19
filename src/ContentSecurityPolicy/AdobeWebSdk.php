<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The Web SDK of Adobe Experience Platform, from
 * https://experienceleague.adobe.com/en/docs/experience-platform/web-sdk/use-cases/configuring-a-csp
 *
 * The edge domain of Adobe, and demdex.net for the migration of the visitors (`idMigrationEnabled`). Add your own
 * edge domain if `edgeDomain` is a domain of yours (a CNAME)
 */
class AdobeWebSdk implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'connect-src' => ['https://*.adobedc.net', 'https://*.demdex.net'],
        ];
    }
}
