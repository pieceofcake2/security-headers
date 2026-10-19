<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * YouTube: the embedded players, of www.youtube.com and of www.youtube-nocookie.com (the privacy-enhanced mode),
 * the IFrame Player API (https://www.youtube.com/iframe_api) and the thumbnails of i.ytimg.com
 */
class YouTube implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://www.youtube.com'],
            'img-src' => ['https://i.ytimg.com'],
            'frame-src' => ['https://www.youtube.com', 'https://www.youtube-nocookie.com'],
        ];
    }
}
