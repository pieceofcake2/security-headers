# CakePHP 2 Security Headers Plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/security-headers?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/security-headers?label=Packagist)](https://packagist.org/packages/pieceofcake2/security-headers)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/security-headers/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/security-headers/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/security-headers/actions/workflows/CI.yml)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/security-headers/CI.yml?label=CI)](https://github.com/pieceofcake2/security-headers/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/security-headers?label=Coverage)](https://codecov.io/gh/pieceofcake2/security-headers)

A dispatcher filter for [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) applications
that adds the security headers to every response: `Content-Security-Policy`, `X-Frame-Options`,
`X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy` and
`Cross-Origin-Resource-Policy`, and removes `X-Powered-By`.

The headers that the web server or the CDN should send, such as `Strict-Transport-Security`, are
off by default. The Content Security Policy is sent as `Content-Security-Policy-Report-Only` by
default, so that you can adapt it to your application before enforcing it.

## Documentation

- Website: https://pieceofcake2.github.io/security-headers/ (日本語: https://pieceofcake2.github.io/security-headers/ja/)
- Markdown: [docs/README.md](docs/README.md) (日本語: [docs/ja/README.md](docs/ja/README.md)) (for AI tools and offline reading)

It covers every setting and its default, the headers for the web server or the CDN, enforcing the
Content Security Policy, and changing the headers in an action.

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/SecurityHeaders/docs/README.md` (the location depends on your `installer-paths`).

## Requirements

* PHP 8.0+
* pieceofcake2/cakephp 2.12+

## Installation

```bash
composer require pieceofcake2/security-headers
```

Load the plugin and add the filter in `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`):

```php
CakePlugin::load('SecurityHeaders');

Configure::write('Dispatcher.filters', [
    'AssetDispatcher',
    'CacheDispatcher',
    'SecurityHeaders.SecurityHeadersFilter',
]);
```

See [Installation](docs/README.md#installation) for the `installer-paths` setting.

## Usage

Change the settings in `config/bootstrap.php`:

```php
Configure::write('SecurityHeaders', [
    'xFrameOptions' => 'DENY',
    'permissionsPolicy' => 'camera=(), microphone=(), geolocation=(), payment=()',
]);
```

A setting is the value of its header, or `false` not to send it. See [Settings](docs/README.md#settings).

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## License

Licensed under the MIT License. See [LICENSE](LICENSE).
