<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * Google Translate: the Translate Element library (te_lib), of the website widget
 * (https://translate.google.com/translate_a/element.js) and of the translation of pages in Chrome on iOS, which loads
 * it into the page. Its scripts and styles, the logos, the translations and the logging images of translate.google.com.
 *
 * Google publishes no policy for it: the sources are those that the library loads. The translated text has `style`
 * attributes, so it adds `'unsafe-inline'` to `style-src-attr` (not to `style-src`: `<style>` elements stay blocked)
 */
class GoogleTranslate implements Sources
{
    /**
     * @inheritDoc
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://translate.google.com', 'https://translate.googleapis.com', 'https://translate-pa.googleapis.com'],
            'style-src' => ['https://www.gstatic.com/_/translate_http/'],
            'style-src-attr' => ["'unsafe-inline'"],
            'img-src' => ['https://translate.google.com', 'https://translate.googleapis.com/translate_static/', 'https://www.gstatic.com', 'https://www.google.com/images/', 'https://fonts.gstatic.com/s/i/productlogos/translate/'],
            'connect-src' => ['https://translate.googleapis.com', 'https://translate-pa.googleapis.com'],
        ];
    }
}
