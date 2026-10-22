<?php
/**
 * The router of the built-in web server of the policy check: `/<page>` answers the page of `pages.php` with the
 * enforced policy of its classes, and `/report?page=<page>` writes the violation reports to the file of
 * `SECURITY_HEADERS_REPORTS`.
 */

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/Lib/SecurityHeaders.php';

$path = (string)parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/report') {
    $report = json_encode([
        'page' => (string)($_GET['page'] ?? ''),
        'report' => json_decode((string)file_get_contents('php://input'), true),
    ]);
    file_put_contents((string)getenv('SECURITY_HEADERS_REPORTS'), $report . "\n", FILE_APPEND | LOCK_EX);
    http_response_code(204);

    return true;
}

$name = trim($path, '/');
$pages = require __DIR__ . '/pages.php';
if (!isset($pages[$name])) {
    http_response_code(404);

    return true;
}
$page = $pages[$name];

$nonce = SecurityHeaders::nonce();
$settings = SecurityHeaders::settings([
    'contentSecurityPolicyAllow' => $page['allow'],
    'contentSecurityPolicyNonce' => true,
    'contentSecurityPolicyStrictDynamic' => $page['strictDynamic'] ?? false,
    'contentSecurityPolicyReportOnly' => false,
    'reportUri' => '/report?page=' . $name,
]);
foreach (SecurityHeaders::headers($settings, false, $nonce) as $header => $value) {
    header($header . ': ' . $value);
}

// The title becomes "ready" when every URL of `loads` is in the resource timing, which has no entry for a URL
// that the policy blocks
$check = sprintf(
    '(function(){var loads=%s;function check(){var names=performance.getEntriesByType("resource").map(function(e){return e.name;});'
    . 'var missing=loads.filter(function(l){return !names.some(function(n){return n.indexOf(l)===0;});});'
    . 'document.title=missing.length?"missing "+missing.join(" "):"ready";if(missing.length){setTimeout(check,250);}}check();})();',
    json_encode($page['loads'], JSON_UNESCAPED_SLASHES),
);
echo '<!doctype html><html><head><meta charset="utf-8"><title>loading</title></head><body>'
    . str_replace('{nonce}', $nonce, $page['html'])
    . '<script nonce="' . $nonce . '">' . $check . '</script></body></html>';
