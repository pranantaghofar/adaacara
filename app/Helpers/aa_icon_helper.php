<?php

if (! function_exists('aa_phosphor_icon')) {
    /**
     * Inline icon helper using a small Phosphor-style local set.
     *
     * Keeping icons inline avoids adding a runtime CDN dependency on shared hosting.
     */
    function aa_phosphor_icon(string $name, array $attrs = []): string
    {
        $icons = [
            'heart' => '<path d="M19.5 12.6 12 20l-7.5-7.4a5 5 0 0 1 7.1-7.1l.4.4.4-.4a5 5 0 0 1 7.1 7.1Z"/>',
            'gift' => '<path d="M4 10h16v10H4z"/><path d="M12 10v10M4 14h16M7.5 7.5A2.5 2.5 0 0 0 12 9.1 2.5 2.5 0 1 0 7.5 7.5ZM16.5 7.5A2.5 2.5 0 0 1 12 9.1a2.5 2.5 0 1 1 4.5-1.6Z"/><path d="M5 9h14v1H5z"/>',
            'music' => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>',
            'baby' => '<circle cx="12" cy="9" r="4"/><path d="M8 14.5a5 5 0 0 0 8 0M8.5 20h7M9.5 8h.01M14.5 8h.01"/>',
            'sparkles' => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3ZM5.5 15l.9 2.3 2.1.8-2.1.8-.9 2.1-.9-2.1-2.1-.8 2.1-.8.9-2.3ZM19 14l.8 2.1 2.2.9-2.2.9L19 20l-.8-2.1-2.2-.9 2.2-.9L19 14Z"/>',
            'card' => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M7 10h10M7 14h6"/>',
            'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2.5L10 4h4l1.5 2H18a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="4"/><path d="M17.5 9.5h.01"/>',
            'map-pin' => '<path d="M12 21s7-4.6 7-11a7 7 0 1 0-14 0c0 6.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
            'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3.5 2"/>',
            'cursor-click' => '<path d="m4 4 7.5 16 2-7 6.5-2L4 4Z"/><path d="M15 15.5 20 20"/>',
            'trend' => '<path d="m4 17 6-6 4 4 6-8"/><path d="M15 7h5v5"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'chevron' => '<path d="m9 18 6-6-6-6"/>',
            'back' => '<path d="m15 18-6-6 6-6"/>',
            'crown' => '<path d="m3 8 4.5 4L12 5l4.5 7L21 8l-2 10H5L3 8Z"/><path d="M5 21h14"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'login' => '<path d="M10 17v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2v1"/><path d="M3 12h12m0 0-3-3m3 3-3 3"/>',
            'register' => '<circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0M19 8v6M16 11h6"/>',
            'envelope' => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4.5 7.5 7.5 6 7.5-6"/>',
            'instagram-logo' => '<rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><path d="M17 7h.01"/>',
            'whatsapp-logo' => '<path d="m5.2 19.2 1.2-4.1a7.2 7.2 0 1 1 2.8 2.7l-4 1.4Z"/><path d="M9.4 8.7c.2-.4.4-.4.7-.4h.4c.2 0 .4.1.5.4l.7 1.5c.1.3.1.5-.1.7l-.4.4c-.1.2-.1.3 0 .5.4.8 1.1 1.5 1.9 1.9.2.1.4.1.5 0l.5-.4c.2-.2.4-.2.6-.1l1.5.7c.3.1.4.3.4.5v.4c0 .3-.1.5-.4.7-.6.4-1.4.5-2.4.2a8 8 0 0 1-5-5c-.3-1-.2-1.8.1-2.5Z"/>',
            'threads-logo' => '<path d="M17.8 9.1C17.2 6 15 4.2 11.8 4.2 7.4 4.2 4.5 7.3 4.5 12s2.9 7.8 7.4 7.8c3.8 0 6.2-2 6.2-4.9 0-2.6-2.1-4.1-5.8-4.1h-1.2"/><path d="M15.8 13.1c-.3 2-1.8 3.1-4 3.1-2.1 0-3.5-1.6-3.5-4.2 0-2.7 1.4-4.3 3.4-4.3 1.8 0 3.1 1 3.7 2.7"/>',
        ];

        $class = (string) ($attrs['class'] ?? '');
        $strokeWidth = (string) ($attrs['strokeWidth'] ?? '1.9');
        $label = trim((string) ($attrs['label'] ?? ''));
        $aria = $label === '' ? 'aria-hidden="true"' : 'aria-label="' . esc($label, 'attr') . '"';

        return '<svg' . ($class !== '' ? ' class="' . esc($class, 'attr') . '"' : '') . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . esc($strokeWidth, 'attr') . '" stroke-linecap="round" stroke-linejoin="round" ' . $aria . '>' . ($icons[$name] ?? $icons['sparkles']) . '</svg>';
    }
}
