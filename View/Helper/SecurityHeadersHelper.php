<?php
declare(strict_types=1);

/**
 * The nonce of the Content Security Policy for the inline scripts, when `contentSecurityPolicyNonce` is on:
 *
 * ```
 * <script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">
 * ```
 */
class SecurityHeadersHelper extends AppHelper
{
    /**
     * The nonce of the request, or an empty string when `contentSecurityPolicyNonce` is off
     *
     * @return string
     */
    public function nonce(): string
    {
        // No request in the views of the emails sent from a shell
        /** @var \CakeRequest|null $request */
        $request = $this->request;
        $nonce = $request?->param(SecurityHeadersFilter::NONCE);

        return is_string($nonce) ? $nonce : '';
    }
}
