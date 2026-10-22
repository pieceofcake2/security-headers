<?php
declare(strict_types=1);

namespace PieceOfCake2\SecurityHeaders\ContentSecurityPolicy;

use InvalidArgumentException;

/**
 * The domains of Google of the countries, for the features of Google Ads that use the domain of the country of the
 * user (GoogleAnalyticsWithAds, GoogleAds). From https://www.google.com/supported_domains: the country is the last part
 * of the domain, e.g. `uk` for `google.co.uk`. The users without a domain of their country use `google.com`
 */
final class GoogleDomains
{
    /**
     * The part of the domain after `google.`, by country
     */
    private const DOMAINS = [
        'ad' => 'ad', 'ae' => 'ae', 'af' => 'com.af', 'ag' => 'com.ag', 'al' => 'al', 'am' => 'am',
        'ao' => 'co.ao', 'ar' => 'com.ar', 'as' => 'as', 'at' => 'at', 'au' => 'com.au', 'az' => 'az',
        'ba' => 'ba', 'bd' => 'com.bd', 'be' => 'be', 'bf' => 'bf', 'bg' => 'bg', 'bh' => 'com.bh',
        'bi' => 'bi', 'bj' => 'bj', 'bn' => 'com.bn', 'bo' => 'com.bo', 'br' => 'com.br', 'bs' => 'bs',
        'bt' => 'bt', 'bw' => 'co.bw', 'by' => 'by', 'bz' => 'com.bz', 'ca' => 'ca', 'cd' => 'cd',
        'cf' => 'cf', 'cg' => 'cg', 'ch' => 'ch', 'ci' => 'ci', 'ck' => 'co.ck', 'cl' => 'cl', 'cm' => 'cm',
        'cn' => 'cn', 'co' => 'com.co', 'cr' => 'co.cr', 'cu' => 'com.cu', 'cv' => 'cv', 'cy' => 'com.cy',
        'cz' => 'cz', 'de' => 'de', 'dj' => 'dj', 'dk' => 'dk', 'dm' => 'dm', 'do' => 'com.do', 'dz' => 'dz',
        'ec' => 'com.ec', 'ee' => 'ee', 'eg' => 'com.eg', 'es' => 'es', 'et' => 'com.et', 'fi' => 'fi',
        'fj' => 'com.fj', 'fm' => 'fm', 'fr' => 'fr', 'ga' => 'ga', 'ge' => 'ge', 'gg' => 'gg',
        'gh' => 'com.gh', 'gi' => 'com.gi', 'gl' => 'gl', 'gm' => 'gm', 'gr' => 'gr', 'gt' => 'com.gt',
        'gy' => 'gy', 'hk' => 'com.hk', 'hn' => 'hn', 'hr' => 'hr', 'ht' => 'ht', 'hu' => 'hu',
        'id' => 'co.id', 'ie' => 'ie', 'il' => 'co.il', 'im' => 'im', 'in' => 'co.in', 'iq' => 'iq',
        'is' => 'is', 'it' => 'it', 'je' => 'je', 'jm' => 'com.jm', 'jo' => 'jo', 'jp' => 'co.jp',
        'ke' => 'co.ke', 'kg' => 'kg', 'kh' => 'com.kh', 'ki' => 'ki', 'kr' => 'co.kr', 'kw' => 'com.kw',
        'kz' => 'kz', 'la' => 'la', 'lb' => 'com.lb', 'li' => 'li', 'lk' => 'lk', 'ls' => 'co.ls',
        'lt' => 'lt', 'lu' => 'lu', 'lv' => 'lv', 'ly' => 'com.ly', 'ma' => 'co.ma', 'md' => 'md',
        'me' => 'me', 'mg' => 'mg', 'mk' => 'mk', 'ml' => 'ml', 'mm' => 'com.mm', 'mn' => 'mn',
        'mt' => 'com.mt', 'mu' => 'mu', 'mv' => 'mv', 'mw' => 'mw', 'mx' => 'com.mx', 'my' => 'com.my',
        'mz' => 'co.mz', 'na' => 'com.na', 'ne' => 'ne', 'ng' => 'com.ng', 'ni' => 'com.ni', 'nl' => 'nl',
        'no' => 'no', 'np' => 'com.np', 'nr' => 'nr', 'nu' => 'nu', 'nz' => 'co.nz', 'om' => 'com.om',
        'pa' => 'com.pa', 'pe' => 'com.pe', 'pg' => 'com.pg', 'ph' => 'com.ph', 'pk' => 'com.pk',
        'pl' => 'pl', 'pn' => 'pn', 'pr' => 'com.pr', 'ps' => 'ps', 'pt' => 'pt', 'py' => 'com.py',
        'qa' => 'com.qa', 'ro' => 'ro', 'rs' => 'rs', 'ru' => 'ru', 'rw' => 'rw', 'sa' => 'com.sa',
        'sb' => 'com.sb', 'sc' => 'sc', 'se' => 'se', 'sg' => 'com.sg', 'sh' => 'sh', 'si' => 'si',
        'sk' => 'sk', 'sl' => 'com.sl', 'sm' => 'sm', 'sn' => 'sn', 'so' => 'so', 'sr' => 'sr', 'st' => 'st',
        'sv' => 'com.sv', 'td' => 'td', 'tg' => 'tg', 'th' => 'co.th', 'tj' => 'com.tj', 'tl' => 'tl',
        'tm' => 'tm', 'tn' => 'tn', 'to' => 'to', 'tr' => 'com.tr', 'tt' => 'tt', 'tw' => 'com.tw',
        'tz' => 'co.tz', 'ua' => 'com.ua', 'ug' => 'co.ug', 'uk' => 'co.uk', 'uy' => 'com.uy',
        'uz' => 'co.uz', 'vc' => 'com.vc', 've' => 'co.ve', 'vi' => 'co.vi', 'vn' => 'com.vn', 'vu' => 'vu',
        'ws' => 'ws', 'za' => 'co.za', 'zm' => 'co.zm', 'zw' => 'co.zw',
    ];

    /**
     * The domain of Google of a country, e.g. `google.co.jp` for `jp`
     *
     * @param string $country Country, e.g. `jp`
     * @return string
     * @throws \InvalidArgumentException When Google has no domain for the country
     */
    public static function domain(string $country): string
    {
        $suffix = self::DOMAINS[strtolower($country)] ?? null;
        if ($suffix === null) {
            throw new InvalidArgumentException(sprintf('Google has no domain for the country: %s', $country));
        }

        return 'google.' . $suffix;
    }
}
