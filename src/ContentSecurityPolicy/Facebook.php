<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The JavaScript SDK of Facebook and its social plugins, e.g. the Page plugin, the embedded posts, the Like button
 * and the comments: the SDK of connect.facebook.net and the frames of www.facebook.com. The SDK adds `<style>`
 * elements, which need `'unsafe-inline'` in `style-src`
 */
class Facebook implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://connect.facebook.net'],
            'frame-src' => ['https://www.facebook.com'],
            'style-src' => ["'unsafe-inline'"],
        ];
    }
}
