<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

/**
 * The sources of a service whose embed code or base code has an inline script, e.g. the snippet of Google Tag
 * Manager: listing the class in `contentSecurityPolicyAllow` turns on the nonce of the policy, unless
 * `contentSecurityPolicyNonce` is `false`. The inline script gets it from `SecurityHeadersHelper::nonce()`.
 */
interface NeedsNonce extends Sources
{
}
