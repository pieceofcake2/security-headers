<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The embedded posts, timelines and buttons of X (formerly Twitter): the script
 * https://platform.twitter.com/widgets.js and its frames of platform.twitter.com and, for the timelines,
 * syndication.twitter.com. The script adds `<style>` elements, which need `'unsafe-inline'` in `style-src`
 */
class X implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://platform.twitter.com'],
            'frame-src' => ['https://platform.twitter.com', 'https://syndication.twitter.com'],
            'style-src' => ["'unsafe-inline'"],
        ];
    }
}
