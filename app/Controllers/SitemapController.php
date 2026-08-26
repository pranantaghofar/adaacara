<?php

namespace App\Controllers;

use App\Libraries\GuideArticleCatalog;
use App\Libraries\SeoLandingPageCatalog;
use App\Models\TemplateModel;
use CodeIgniter\HTTP\ResponseInterface;

class SitemapController extends BaseController
{
    private const CACHE_KEY = 'aa_dynamic_sitemap_v3';
    private const CACHE_TTL = 900;

    public function index(): ResponseInterface
    {
        $xml = $this->cachedSitemap();

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=900')
            ->setBody($xml);
    }

    private function cachedSitemap(): string
    {
        try {
            $cached = cache(self::CACHE_KEY);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        } catch (\Throwable) {
            // Sitemap must still render if cache storage is unavailable.
        }

        $xml = $this->buildSitemap();

        try {
            cache()->save(self::CACHE_KEY, $xml, self::CACHE_TTL);
        } catch (\Throwable) {
            // Ignore cache write failures.
        }

        return $xml;
    }

    private function buildSitemap(): string
    {
        $urls = [];
        $today = date('Y-m-d');

        $this->addUrl($urls, site_url('/'), $today, 'daily', '1.0');
        $this->addUrl($urls, site_url('about-us'), $today, 'monthly', '0.6');
        $this->addUrl($urls, site_url('templates'), $today, 'daily', '0.9');
        $this->addUrl($urls, site_url('panduan'), $today, 'weekly', '0.75');
        $this->addUrl($urls, site_url('plans'), $today, 'monthly', '0.7');
        $this->addUrl($urls, site_url('terms'), $today, 'yearly', '0.4');
        $this->addUrl($urls, site_url('privacy'), $today, 'yearly', '0.4');
        $this->addUrl($urls, site_url('cookies'), $today, 'yearly', '0.3');

        foreach (SeoLandingPageCatalog::pages() as $page) {
            $path = trim((string) ($page['path'] ?? ''), '/');
            if ($path === '') {
                continue;
            }

            $this->addUrl($urls, site_url($path), $today, 'monthly', '0.8');
        }

        foreach (GuideArticleCatalog::all() as $article) {
            $slug = trim((string) ($article['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $this->addUrl(
                $urls,
                site_url('panduan/' . $slug),
                (string) ($article['updated_at'] ?? $today),
                'monthly',
                '0.7'
            );
        }

        foreach ($this->templateUrls() as $template) {
            $this->addUrl(
                $urls,
                (string) $template['loc'],
                (string) $template['lastmod'],
                'weekly',
                '0.7'
            );
        }

        $body = array_map(fn (array $url): string => $this->urlNode($url), array_values($urls));

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $body)
            . "\n</urlset>\n";
    }

    /**
     * @return list<array{loc: string, lastmod: string}>
     */
    private function templateUrls(): array
    {
        try {
            $templates = (new TemplateModel())->getTemplateListingCards();
        } catch (\Throwable) {
            return [];
        }

        $urls = [];
        foreach ($templates as $template) {
            $slug = trim((string) ($template['slug'] ?? ''));
            if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                continue;
            }

            $urls[] = [
                'loc' => site_url('templates/' . $slug),
                'lastmod' => (string) ($template['updated_at'] ?? $template['approved_at'] ?? date('Y-m-d')),
            ];
        }

        return $urls;
    }

    /**
     * @param array<string, array{loc: string, lastmod: string, changefreq: string, priority: string}> $urls
     */
    private function addUrl(array &$urls, string $loc, string $lastmod, string $changefreq, string $priority): void
    {
        $loc = $this->cleanUrl($loc);
        if ($loc === '' || isset($urls[$loc])) {
            return;
        }

        $urls[$loc] = [
            'loc' => $loc,
            'lastmod' => $this->dateValue($lastmod),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * @param array{loc: string, lastmod: string, changefreq: string, priority: string} $url
     */
    private function urlNode(array $url): string
    {
        return '  <url>' . "\n"
            . '    <loc>' . $this->xml($url['loc']) . '</loc>' . "\n"
            . '    <lastmod>' . $this->xml($url['lastmod']) . '</lastmod>' . "\n"
            . '    <changefreq>' . $this->xml($url['changefreq']) . '</changefreq>' . "\n"
            . '    <priority>' . $this->xml($url['priority']) . '</priority>' . "\n"
            . '  </url>';
    }

    private function cleanUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        return str_replace(['/index.php/', '/index.php'], ['/', ''], $url);
    }

    private function dateValue($value): string
    {
        $timestamp = strtotime((string) $value);

        return $timestamp === false ? date('Y-m-d') : date('Y-m-d', $timestamp);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
