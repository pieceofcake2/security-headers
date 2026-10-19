<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The sources that a service needs in the Content Security Policy, for `contentSecurityPolicyAllow`:
 *
 * ```
 * class Stripe implements Sources
 * {
 *     public function sources(): array
 *     {
 *         return [
 *             'script-src' => ['https://js.stripe.com'],
 *             'frame-src' => ['https://js.stripe.com'],
 *         ];
 *     }
 * }
 * ```
 */
interface Sources
{
    /**
     * The sources, by directive
     *
     * @return array<string, string|array<string>>
     */
    public function sources(): array;
}
