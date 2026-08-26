<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('aa_asset_url')) {
    /**
     * Builds a public asset URL with a filemtime cache buster.
     *
     * This keeps long browser cache for public/assets safe: normal refresh will
     * request the new URL after the underlying file changes.
     */
    function aa_asset_url(string $path, ?string $protocol = null): string
    {
        $normalizedPath = ltrim($path, '/');
        $url = base_url($normalizedPath, $protocol);

        if (! str_starts_with($normalizedPath, 'assets/')) {
            return $url;
        }

        $filePath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
        if (! is_file($filePath)) {
            return $url;
        }

        $version = filemtime($filePath);
        if (! $version) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }
}
