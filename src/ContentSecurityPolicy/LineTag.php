<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The LINE Tag of LINE Ads: its scripts of d.line-scdn.net and tag.flvcdn.net, and its events sent to tr.line.me
 */
class LineTag implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://d.line-scdn.net', 'https://tag.flvcdn.net'],
            'img-src' => ['https://tr.line.me'],
            'connect-src' => ['https://tr.line.me'],
        ];
    }
}
