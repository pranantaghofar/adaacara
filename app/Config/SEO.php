<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SEO extends BaseConfig
{
    public string $siteName = 'AdaAcara';
    public string $locale = 'id_ID';
    public string $language = 'id';
    public string $baseUrl = 'https://adaacara.com';
    public string $defaultTitle = 'AdaAcara - Buat & Jual Undangan Digital Online';
    public string $titleSuffix = 'AdaAcara';
    public bool $appendTitleSuffix = false;
    public string $defaultDescription = 'AdaAcara adalah editor visual untuk membuat undangan digital, landing page event, RSVP, ucapan tamu, wedding gift, dan halaman publik dengan editor desain seperti Canva.';
    public string $defaultImage = 'https://adaacara.com/assets/img/og-default.png';
    public string $logo = 'https://adaacara.com/assets/img/logo2.png';
    public string $favicon = 'https://adaacara.com/assets/img/logo2.png';
    public string $twitterSite = '@adaacara';
    public string $twitterCreator = '@adaacara';
    public string $defaultRobots = 'index, follow, max-image-preview:large';
    public bool $cacheEnabled = true;
    public int $cacheTtl = 3600;
    public bool $indexNowEnabled = true;
    public string $indexNowKey = '444072ecb01677872e3e4867810f81af';
    public string $indexNowEndpoint = 'https://api.indexnow.org/indexnow';

    /**
     * @var list<string>
     */
    public array $indexNowBlockedPathPrefixes = [
        '/admin',
        '/editor',
        '/dashboard',
        '/checkout',
        '/orders',
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/verify-email',
        '/creator',
        '/seller',
        '/preview',
        '/templates/preview/',
        '/u/',
    ];

    /**
     * @var list<string>
     */
    public array $sameAs = [
        'https://www.instagram.com/adaacara.official/',
    ];

    /**
     * @var array<string, string>
     */
    public array $organization = [
        'name' => 'PT Shagania Labs Indonesia',
        'brand' => 'AdaAcara',
        'url' => 'https://adaacara.com',
        'email' => '',
    ];

    /**
     * @var array<string, string>
     */
    public array $webApplication = [
        'name' => 'AdaAcara',
        'applicationCategory' => 'DesignApplication',
        'operatingSystem' => 'Web',
        'offersPrice' => '0',
        'offersCurrency' => 'IDR',
    ];
}
