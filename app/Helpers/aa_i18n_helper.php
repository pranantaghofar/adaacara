<?php

use Config\AaTranslations;

if (! function_exists('aa_supported_langs')) {
    function aa_supported_langs(): array
    {
        return ['id', 'en', 'ms'];
    }
}

if (! function_exists('aa_current_lang')) {
    function aa_current_lang(): string
    {
        $request = service('request');
        $supported = aa_supported_langs();
        $queryLang = strtolower(trim((string) ($request->getGet('lang') ?? '')));

        if (in_array($queryLang, $supported, true)) {
            try {
                session()->set('aa_lang', $queryLang);
            } catch (Throwable $error) {
            }

            return $queryLang;
        }

        try {
            $sessionLang = strtolower(trim((string) (session()->get('aa_lang') ?? '')));
            if (in_array($sessionLang, $supported, true)) {
                return $sessionLang;
            }
        } catch (Throwable $error) {
        }

        $cookieLang = strtolower(trim((string) ($request->getCookie('aa_lang') ?? '')));

        return in_array($cookieLang, $supported, true) ? $cookieLang : 'id';
    }
}

if (! function_exists('aa_t')) {
    function aa_t(string $key, string $fallback, array $replace = [], ?string $lang = null): string
    {
        $lang = $lang ?: aa_current_lang();
        if ($lang === 'id') {
            $text = $fallback;
        } else {
            $config = config(AaTranslations::class);
            $text = (string) ($config->translations[$lang][$key] ?? $fallback);
        }

        foreach ($replace as $name => $value) {
            $text = str_replace('{' . $name . '}', (string) $value, $text);
        }

        return $text;
    }
}

if (! function_exists('aa_lang_label')) {
    function aa_lang_label(string $lang): string
    {
        return match ($lang) {
            'en' => 'English',
            'ms' => 'Malaysia',
            default => 'Indonesia',
        };
    }
}

if (! function_exists('aa_lang_url')) {
    function aa_lang_url(?string $url = null, ?string $lang = null): string
    {
        $request = service('request');
        $url = $url ?: current_url();
        $lang = in_array($lang, aa_supported_langs(), true) ? $lang : aa_current_lang();
        $parts = parse_url($url);
        $query = [];

        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        } else {
            $query = $request->getGet() ?? [];
        }

        $query['lang'] = $lang;
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? $url;
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $port . $path . '?' . http_build_query($query) . $fragment;
    }
}
