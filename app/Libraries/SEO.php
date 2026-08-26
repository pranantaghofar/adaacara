<?php

namespace App\Libraries;

use Config\SEO as SEOConfig;

class SEO
{
    private SEOConfig $config;

    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $schemas = [];

    public function __construct(?SEOConfig $config = null)
    {
        $this->config = $config ?? new SEOConfig();
        $this->reset();
    }

    public function reset(): self
    {
        $this->meta = [
            'title' => $this->config->defaultTitle,
            'description' => $this->config->defaultDescription,
            'canonical' => $this->config->baseUrl . '/',
            'image' => $this->config->defaultImage,
            'type' => 'website',
            'robots' => $this->config->defaultRobots,
            'locale' => $this->config->locale,
            'site_name' => $this->config->siteName,
        ];
        $this->schemas = [];

        return $this;
    }

    public function title(?string $title): self
    {
        $title = $this->cleanText($title);
        if ($title !== '') {
            $this->meta['title'] = $this->config->appendTitleSuffix
                ? $this->appendSuffix($title)
                : $title;
        }

        return $this;
    }

    public function description(?string $description): self
    {
        $description = $this->cleanText($description);
        if ($description !== '') {
            $this->meta['description'] = $this->limit($description, 300);
        }

        return $this;
    }

    public function canonical(?string $url): self
    {
        $url = $this->absoluteUrl($url);
        if ($url !== '') {
            $this->meta['canonical'] = $url;
        }

        return $this;
    }

    public function image(?string $url): self
    {
        $url = $this->absoluteUrl($url);
        if ($url !== '') {
            $this->meta['image'] = $url;
        }

        return $this;
    }

    public function type(?string $type): self
    {
        $type = strtolower($this->cleanText($type));
        $this->meta['type'] = $type !== '' ? $type : 'website';

        return $this;
    }

    public function robots(?string $robots): self
    {
        $robots = $this->cleanText($robots);
        if ($robots !== '') {
            $this->meta['robots'] = $robots;
        }

        return $this;
    }

    public function website(): self
    {
        $this->type('website');
        $this->schema($this->organizationSchema());
        $this->schema($this->websiteSchema());
        $this->schema($this->webApplicationSchema());

        return $this;
    }

