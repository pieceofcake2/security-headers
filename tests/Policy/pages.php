<?php
/**
 * The pages of the policy check: for each service, the classes of the policy, the embed code or the base code of
 * the service (`{nonce}` is the nonce of the response), and the URLs that the page must load.
 */

use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Facebook;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleAnalytics;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleFonts;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleMaps;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleMapsEmbed;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\GoogleTagManager;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Instagram;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\Line;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\LineTag;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\MetaPixel;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\MicrosoftAds;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\TikTok;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\TikTokPixel;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\X;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\XPixel;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\YahooJapanAds;
use PieceOfCake2\SecurityHeaders\ContentSecurityPolicy\YouTube;

return [
    'google-tag-manager' => [
        'allow' => [GoogleTagManager::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;var n=d.querySelector('[nonce]');
n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
HTML,
        'loads' => ['https://www.googletagmanager.com/gtm.js'],
    ],
    'google-analytics' => [
        'allow' => [GoogleAnalytics::class],
        'html' => <<<'HTML'
<script nonce="{nonce}" async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script nonce="{nonce}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());gtag('config','G-XXXXXXXXXX');</script>
HTML,
        'loads' => ['https://www.googletagmanager.com/gtag/js'],
    ],
    'google-maps' => [
        'allow' => [GoogleMaps::class],
        'html' => <<<'HTML'
<div id="map" style="width:320px;height:240px"></div>
<script nonce="{nonce}">function initMap(){new google.maps.Map(document.getElementById('map'),{center:{lat:35.6586,lng:139.7454},zoom:14});}</script>
<script nonce="{nonce}" async src="https://maps.googleapis.com/maps/api/js?callback=initMap"></script>
HTML,
        'loads' => ['https://maps.googleapis.com/maps/api/js'],
    ],
    'google-maps-embed' => [
        'allow' => [GoogleMapsEmbed::class],
        'html' => <<<'HTML'
<iframe width="320" height="240" src="https://www.google.com/maps?q=Tokyo+Tower&output=embed"></iframe>
HTML,
        'loads' => ['https://www.google.com/maps'],
    ],
    'google-fonts' => [
        'allow' => [GoogleFonts::class],
        'html' => <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">
<p id="google-fonts">日本語のフォント</p>
<script nonce="{nonce}">document.getElementById('google-fonts').style.fontFamily = "'Noto Sans JP'";</script>
HTML,
        'loads' => ['https://fonts.googleapis.com/css2', 'https://fonts.gstatic.com/'],
    ],
    'youtube' => [
        'allow' => [YouTube::class],
        'html' => <<<'HTML'
<img src="https://i.ytimg.com/vi/M7lc1UVf-VE/hqdefault.jpg" width="120">
<iframe width="320" height="180" src="https://www.youtube-nocookie.com/embed/M7lc1UVf-VE" allowfullscreen></iframe>
<iframe width="320" height="180" src="https://www.youtube.com/embed/M7lc1UVf-VE" allowfullscreen></iframe>
<div id="player"></div>
<script nonce="{nonce}">var tag=document.createElement('script');tag.src='https://www.youtube.com/iframe_api';document.head.appendChild(tag);
function onYouTubeIframeAPIReady(){new YT.Player('player',{height:'180',width:'320',videoId:'M7lc1UVf-VE'});}</script>
HTML,
        'loads' => [
            'https://i.ytimg.com/',
            'https://www.youtube-nocookie.com/embed/',
            'https://www.youtube.com/embed/',
            'https://www.youtube.com/iframe_api',
        ],
    ],
    'meta-pixel' => [
        'allow' => [MetaPixel::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','1234567890123456');fbq('track','PageView');</script>
HTML,
        'loads' => ['https://connect.facebook.net/en_US/fbevents.js'],
    ],
    'facebook' => [
        'allow' => [Facebook::class],
        'html' => <<<'HTML'
<div id="fb-root"></div>
<script nonce="{nonce}" async defer crossorigin="anonymous" src="https://connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v21.0"></script>
<div class="fb-page" data-href="https://www.facebook.com/facebook" data-tabs="timeline" data-width="340" data-height="300"></div>
HTML,
        'loads' => ['https://connect.facebook.net/ja_JP/sdk.js'],
    ],
    'instagram' => [
        'allow' => [Instagram::class],
        'html' => <<<'HTML'
<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/DAZ2uq3S_Sn/" data-instgrm-version="14" style="background:#FFF; border:0; max-width:540px; min-width:326px;"><a href="https://www.instagram.com/p/DAZ2uq3S_Sn/">View this post on Instagram</a></blockquote>
<script nonce="{nonce}" async src="https://www.instagram.com/embed.js"></script>
HTML,
        'loads' => ['https://www.instagram.com/embed.js'],
    ],
    'x' => [
        'allow' => [X::class],
        'html' => <<<'HTML'
<blockquote class="twitter-tweet"><p lang="en" dir="ltr">test</p>&mdash; X (@X) <a href="https://twitter.com/X/status/1683920951807971329">July 25, 2023</a></blockquote>
<script nonce="{nonce}" async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
HTML,
        'loads' => ['https://platform.twitter.com/widgets.js'],
    ],
    'x-pixel' => [
        'allow' => [XPixel::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">!function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);},s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');twq('config','o0000');</script>
HTML,
        'loads' => ['https://static.ads-twitter.com/uwt.js'],
    ],
    'tiktok' => [
        'allow' => [TikTok::class],
        'html' => <<<'HTML'
<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@scout2015/video/6718335390845095173" data-video-id="6718335390845095173" style="max-width:605px;min-width:325px"><section><a href="https://www.tiktok.com/@scout2015">@scout2015</a></section></blockquote>
<script nonce="{nonce}" async src="https://www.tiktok.com/embed.js"></script>
HTML,
        'loads' => ['https://www.tiktok.com/embed.js'],
    ],
    'tiktok-pixel' => [
        'allow' => [TikTokPixel::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">!function (w, d, t) {w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};ttq.load('C0000000000000000000');ttq.page();}(window, document, 'ttq');</script>
HTML,
        'loads' => ['https://analytics.tiktok.com/i18n/pixel/events.js'],
    ],
    'line' => [
        'allow' => [Line::class],
        'html' => <<<'HTML'
<div class="line-it-button" data-lang="ja" data-type="share-a" data-env="REAL" data-url="https://pieceofcake2.github.io/" data-color="default" data-size="small" data-count="false" data-ver="3" style="display: none;"></div>
<script nonce="{nonce}" src="https://www.line-website.com/social-plugins/js/thirdparty/loader.min.js" async="async" defer="defer"></script>
HTML,
        // The frames of the buttons are drawn late, so only the script is checked
        'loads' => ['https://www.line-website.com/social-plugins/js/thirdparty/loader.min.js'],
    ],
    'line-tag' => [
        'allow' => [LineTag::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">(function(g,d,o){g._ltq=g._ltq||[];g._lt=g._lt||function(){g._ltq.push(arguments)};var h=location.protocol==='https:'?'https://d.line-scdn.net':'http://d.line-cdn.net';var s=d.createElement('script');s.async=1;s.src=o||h+'/n/line_tag/public/release/v1/lt.js';var t=d.getElementsByTagName('script')[0];t.parentNode.insertBefore(s,t);})(window,document,'https://d.line-scdn.net/n/line_tag/public/release/v1/lt.js');
_lt('init',{customerType:'lap',tagId:'00000000-0000-0000-0000-000000000000'});_lt('send','pv',['00000000-0000-0000-0000-000000000000']);</script>
HTML,
        'loads' => ['https://d.line-scdn.net/n/line_tag/public/release/v1/lt.js'],
    ],
    'yahoo-japan-ads' => [
        'allow' => [YahooJapanAds::class],
        'html' => <<<'HTML'
<script nonce="{nonce}" async src="https://s.yimg.jp/images/listing/tool/cv/ytag.js"></script>
<script nonce="{nonce}">window.yjDataLayer=window.yjDataLayer||[];function ytag(){yjDataLayer.push(arguments);}
ytag({"type":"ycl_cookie"});
ytag({"type":"yss_conversion","config":{"yahoo_conversion_id":"1000000000","yahoo_conversion_label":"AAAAAAAAAAAAAAAAAAAA","yahoo_conversion_value":"0"}});
ytag({"type":"yss_retargeting","config":{"yahoo_ss_retargeting_id":"1000000000","yahoo_sstag_custom_params":{}}});
ytag({"type":"yjad_conversion","config":{"yahoo_ydn_conv_io":"AAAAAAAAAAAAAAAAAAAA","yahoo_ydn_conv_label":"AAAAAAAAAAAAAAAAAAAA","yahoo_ydn_conv_transaction_id":"","yahoo_ydn_conv_value":"0"}});
ytag({"type":"yjad_retargeting","config":{"yahoo_retargeting_id":"AAAAAAAAAA","yahoo_sstag_custom_params":{}}});</script>
HTML,
        'loads' => ['https://s.yimg.jp/images/listing/tool/cv/ytag.js'],
    ],
    'microsoft-ads' => [
        'allow' => [MicrosoftAds::class],
        'html' => <<<'HTML'
<script nonce="{nonce}">(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"000000000",enableAutoSpaTracking:true};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","https://bat.bing.com/bat.js","uetq");</script>
HTML,
        'loads' => ['https://bat.bing.com/bat.js'],
    ],
];
