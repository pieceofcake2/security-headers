# Security Headers プラグインドキュメント

Security Headers プラグインは、ディスパッチャーフィルターで、アプリケーションのすべてのレスポンスに
セキュリティヘッダーを付けます。対象は、ページ、リダイレクト、エラーページ、ダウンロード、プラグインと
テーマのファイルです。ヘッダーは、ページが実行するスクリプト、ほかのサイトがページをフレームで表示
できるか、ほかのサイトに送る情報などを制限するよう、ブラウザーに伝えます。

ウェブサーバーや CDN が送るべきヘッダーと、ページを壊しやすいヘッダーは、デフォルトでオフです。
Content Security Policy は、デフォルトでは報告のみのモードで送ります。

- [インストール](#installation)
- [設定](#settings)
- [Content Security Policy にサイトを追加する](#adding-sites-to-the-content-security-policy)
- [ウェブサーバーや CDN で送るヘッダー](#headers-for-the-web-server-or-the-cdn)
- [Content Security Policy を有効にする](#enforcing-the-content-security-policy)
- [アクションでヘッダーを変える](#changing-the-headers-in-an-action)
- [注意点](#notes)
- [プラグインを使わない場合](#without-the-plugin)

## インストール

必要なもの: PHP 8.0+、[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) 2.12+。

```bash
composer require pieceofcake2/security-headers
```

プラグインは `cakephp-plugin` タイプなので、`composer/installers` でプラグインのディレクトリに
インストールされます。アプリケーションの `composer.json` に `installer-paths` を設定してください。

| レイアウト | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

`config/bootstrap.php` (レガシーのレイアウトでは `app/Config/bootstrap.php`) でプラグインを読み込み、
ディスパッチャーフィルターに `SecurityHeaders.SecurityHeadersFilter` を追加します。
`CakePlugin::loadAll()` を使っている場合、プラグインの読み込みは不要です。

```php
CakePlugin::load('SecurityHeaders');

Configure::write('Dispatcher.filters', [
    'AssetDispatcher',
    'CacheDispatcher',
    'SecurityHeaders.SecurityHeadersFilter',
]);
```

フィルターは、リストのどこに書いても、ほかのフィルターより先に動きます (優先度は 1。`AssetDispatcher`
と `CacheDispatcher` は 9)。そのため、プラグインのファイルやキャッシュしたビューにもヘッダーが付きます。

`webroot/` のファイルは CakePHP を通りません。ウェブサーバーが、ウェブサーバーのヘッダーを付けて送ります。

## 設定

`config/bootstrap.php` で `Configure::write('SecurityHeaders', [...])` を書いて設定を変えます。設定の値は
そのヘッダーの値で、`false` にすると送りません。書かなかった設定はデフォルトのままです。未知の設定は
`InvalidArgumentException` を投げます。設定名の誤字で、ヘッダーが気づかないままデフォルトになることを
防ぐためです。

```php
Configure::write('SecurityHeaders', [
    'xFrameOptions' => 'DENY',
    'contentSecurityPolicySources' => [
        'script-src' => ['https://cdn.example.com'],
    ],
]);
```

デフォルトでオン:

| 設定 | ヘッダー | デフォルト | 役割 |
| --- | --- | --- | --- |
| `contentSecurityPolicy` | `Content-Security-Policy` | `default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'` | ページのスクリプト、スタイル、画像、フレーム、フォームの送信先を制限します。XSS に対する主な防御です。ディレクティブとソースの配列か、ポリシーの文字列で書きます。 |
| `contentSecurityPolicySources` | | `[]` | ポリシーのディレクティブに追加するソース (ほかのサイトなど)。[Content Security Policy にサイトを追加する](#adding-sites-to-the-content-security-policy) を参照してください。 |
| `contentSecurityPolicyReportOnly` | | `true` | ポリシーを `Content-Security-Policy-Report-Only` で送ります。ブラウザーは違反をブロックせず、コンソール (と `reportUri`) に報告します。[Content Security Policy を有効にする](#enforcing-the-content-security-policy) を参照してください。 |
| `reportUri` | | `false` | 違反の報告を受け取る URL。ポリシーに `report-uri` として追加します。 |
| `xFrameOptions` | `X-Frame-Options` | `SAMEORIGIN` | ほかのサイトがページをフレームで表示できるか (クリックジャッキング)。モダンなブラウザーではポリシーの `frame-ancestors` が優先されます。古いブラウザー向けです。 |
| `xContentTypeOptions` | `X-Content-Type-Options` | `nosniff` | ブラウザーがレスポンスの種類を推測しないようにします。たとえば、アップロードされたテキストファイルをスクリプトとして実行しません。 |
| `referrerPolicy` | `Referrer-Policy` | `strict-origin-when-cross-origin` | ほかのサイトの `Referer` には、ページのオリジンだけを送ります。ID やトークンを含みうるパスとクエリーは送りません。 |
| `permissionsPolicy` | `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | ページとそのフレームが使えないブラウザーの機能。 |
| `crossOriginOpenerPolicy` | `Cross-Origin-Opener-Policy` | `same-origin` | ページが開いた、またはページを開いたほかのサイトのウィンドウは、`window.opener` でページにアクセスできません。 |
| `crossOriginResourcePolicy` | `Cross-Origin-Resource-Policy` | `same-origin` | ほかのサイトは、`<img>` や `<script>` でレスポンス (画像や JSON など) を読み込めません。 |
| `removePoweredBy` | `X-Powered-By` | `true` | `expose_php` がオンのとき PHP が付ける PHP のバージョンを取り除きます。 |

デフォルトでオフ:

| 設定 | ヘッダー | 例 | オフの理由 |
| --- | --- | --- | --- |
| `strictTransportSecurity` | `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | ウェブサーバーや CDN で送るものです。[下記](#headers-for-the-web-server-or-the-cdn)を参照してください。 |
| `cacheControl` | `Cache-Control` | `no-store` | ウェブサーバーや CDN、各アクションで決めるものです。セッションのあるページには PHP が `no-store, no-cache, must-revalidate` を送り (デフォルトの `session.cache_limiter`)、アクションは `$this->response->cache()` や `disableCache()` で設定します。 |
| `crossOriginEmbedderPolicy` | `Cross-Origin-Embedder-Policy` | `require-corp` | `Cross-Origin-Resource-Policy` や CORS のヘッダーを送らないほかのサイトの画像、スクリプト、フレームが表示されなくなります。必要なのは `SharedArrayBuffer` や精度の高いタイマーを使う場合だけです。 |
| `xXssProtection` | `X-XSS-Protection` | `0` | 廃止されたヘッダーです。モダンなブラウザーは無視し、古いブラウザーのフィルターは悪用されることがありました。スキャナーに求められた場合は `0` を送ってください。 |

ウェブサーバーが送る `Server` はプラグインでは取り除きません。ウェブサーバーでオフにしてください
(nginx では `server_tokens off;`、Apache では `ServerTokens Prod` と `ServerSignature Off`)。

## Content Security Policy にサイトを追加する

ほかのサイトのスクリプト、画像、フレームを許可するには、`contentSecurityPolicySources` でディレクティブ
ごとに追加します。ポリシーのほかのディレクティブとソースはそのまま残ります。

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicySources' => [
        'script-src' => ['https://cdn.example.com'],
        'img-src' => ['https://images.example.com', 'data:'],
        'frame-src' => ['https://www.youtube-nocookie.com'],
    ],
]);
// default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none';
// script-src 'self' https://cdn.example.com; img-src 'self' https://images.example.com data:;
// frame-src 'self' https://www.youtube-nocookie.com
```

ポリシーにないディレクティブは、ブラウザーが代わりに使うディレクティブ (`script-src` なら `default-src`)
のソースから始めるので、`'self'` は許可されたままです。ソースを追加したディレクティブからは `'none'`
を取り除きます。

ポリシー全体を書くには、`contentSecurityPolicy` を配列か文字列で設定します。

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicy' => [
        'default-src' => ["'self'"],
        'img-src' => ["'self'", 'data:'],
        'frame-ancestors' => ["'none'"],
        'form-action' => ["'self'"],
        'base-uri' => ["'self'"],
        'object-src' => ["'none'"],
    ],
]);
```

一部のアクションだけで追加するには、[コンポーネントの `addSources()`](#changing-the-headers-in-an-action) を使います。

## ウェブサーバーや CDN で送るヘッダー

次のヘッダーはウェブサーバー、ロードバランサー、CDN で送るもので、デフォルトでオフです。

- `Strict-Transport-Security` (HSTS) は、そのサイトに HTTPS だけで接続するようブラウザーに伝えます。
  `webroot/` のファイルを含むすべてのレスポンスで、HTTPS のときだけ送る必要があります。TLS を終端する
  ロードバランサーや CDN の後ろでは、アプリケーションからは HTTP に見えるため判断できません。
  プラグインでオンにした場合は、`$request->is('ssl')` が true のときだけ送ります。
- 静的ファイルやキャッシュしたページの `Cache-Control` は、CDN とウェブサーバーで決めます。

これらを送れるウェブサーバーや CDN を使わずにアプリケーションを公開する場合だけ、プラグインでオンに
してください。

## Content Security Policy を有効にする

デフォルトのポリシーは、サイト自身のスクリプト、スタイル、画像だけを許可します。有効にすると、
インラインの `<script>` ブロック、`style` 属性、`onclick` などのハンドラー、CDN のファイルは
ブロックされます。多くのアプリケーションでは先に修正が必要なため、デフォルトでは
`Content-Security-Policy-Report-Only` で送ります。

1. ブラウザーのコンソールを開いてアプリケーションのページを開き、報告されたものを修正するか許可します。
   インラインのスクリプトはファイルに移すか、必要な[サイトを追加](#adding-sites-to-the-content-security-policy)します。
2. 必要なら、`reportUri` で利用者のブラウザーからの報告を集めます。
3. 報告が出なくなったら、ポリシーを有効にします。

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicyReportOnly' => false,
]);
```

## アクションでヘッダーを変える

一部のアクションでヘッダーを変えるには `SecurityHeadersComponent` を使います。たとえば、パートナーの
サイトがフレームで表示するページでは次のようにします。

```php
class WidgetsController extends AppController
{
    public $components = ['SecurityHeaders.SecurityHeaders'];

    /**
     * パートナーのサイトのフレームで表示するページ
     *
     * @return void
     */
    public function embed(): void
    {
        $this->SecurityHeaders->set('Content-Security-Policy', "frame-ancestors 'self' https://partner.example.com");
        $this->SecurityHeaders->disable('X-Frame-Options');
    }

    /**
     * YouTube の動画を表示する
     *
     * @return void
     */
    public function video(): void
    {
        $this->SecurityHeaders->addSources('frame-src', 'https://www.youtube-nocookie.com');
    }
}
```

- `addSources($directive, $sources)` は、`contentSecurityPolicySources` と同じように、設定の Content
  Security Policy のディレクティブにソースを追加します。
- `set($name, $value)` は、設定のヘッダーを置き換えます。
- `disable($name)` は、このレスポンスではそのヘッダーを送りません。

ヘッダー名は、フィルターが送る名前で書いてください。報告のみのモードでは
`Content-Security-Policy-Report-Only` です。

## 注意点

- **ほかのサイトのポップアップ。** `Cross-Origin-Opener-Policy: same-origin` では、ほかのサイトの
  ログインや決済のために開いたポップアップから、`window.opener` でページにアクセスできません。
  そうした流れが動かなくなった場合は `same-origin-allow-popups` を使ってください。
- **ほかのサイト向けのリソース。** `Cross-Origin-Resource-Policy: same-origin` では、ほかのサイトが
  `<img>` や `<script>` でアプリケーションの画像やファイルを読み込めません。たとえば、メールや自分の
  別ドメインに埋め込む画像です。アプリケーション全体で、またはコンポーネントでそのアクションだけ、
  `same-site` か `cross-origin` にしてください。API の `fetch()` など、CORS のリクエストには影響しません。
- **エラーページ。** エラーページは別のレスポンスを使うため、フィルターは `header()` でもヘッダーを
  送ります。あとからアプリケーションや CakePHP が同じ名前のヘッダーを送ると、そちらに置き換わります。

## プラグインを使わない場合

自分でヘッダーを送るには、`AppController::beforeFilter()` でレスポンスに追加します。この方法では、
プラグインとテーマのファイル、`CacheDispatcher` がキャッシュしたビューにはヘッダーが付きません。

```php
/**
 * レスポンスにセキュリティヘッダーを追加する
 *
 * @return void
 */
public function beforeFilter(): void
{
    parent::beforeFilter();
    $this->response->header([
        'Content-Security-Policy' => "default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'",
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ]);
}
```

または、ウェブサーバーからすべてのレスポンスに付けます。nginx の例:

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```
