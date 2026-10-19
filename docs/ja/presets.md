# プリセット

プリセットは、サービスが Content Security Policy で必要とするソースを持つ、プラグインのクラス
(`PieceOfCake2\SecurityHeaders\ContentSecurityPolicy`) です。ページで使うものを `contentSecurityPolicyAllow`
に並べます ([ほかのサイトを許可する](README.md#allowing-other-sites) を参照してください)。

```php
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\YouTube;

Configure::write('SecurityHeaders', [
    'contentSecurityPolicyAllow' => [GoogleTagManager::class, YouTube::class],
]);
```

- ソースは、サービスがガイドを公開している場合はそのガイドから、そうでない場合は埋め込みコードや基本コードから
  作り、ポリシーを有効にした Chrome で開いて確かめています。
- **毎週確認:** この印のあるプリセットは、毎週とリリースのたびに、実際のサービスに対してポリシーを有効にした
  Chrome で開いています。ブラウザーが違反を報告すると失敗します。ほかのプリセットは、動かすのにサービスの
  アカウントが必要です。
- 同じサービスでも、埋め込みとコンバージョンタグは別のプリセットです (ページプラグインは `Facebook`、広告の
  コンバージョンは `MetaPixel` など)。ページで使うものだけを並べてください。
- スクリプトは、Google のガイドの `script-src-elem` ではなく `script-src` に追加します。Safari 15.4 より前など、
  `script-src-elem` に対応していないブラウザーでも許可されるようにするためです。
- サービスはホストを変えることがあります。プリセットにないホストがブラウザーのコンソールに報告されたら、名前を
  付けて `contentSecurityPolicyAllow` に追加し、issue で知らせてください。

- [タグマネージャーとアクセス解析](#tag-managers-and-analytics): [`GoogleTagManager`](#googletagmanager), [`GoogleTagManagerPreview`](#googletagmanagerpreview), [`GoogleAnalytics`](#googleanalytics), [`GoogleAnalyticsWithAds`](#googleanalyticswithads), [`AdobeTags`](#adobetags), [`AdobeAnalytics`](#adobeanalytics), [`AdobeWebSdk`](#adobewebsdk)
- [広告のコンバージョン (CV) タグ](#conversion-tags): [`GoogleAds`](#googleads), [`Floodlight`](#floodlight), [`MerchantCenter`](#merchantcenter), [`MetaPixel`](#metapixel), [`TikTokPixel`](#tiktokpixel), [`XPixel`](#xpixel), [`LineTag`](#linetag), [`YahooJapanAds`](#yahoojapanads), [`MicrosoftAds`](#microsoftads)
- [埋め込み](#embeds): [`GoogleMaps`](#googlemaps), [`GoogleMapsEmbed`](#googlemapsembed), [`YouTube`](#youtube), [`Facebook`](#facebook), [`Instagram`](#instagram), [`X`](#x), [`TikTok`](#tiktok), [`Line`](#line)

<a id="tag-managers-and-analytics"></a>

## タグマネージャーとアクセス解析

<a id="googletagmanager"></a>

### `GoogleTagManager`

Google タグマネージャーのコンテナ。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://www.google.com` |

Google のガイドと同じく、読み込むスクリプトにも nonce を渡すスニペットです。

```php
<!-- レイアウトの <head> の中 -->
<script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;var n=d.querySelector('[nonce]');
n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
```

カスタム JavaScript 変数には `script-src` に `'unsafe-eval'` が必要です。できればカスタムテンプレートを使ってください。
[Tag Assistant](https://tagassistant.google.com) の「ページの問題」に、ポリシーがブロックしたリソースが表示されます。

<a id="googletagmanagerpreview"></a>

### `GoogleTagManagerPreview`

Google タグマネージャーのプレビューモード。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` `https://tagmanager.google.com` |
| `style-src` | `https://www.googletagmanager.com` `https://tagmanager.google.com` `https://fonts.googleapis.com` |
| `img-src` | `https://www.googletagmanager.com` `https://ssl.gstatic.com` `https://www.gstatic.com` |
| `font-src` | `https://fonts.gstatic.com` `data:` |

<a id="googleanalytics"></a>

### `GoogleAnalytics`

Google アナリティクス (Google 広告の機能なし)。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` |

タグマネージャーを使わない場合、`gtag.js` のスニペットにインラインのスクリプトがあります: `<script nonce="<?= h($this->SecurityHeaders->nonce()) ?>">`。

<a id="googleanalyticswithads"></a>

### `GoogleAnalyticsWithAds`

Google アナリティクス (Google シグナルやリマーケティングなど、Google 広告の機能あり)。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` `https://*.g.doubleclick.net` |
| `connect-src` | `https://www.googletagmanager.com` `https://*.google-analytics.com` `https://*.google.com` `https://*.g.doubleclick.net` `https://pagead2.googlesyndication.com` |
| `frame-src` | `https://www.googletagmanager.com` |

利用者の国のドメインを追加してください (例: `img-src` と `connect-src` に `https://*.google.co.jp`)。ポリシーではホストの末尾にワイルドカードを使えません。

<a id="adobetags"></a>

### `AdobeTags`

Adobe のホストにある Adobe Experience Platform Tags (旧 Adobe Launch) のライブラリ。

根拠: [Adobe のガイド](https://experienceleague.adobe.com/ja/docs/experience-platform/tags/client-side/content-security-policy)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://assets.adobedtm.com` |

ライブラリは非同期で読み込んでください。拡張機能のクラス (`AdobeAnalytics` など) と、カスタムコードのための nonce (Core 拡張機能に nonce のデータ要素を設定) も追加してください。

<a id="adobeanalytics"></a>

### `AdobeAnalytics`

Adobe Analytics (AppMeasurement) と Experience Cloud ID サービス。

根拠: [Adobe のガイド](https://experienceleague.adobe.com/ja/docs/id-service/using/reference/csp)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `img-src` | `https://*.omtrdc.net` `https://*.2o7.net` `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `connect-src` | `https://*.omtrdc.net` `https://*.2o7.net` `https://*.demdex.net` `https://cm.everesttech.net` `https://assets.adobedtm.com` |
| `frame-src` | `https://*.demdex.net` |

自分のドメイン (CNAME) のトラッキングサーバーは追加してください。

<a id="adobewebsdk"></a>

### `AdobeWebSdk`

Adobe Experience Platform の Web SDK。`*.demdex.net` は訪問者の移行 (`idMigrationEnabled`) のためです。

根拠: [Adobe のガイド](https://experienceleague.adobe.com/ja/docs/experience-platform/web-sdk/use-cases/configuring-a-csp)。

| ディレクティブ | ソース |
| --- | --- |
| `connect-src` | `https://*.adobedc.net` `https://*.demdex.net` |

`edgeDomain` が自分のドメイン (CNAME) の場合は追加してください。

<a id="conversion-tags"></a>

## 広告のコンバージョン (CV) タグ

<a id="googleads"></a>

### `GoogleAds`

Google 広告のコンバージョン、リマーケティング、コンバージョンリンカー、ユーザー提供データ。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://www.google.com` |
| `img-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://googleads.g.doubleclick.net` `https://pagead2.googlesyndication.com` `https://www.google.com` |
| `connect-src` | `https://www.googletagmanager.com` `https://www.googleadservices.com` `https://googleads.g.doubleclick.net` `https://pagead2.googlesyndication.com` `https://www.google.com` `https://ad.doubleclick.net` |
| `frame-src` | `https://www.googletagmanager.com` |

利用者の国のドメインを追加してください (例: `img-src` と `connect-src` に `https://www.google.co.jp`)。

<a id="floodlight"></a>

### `Floodlight`

Floodlight (カスタムスクリプトを含む)。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。

| ディレクティブ | ソース |
| --- | --- |
| `img-src` | `https://www.googletagmanager.com` `https://ad.doubleclick.net` `https://ade.googlesyndication.com` `https://adservice.google.com` |
| `connect-src` | `https://pagead2.googlesyndication.com` `https://www.google.com` `https://www.googleadservices.com` `https://ad.doubleclick.net` |
| `frame-src` | `https://www.googletagmanager.com` `https://*.fls.doubleclick.net` |

<a id="merchantcenter"></a>

### `MerchantCenter`

Google Merchant Center。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Google のガイド](https://developers.google.com/tag-platform/security/guides/csp?hl=ja)。

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.googletagmanager.com` |
| `img-src` | `https://*.merchant-center-analytics.goog` |
| `connect-src` | `https://*.merchant-center-analytics.goog` |

<a id="metapixel"></a>

### `MetaPixel`

Meta ピクセル (Facebook ピクセル)。Facebook と Instagram の広告。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Meta の基本コード](https://developers.facebook.com/docs/meta-pixel/get-started)。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://connect.facebook.net` |
| `img-src` | `https://www.facebook.com` |
| `connect-src` | `https://www.facebook.com` |

<a id="tiktokpixel"></a>

### `TikTokPixel`

TikTok ピクセル。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: TikTok の基本コード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://analytics.tiktok.com` |
| `img-src` | `https://analytics.tiktok.com` |
| `connect-src` | `https://analytics.tiktok.com` `https://analytics-ipv6.tiktokw.us` |

<a id="xpixel"></a>

### `XPixel`

X 広告の X ピクセル (旧 Twitter ピクセル)。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: X の基本コード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://static.ads-twitter.com` |
| `img-src` | `https://analytics.twitter.com` `https://t.co` |
| `connect-src` | `https://analytics.twitter.com` `https://t.co` |

<a id="linetag"></a>

### `LineTag`

LINE 広告の LINE Tag。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: LINE の基本コード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://d.line-scdn.net` `https://tag.flvcdn.net` |
| `img-src` | `https://tr.line.me` |
| `connect-src` | `https://tr.line.me` |

<a id="yahoojapanads"></a>

### `YahooJapanAds`

Yahoo!広告 (検索広告とディスプレイ広告) のコンバージョン測定タグとサイトリターゲティングタグ、サイトジェネラルタグ (ytag.js)。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Yahoo!広告のお知らせ](https://www.lycbiz.com/jp/news/yahoo-ads/20230215) とタグ。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://s.yimg.jp` `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` |
| `img-src` | `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` `https://apm.yahoo.co.jp` |
| `connect-src` | `https://b91.yahoo.co.jp` `https://b97.yahoo.co.jp` `https://b98.yahoo.co.jp` `https://b99.yahoo.co.jp` `https://apm.yahoo.co.jp` `https://dm.slim02.jp` |

検索広告のタグは `b9x.yahoo.co.jp` からスクリプトを読み込み、そこに送信します。ディスプレイ広告のタグは `apm.yahoo.co.jp` に、サイトジェネラルタグは一部を `dm.slim02.jp` に送信します。

<a id="microsoftads"></a>

### `MicrosoftAds`

Microsoft 広告の UET タグ。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: Microsoft の基本コード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://bat.bing.com` |
| `img-src` | `https://bat.bing.com` `https://bat.bing.net` |
| `connect-src` | `https://bat.bing.com` `https://bat.bing.net` |

<a id="embeds"></a>

## 埋め込み

<a id="googlemaps"></a>

### `GoogleMaps`

Google マップの Maps JavaScript API。**nonce をオンにします**。コードにインラインのスクリプトがあります。[インラインのスクリプトと nonce](README.md#inline-scripts-and-the-nonce) を参照してください。

根拠: [Maps JavaScript API のガイド](https://developers.google.com/maps/documentation/javascript/content-security-policy?hl=ja) (許可リストのポリシー)。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `'unsafe-eval'` `https://*.googleapis.com` `https://*.gstatic.com` `https://*.google.com` `https://*.ggpht.com` `https://*.googleusercontent.com` `blob:` |
| `img-src` | `https://*.googleapis.com` `https://*.gstatic.com` `https://*.google.com` `https://*.googleusercontent.com` `data:` |
| `frame-src` | `https://*.google.com` |
| `connect-src` | `https://*.googleapis.com` `https://*.google.com` `https://*.gstatic.com` `data:` `blob:` |
| `font-src` | `https://fonts.gstatic.com` |
| `style-src` | `'unsafe-inline'` `https://fonts.googleapis.com` |
| `worker-src` | `blob:` |

ガイドと同じく、`script-src` に `'unsafe-eval'`、`style-src` に `'unsafe-inline'` を追加します。ガイドの `script-src` の `'unsafe-inline'` は入れていません。コールバックなどのインラインのスクリプトには nonce が必要です。国のドメイン (`maps.google.fr` など) から API を読み込む場合は追加してください。

<a id="googlemapsembed"></a>

### `GoogleMapsEmbed`

iframe で埋め込んだ Google マップ (Maps Embed API、または「共有」>「地図を埋め込む」)。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `frame-src` | `https://www.google.com` |

<a id="youtube"></a>

### `YouTube`

YouTube の埋め込みプレーヤー (`www.youtube.com` と、プライバシー強化モードの `www.youtube-nocookie.com`)、IFrame Player API、`i.ytimg.com` のサムネイル。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.youtube.com` |
| `img-src` | `https://i.ytimg.com` |
| `frame-src` | `https://www.youtube.com` `https://www.youtube-nocookie.com` |

<a id="facebook"></a>

### `Facebook`

Facebook の JavaScript SDK とソーシャルプラグイン (ページプラグイン、埋め込み投稿、いいね！ボタン、コメント)。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://connect.facebook.net` |
| `frame-src` | `https://www.facebook.com` |
| `style-src` | `'unsafe-inline'` |

SDK が `<style>` 要素を追加するため、`style-src` に `'unsafe-inline'` を追加します。プラグインはヘッドレスの Chrome では描画されないため、確認は SDK だけです。

<a id="instagram"></a>

### `Instagram`

Instagram の埋め込み投稿。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.instagram.com` |
| `frame-src` | `https://www.instagram.com` |
| `style-src` | `'unsafe-inline'` |

埋め込みコードに `style` 属性があるため、`style-src` に `'unsafe-inline'` を追加します。投稿はヘッドレスの Chrome では描画されないため、確認はスクリプトだけです。

<a id="x"></a>

### `X`

X (旧 Twitter) の埋め込みポスト、タイムライン、ボタン。`syndication.twitter.com` はタイムラインのためです。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://platform.twitter.com` |
| `frame-src` | `https://platform.twitter.com` `https://syndication.twitter.com` |
| `style-src` | `'unsafe-inline'` |

スクリプトが `<style>` 要素を追加するため、`style-src` に `'unsafe-inline'` を追加します。ポストはヘッドレスの Chrome では描画されないため、確認はスクリプトだけです。

<a id="tiktok"></a>

### `TikTok`

TikTok の埋め込み動画とプロフィール。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.tiktok.com` `https://*.ttwstatic.com` `https://*.tiktokcdn-us.com` |
| `style-src` | `'unsafe-inline'` `https://*.ttwstatic.com` `https://*.tiktokcdn-us.com` |
| `frame-src` | `https://www.tiktok.com` |
| `connect-src` | `https://www.tiktok.com` |
| `img-src` | `data:` |

ファイルは日本では `*.ttwstatic.com`、米国では `*.tiktokcdn-us.com` から配信されます。TikTok は利用者の国で CDN を選びます。埋め込みコードに `style` 属性があるため、`style-src` に `'unsafe-inline'` を追加します。

<a id="line"></a>

### `Line`

LINE のソーシャルプラグイン (「LINEで送る」ボタン、「友だち追加」ボタン)。

根拠: 埋め込みコード。**毎週確認。**

| ディレクティブ | ソース |
| --- | --- |
| `script-src` | `https://www.line-website.com` |
| `frame-src` | `https://social-plugins.line.me` |
| `style-src` | `'unsafe-inline'` |

埋め込みコードに `style` 属性があり、スクリプトがスタイルを設定するため、`style-src` に `'unsafe-inline'` を追加します。