    /**
     * @param list<array{0?: string, 1?: string}|array{question?: string, answer?: string}> $items
     */
    public function faq(array $items): self
    {
        $entities = [];

        foreach ($items as $item) {
            $question = is_array($item) ? $this->cleanText((string) ($item['question'] ?? $item[0] ?? '')) : '';
            $answer = is_array($item) ? $this->cleanText((string) ($item['answer'] ?? $item[1] ?? '')) : '';
            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($entities !== []) {
            $this->schema([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
            ]);
        }

        return $this;
    }

    /**
     * @param list<array{name?: string, url?: string}|array{0?: string, 1?: string}> $items
     */
    public function breadcrumb(array $items): self
    {
        $elements = [];
        $position = 1;

        foreach ($items as $item) {
            $name = is_array($item) ? $this->cleanText((string) ($item['name'] ?? $item[0] ?? '')) : '';
            $url = is_array($item) ? $this->absoluteUrl((string) ($item['url'] ?? $item[1] ?? '')) : '';
            if ($name === '' || $url === '') {
                continue;
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url,
            ];
        }

        if ($elements !== []) {
            $this->schema([
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $elements,
            ]);
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $template
     */
    public function product(array $template): self
    {
        $name = $this->cleanText((string) ($template['name'] ?? $template['title'] ?? 'Template AdaAcara'));
        $description = $this->cleanText((string) ($template['description'] ?? 'Template undangan digital AdaAcara.'));
        $image = $this->absoluteUrl((string) ($template['thumbnail'] ?? $template['image'] ?? $this->config->defaultImage));
        $price = (string) ($template['price'] ?? $template['harga'] ?? '0');

        $this->title($name)
            ->description($description)
            ->type('product')
            ->image($image);

        $this->schema([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->config->siteName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => preg_replace('/[^0-9]/', '', $price) ?: '0',
                'availability' => 'https://schema.org/InStock',
            ],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $event
     */
    public function event(array $event): self
    {
        $name = $this->cleanText((string) ($event['name'] ?? $event['title'] ?? 'Undangan AdaAcara'));
        $description = $this->cleanText((string) ($event['description'] ?? $this->config->defaultDescription));
        $url = $this->absoluteUrl((string) ($event['url'] ?? $this->meta['canonical']));
        $startDate = $this->cleanText((string) ($event['startDate'] ?? $event['event_date'] ?? ''));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'eventAttendanceMode' => 'https://schema.org/MixedEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'organizer' => [
                '@type' => 'Organization',
                'name' => $this->config->siteName,
                'url' => $this->config->baseUrl,
            ],
        ];

        if ($startDate !== '') {
            $schema['startDate'] = $startDate;
        }

        $this->title($name)
            ->description($description)
            ->type('event')
            ->schema($schema);

        return $this;
    }

    /**
     * @param array<string, mixed> $schema
     */
    public function schema(array $schema): self
    {
        if ($schema !== []) {
            $this->schemas[] = $schema;
        }

        return $this;
    }

    public function render(): string
    {
        $payload = [
            'meta' => $this->meta,
            'schemas' => $this->schemas,
        ];
        $cacheKey = 'aa_seo_' . hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: serialize($payload));

        if ($this->config->cacheEnabled) {
            try {
                $cached = cache($cacheKey);
                if (is_string($cached) && $cached !== '') {
                    return $cached;
                }
            } catch (\Throwable) {
                // Rendering SEO must never break the page if cache is unavailable.
            }
        }

        $html = $this->renderMeta() . $this->renderSchemas();

        if ($this->config->cacheEnabled) {
            try {
                cache()->save($cacheKey, $html, max(60, $this->config->cacheTtl));
            } catch (\Throwable) {
                // Ignore cache write failures.
            }
        }

        return $html;
    }

    private function renderMeta(): string
    {
        $title = $this->cleanText((string) ($this->meta['title'] ?? $this->config->defaultTitle));
        $description = $this->cleanText((string) ($this->meta['description'] ?? $this->config->defaultDescription));
        $canonical = $this->absoluteUrl((string) ($this->meta['canonical'] ?? $this->config->baseUrl . '/'));
        $image = $this->absoluteUrl((string) ($this->meta['image'] ?? $this->config->defaultImage));
        $type = $this->cleanText((string) ($this->meta['type'] ?? 'website'));
        $robots = $this->cleanText((string) ($this->meta['robots'] ?? $this->config->defaultRobots));
        $siteName = $this->cleanText((string) ($this->meta['site_name'] ?? $this->config->siteName));
        $locale = $this->cleanText((string) ($this->meta['locale'] ?? $this->config->locale));

        $lines = [
            '<title>' . esc($title) . '</title>',
            '<meta name="description" content="' . esc($description, 'attr') . '">',
            '<meta name="robots" content="' . esc($robots, 'attr') . '">',
            '<link rel="canonical" href="' . esc($canonical, 'attr') . '">',
            '<meta property="og:locale" content="' . esc($locale, 'attr') . '">',
            '<meta property="og:type" content="' . esc($type, 'attr') . '">',
            '<meta property="og:site_name" content="' . esc($siteName, 'attr') . '">',
            '<meta property="og:title" content="' . esc($title, 'attr') . '">',
            '<meta property="og:description" content="' . esc($description, 'attr') . '">',
            '<meta property="og:url" content="' . esc($canonical, 'attr') . '">',
            '<meta property="og:image" content="' . esc($image, 'attr') . '">',
            '<meta property="og:image:alt" content="' . esc($title, 'attr') . '">',
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="' . esc($title, 'attr') . '">',
            '<meta name="twitter:description" content="' . esc($description, 'attr') . '">',
            '<meta name="twitter:image" content="' . esc($image, 'attr') . '">',
        ];

        if ($this->config->twitterSite !== '') {
            $lines[] = '<meta name="twitter:site" content="' . esc($this->config->twitterSite, 'attr') . '">';
        }
        if ($this->config->twitterCreator !== '') {
            $lines[] = '<meta name="twitter:creator" content="' . esc($this->config->twitterCreator, 'attr') . '">';
        }

        return implode("\n    ", $lines) . "\n    ";
    }

    private function renderSchemas(): string
    {
        $chunks = [];

        foreach ($this->schemas as $schema) {
            $schema = $this->removeEmpty($schema);
            if ($schema === []) {
                continue;
            }

            $json = json_encode(
                $schema,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
                | JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_AMP
                | JSON_HEX_QUOT
            );
            if (is_string($json) && $json !== '') {
                $chunks[] = '<script type="application/ld+json">' . $json . '</script>';
            }
        }

        return $chunks === [] ? '' : implode("\n    ", $chunks) . "\n    ";
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $this->config->baseUrl . '/#organization',
            'name' => $this->config->organization['name'] ?? $this->config->siteName,
            'alternateName' => $this->config->organization['brand'] ?? $this->config->siteName,
            'url' => $this->config->baseUrl,
            'logo' => $this->config->logo,
            'sameAs' => $this->config->sameAs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $this->config->baseUrl . '/#website',
            'url' => $this->config->baseUrl . '/',
            'name' => $this->config->siteName,
            'publisher' => [
                '@id' => $this->config->baseUrl . '/#organization',
            ],
            'inLanguage' => $this->config->language,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function webApplicationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $this->config->webApplication['name'] ?? $this->config->siteName,
            'url' => $this->config->baseUrl . '/',
            'applicationCategory' => $this->config->webApplication['applicationCategory'] ?? 'DesignApplication',
            'operatingSystem' => $this->config->webApplication['operatingSystem'] ?? 'Web',
            'description' => $this->config->defaultDescription,
            'offers' => [
                '@type' => 'Offer',
                'price' => $this->config->webApplication['offersPrice'] ?? '0',
                'priceCurrency' => $this->config->webApplication['offersCurrency'] ?? 'IDR',
            ],
        ];
    }

    private function appendSuffix(string $title): string
    {
        $suffix = $this->cleanText($this->config->titleSuffix);
        if ($suffix === '' || str_contains($title, $suffix)) {
            return $title;
        }

        return $title . ' - ' . $suffix;
    }

    private function cleanText(?string $value): string
    {
        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    private function limit(string $value, int $limit): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(substr($value, 0, max(0, $limit - 3))) . '...';
    }

    private function absoluteUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $this->withoutIndexPage($url);
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return rtrim($this->config->baseUrl, '/') . '/' . ltrim($url, '/');
    }

    private function withoutIndexPage(string $url): string
    {
        return str_replace(['/index.php/', '/index.php'], ['/', ''], $url);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function removeEmpty($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $clean = [];
        foreach ($value as $key => $item) {
            $item = $this->removeEmpty($item);
            if ($item === '' || $item === null || $item === []) {
                continue;
            }
            $clean[$key] = $item;
        }

        return $clean;
    }
}
