# Security Headers プラグインドキュメント

Security Headers プラグインは、ディスパッチャーフィルターで、アプリケーションのすべてのレスポンスに
セキュリティヘッダーを付けます。対象は、ページ、リダイレクト、エラーページ、ダウンロード、プラグインと
テーマのファイルです。ヘッダーは、ページが実行するスクリプト、ほかのサイトがページをフレームで表示
できるか、ほかのサイトに送る情報などを制限するよう、ブラウザーに伝えます。

ウェブサーバーや CDN が送るべきヘッダーと、ページを壊しやすいヘッダーは、デフォルトでオフです。
Content Security Policy は、デフォルトでは報告のみのモードで送ります。

- [インストール](#installation)
- [設定](#settings)
- [ほかのサイトを許可する](#allowing-other-sites)
- [インラインのスクリプトと nonce](#inline-scripts-and-the-nonce)
- [ウェブサーバーや CDN で送るヘッダー](#headers-for-the-web-server-or-the-cdn)
- [Content Security Policy を有効にする](#enforcing-the-content-security-policy)
- [アクションでヘッダーを変える](#changing-the-headers-in-an-action)
- [注意点](#notes)
- [プラグインを使わない場合](#without-the-plugin)
- [プリセット](presets.md): サービスのクラスとそのソース

<a id="installation"></a>

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

<a id="settings"></a>

## 設定

`config/bootstrap.php` で `Configure::write('SecurityHeaders', [...])` を書いて設定を変えます。設定の値は
そのヘッダーの値で、`false` にすると送りません。書かなかった設定はデフォルトのままです。未知の設定、型の違う値
(ヘッダーに配列、`true` の代わりに `1` など)、ポリシーの未知のディレクティブは `InvalidArgumentException` を
投げます。誤字で、ヘッダーが気づかないままデフォルトになったり、ブラウザーが無視するディレクティブが残ったり
することを防ぐためです。

```php
Configure::write('SecurityHeaders', [
    'xFrameOptions' => 'DENY',
    'contentSecurityPolicyAllow' => [
        'cdn' => ['script-src' => ['https://cdn.example.com']],
    ],
]);
```

デフォルトでオン:

| 設定 | ヘッダー | デフォルト | 役割 |
| --- | --- | --- | --- |
| `contentSecurityPolicy` | `Content-Security-Policy` | `default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'` | ページのスクリプト、スタイル、画像、フレーム、フォームの送信先を制限します。XSS に対する主な防御です。ディレクティブとソースの配列か、ポリシーの文字列で書きます。 |
| `contentSecurityPolicyAllow` | | `[]` | ポリシーで許可するほかのサイト。サービスのクラスのインスタンス (例: `new GoogleTagManager()`) か、名前を付けたディレクティブごとのソースを書きます。[ほかのサイトを許可する](#allowing-other-sites) を参照してください。 |
| `contentSecurityPolicyNonce` | | `null` | リクエストごとに新しい nonce を `script-src` に追加します。`nonce` 属性にその値を持つインラインのスクリプトが許可されます。デフォルト (`null`) では、`contentSecurityPolicyAllow` のプリセットが必要とするときにオンです。`true` で常にオン、`false` でオフです。[インラインのスクリプトと nonce](#inline-scripts-and-the-nonce) を参照してください。 |
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
| `cacheControl` | `Cache-Control` | `no-store` | ウェブサーバーや CDN、各アクションで決めるものです。キャッシュすべきファイルも含め、すべてのレスポンスの `Cache-Control` を置き換えてしまいます。アクションは `$this->response->cache()` や `disableCache()` で設定します。セッションのあるページは [セッションのあるページ](#notes) を参照してください。 |
| `crossOriginEmbedderPolicy` | `Cross-Origin-Embedder-Policy` | `require-corp` | `Cross-Origin-Resource-Policy` や CORS のヘッダーを送らないほかのサイトの画像、スクリプト、フレームが表示されなくなります。必要なのは `SharedArrayBuffer` や精度の高いタイマーを使う場合だけです。 |
| `xXssProtection` | `X-XSS-Protection` | `0` | 廃止されたヘッダーです。モダンなブラウザーは無視し、古いブラウザーのフィルターは悪用されることがありました。スキャナーに求められた場合は `0` を送ってください。 |

ウェブサーバーが送る `Server` はプラグインでは取り除きません。ウェブサーバーでオフにしてください
(nginx では `server_tokens off;`、Apache では `ServerTokens Prod` と `ServerSignature Off`)。

<a id="allowing-other-sites"></a>

## ほかのサイトを許可する

ほかのサイトのスクリプト、画像、フレームを許可するには、`contentSecurityPolicyAllow` に並べます。
サービスのクラスのインスタンスか、自分で付けた名前とディレクティブごとのソースを書きます。ポリシーのほかの
ディレクティブとソースはそのまま残ります。

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleAnalytics;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\YouTube;

Configure::write('SecurityHeaders', [
    'contentSecurityPolicyAllow' => [
        new GoogleTagManager(),
        new GoogleAnalytics(),
        'cdn' => [
            'script-src' => ['https://cdn.example.com'],
        ],
        new YouTube(),
    ],
]);
```

引数の要らないクラスは、クラス名 (例: `GoogleTagManager::class`) でも書けます。インスタンスには、クラスの
オプションを渡せます。たとえば `GoogleAnalyticsWithAds` と `GoogleAds` には、ユーザーの国の Google のドメインの
ために、ユーザーの国を渡します: `new GoogleAnalyticsWithAds(countries: ['jp'])` ([プリセット](presets.md#googleanalyticswithads) を参照)。

ポリシーにないディレクティブは、ブラウザーが代わりに使うディレクティブ (`script-src` なら `default-src`)
のソースから始めるので、`'self'` は許可されたままです。ソースを追加したディレクティブからは `'none'`
を取り除きます。デフォルトのポリシーに上の `cdn` と `new YouTube()` を追加すると、次のようになります。

```text
default-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none';
script-src 'self' https://cdn.example.com https://www.youtube.com; img-src 'self' https://i.ytimg.com;
frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com
```

プラグインには、Google、Adobe、Meta (Facebook、Instagram)、X、TikTok、LINE、YouTube、Yahoo! JAPAN、Microsoft の
タグ、コンバージョンタグ、埋め込みのためのクラス (プリセット) があります。それぞれのソースと注意点は
[プリセット](presets.md) を参照してください。スクリプトの必要に応じて、`style-src` に `'unsafe-inline'` や
`script-src` に `'unsafe-eval'` を追加するものがあります。

複数のアプリケーションで同じサービスのソースを使うには、`Sources` を実装したクラスを `src/Lib/` などに
書き、同じように並べます。

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Sources;

class StripeSources implements Sources
{
    /**
     * Stripe.js のソース
     *
     * @return array<string, array<string>>
     */
    public function sources(): array
    {
        return [
            'script-src' => ['https://js.stripe.com'],
            'frame-src' => ['https://js.stripe.com'],
        ];
    }
}
```

プラグインはクラスを引数なしで作ります。サービスのコードにインラインのスクリプトがある場合は、`Sources` の
代わりに `NeedsNonce` を実装してください。そのクラスは nonce をオンにします
([インラインのスクリプトと nonce](#inline-scripts-and-the-nonce))。`Sources` を実装していないクラス、引数が必要な
クラス、名前のない値は `InvalidArgumentException` を投げます。

代わりにポリシー全体を書くには、`contentSecurityPolicy` を配列か文字列で設定します。

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

<a id="inline-scripts-and-the-nonce"></a>

## インラインのスクリプトと nonce

ポリシーは、インラインのスクリプト (`<script>` ブロック、`onclick` などの属性、`javascript:` のリンク) を
ブロックします。`nonce` 属性にレスポンスの nonce (レスポンスごとに変わるランダムな値) を持つ `<script>` ブロック
は許可します。

インラインのスクリプトを使うプリセット (`GoogleTagManager` など。[プリセット](presets.md) のページの
「nonce をオンにします」) を `contentSecurityPolicyAllow` に入れると、nonce はオンになります。
`'contentSecurityPolicyNonce' => true` でも常にオンになり、`false` でオフになります。

`AppController` でヘルパーを読み込みます。

```php
public $helpers = ['SecurityHeaders.SecurityHeaders'];
```

ビューやレイアウトのインラインのスクリプトに nonce を付けます。

```php
<!-- ビューやレイアウトの中 -->
<script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">
    window.dataLayer = window.dataLayer || [];
</script>
<?= $this->Html->scriptBlock('console.log(1);', ['nonce' => $this->SecurityHeaders->nonce()]) ?>
```

`SecurityHeadersHtmlHelper` は、`HtmlHelper` の `script()`、`scriptBlock()`、`scriptStart()` のスクリプトに nonce
を付けます。`Html` として読み込みます。

```php
public $helpers = [
    'Html' => ['className' => 'SecurityHeaders.SecurityHeadersHtml'],
    'SecurityHeaders.SecurityHeaders',
];
```

```php
<?= $this->Html->script('https://cdn.example.com/widget.js') ?>
<!-- <script src="https://cdn.example.com/widget.js" nonce="..."></script> -->
<?php $this->Html->scriptStart(['inline' => false]); ?>
    window.dataLayer = window.dataLayer || [];
<?php $this->Html->scriptEnd(); ?>
```

`nonce` オプションを渡すとその値になり、`'nonce' => false` で付けません。テンプレートに書いた `<script>` 要素には、
`nonce` 属性を書く必要があります。レスポンスのすべての `<script>` に nonce を付けるフィルターは、XSS の
スクリプトにも付けてしまうためです。

- **属性と `javascript:` のリンク**には nonce を付けられません。コードをスクリプトのファイルに移してください
  (`addEventListener()` など)。アプリケーションのスケルトン (pieceofcake2/app。CakePHP 5 のものも同じ) には
  2 つあります。フラッシュメッセージは `onclick` 属性でクリックすると消え、エラーのレイアウトには
  `javascript:history.back()` のリンクがあります。ポリシーを有効にすると、これらは動かなくなります。
- **nonce を使わない場合**、サービスのガイドは `'unsafe-inline'` でインラインのスクリプトを許可します。
  これは XSS のインラインのスクリプトも許可します。nonce を付けられない場合だけ、
  `contentSecurityPolicyAllow` に追加してください。
- **キャッシュ。** nonce はレスポンスごとに変わります。`CacheHelper` や CDN が HTML ごとキャッシュした
  ページには別のレスポンスの nonce が入っているため、インラインのスクリプトがブロックされます。

<a id="headers-for-the-web-server-or-the-cdn"></a>

## ウェブサーバーや CDN で送るヘッダー

次のヘッダーはウェブサーバー、ロードバランサー、CDN で送るもので、デフォルトでオフです。

- `Strict-Transport-Security` (HSTS) は、そのサイトに HTTPS だけで接続するようブラウザーに伝えます。
  `webroot/` のファイルを含むすべてのレスポンスで、HTTPS のときだけ送る必要があります。TLS を終端する
  ロードバランサーや CDN の後ろでは、アプリケーションからは HTTP に見えるため判断できません。
  プラグインでオンにした場合は、`$request->is('ssl')` が true のときだけ送ります。
- 静的ファイルやキャッシュしたページの `Cache-Control` は、CDN とウェブサーバーで決めます。

これらを送れるウェブサーバーや CDN を使わずにアプリケーションを公開する場合だけ、プラグインでオンに
してください。

<a id="enforcing-the-content-security-policy"></a>

## Content Security Policy を有効にする

デフォルトのポリシーは、サイト自身のスクリプト、スタイル、画像だけを許可します。有効にすると、
インラインの `<script>` ブロック、`style` 属性、`onclick` などのハンドラー、CDN のファイルは
ブロックされます。多くのアプリケーションでは先に修正が必要なため、デフォルトでは
`Content-Security-Policy-Report-Only` で送ります。

1. ブラウザーのコンソールを開いてアプリケーションのページを開き、報告されたものを修正するか許可します。
   インラインのスクリプトはファイルに移すか、必要な[サイトを許可](#allowing-other-sites)します。
2. 必要なら、`reportUri` で利用者のブラウザーからの報告を集めます。
3. 報告が出なくなったら、ポリシーを有効にします。

```php
Configure::write('SecurityHeaders', [
    'contentSecurityPolicyReportOnly' => false,
]);
```

<a id="changing-the-headers-in-an-action"></a>

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

- `addSources($directive, $sources)` は、`contentSecurityPolicyAllow` と同じように、設定の Content
  Security Policy のディレクティブにソースを追加します。
- `set($name, $value)` は、設定のヘッダーを置き換えます。
- `disable($name)` は、このレスポンスではそのヘッダーを送りません。

ヘッダー名は、フィルターが送る名前で書いてください。報告のみのモードでは
`Content-Security-Policy-Report-Only` です。

<a id="notes"></a>

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
- **セッションのあるページ。** CakePHP 2 は、Internet Explorer 8 以前のために、PHP のキャッシュリミッターを
  デフォルトで `must-revalidate` にします。PHP はこの値を知らないのでキャッシュのヘッダーを送らず、ログイン中の
  ページに `Cache-Control` が付きません。ログアウトしたあとにブラウザーがページを再び表示したり、共有のキャッシュが
  ページを保存したりできます。CakePHP 5 と同じく PHP のキャッシュリミッターを使うよう、`config/core.php` で
  設定してください。

  ```php
  Configure::write('Session.cacheLimiter', 'nocache');
  ```

  これで PHP は、セッションを開始するページに `Cache-Control: no-store, no-cache, must-revalidate` を送ります。

<a id="without-the-plugin"></a>

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
