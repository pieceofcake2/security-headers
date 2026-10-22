<?php
declare(strict_types=1);

/**
 * `HtmlHelper` that gives the nonce of the Content Security Policy to the scripts of `script()`, `scriptBlock()` and
 * `scriptStart()`, when `contentSecurityPolicyNonce` is on. Use it as `Html`:
 *
 * ```
 * public $helpers = ['Html' => ['className' => 'SecurityHeaders.SecurityHeadersHtml']];
 * ```
 *
 * A `nonce` option replaces it, and `'nonce' => false` leaves it out.
 *
 * @property \SecurityHeadersHelper $SecurityHeaders
 */
class SecurityHeadersHtmlHelper extends HtmlHelper
{
    /**
     * @var array<int|string, mixed>
     */
    public $helpers = ['SecurityHeaders.SecurityHeaders'];

    /**
     * @inheritDoc
     */
    public function script($url, $options = [])
    {
        if (is_bool($options)) {
            $options = ['inline' => $options];
        }

        return parent::script($url, $this->withNonce($options));
    }

    /**
     * @inheritDoc
     */
    public function scriptBlock($script, $options = [])
    {
        return parent::scriptBlock($script, $this->withNonce($options));
    }

    /**
     * The options with the nonce, unless they have one
     *
     * @param array<string, mixed> $options Options of a script
     * @return array<string, mixed>
     */
    private function withNonce(array $options): array
    {
        $nonce = $this->SecurityHeaders->nonce();
        if ($nonce !== '' && !array_key_exists('nonce', $options)) {
            $options['nonce'] = $nonce;
        }

        return $options;
    }
}
