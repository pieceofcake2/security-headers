<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The library of Adobe Experience Platform Tags (formerly Adobe Launch) on the host of Adobe, from
 * https://experienceleague.adobe.com/en/docs/experience-platform/tags/client-side/content-security-policy
 *
 * The library must be loaded asynchronously. The inline scripts and the custom code need the nonce: set the data
 * element of the nonce in the Core extension. Add the classes of the extensions too, e.g. AdobeAnalytics
 */
class AdobeTags implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://assets.adobedtm.com'],
        ];
    }
}
