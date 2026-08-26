<?php

namespace App\Libraries;

use App\Models\AppSettingModel;

class CompanyLegalDocuments
{
    public const SETTING_KEY = 'company_legal_documents';

    private const DOCUMENTS = [
        'deed' => 'Akta Pendirian',
        'ahu' => 'SK Kemenkumham / AHU',
        'nib' => 'NIB',
        'npwp' => 'NPWP Perusahaan',
        'oss' => 'Sertifikat OSS / Perizinan Berusaha',
        'trademark' => 'Sertifikat Merek / HAKI',
        'supporting' => 'Dokumen pendukung lain',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return self::DOCUMENTS;
    }

    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, self::DOCUMENTS);
    }

    /**
     * @return array<string, array{label: string, path: string, updated_at: string}>
     */
    public static function load(?AppSettingModel $settings = null): array
    {
        $settings ??= new AppSettingModel();
        $raw = (string) ($settings->getValue(self::SETTING_KEY, '{}') ?? '{}');
        $stored = json_decode($raw, true);

        if (! is_array($stored)) {
            $stored = [];
        }

        $documents = [];
        foreach (self::DOCUMENTS as $key => $label) {
            $item = is_array($stored[$key] ?? null) ? $stored[$key] : [];
            $path = self::cleanPath((string) ($item['path'] ?? ''));

            $documents[$key] = [
                'label' => $label,
                'path' => $path,
                'updated_at' => $path !== '' ? (string) ($item['updated_at'] ?? '') : '',
            ];
        }

        return $documents;
    }

    /**
     * @param array<string, array{label?: string, path?: string, updated_at?: string}> $documents
     */
    public static function save(array $documents, int $adminId, ?AppSettingModel $settings = null): void
    {
        $settings ??= new AppSettingModel();
        $payload = [];

        foreach (self::DOCUMENTS as $key => $label) {
            $path = self::cleanPath((string) ($documents[$key]['path'] ?? ''));
            if ($path === '') {
                continue;
            }

            $payload[$key] = [
                'path' => $path,
                'updated_at' => (string) ($documents[$key]['updated_at'] ?? date('Y-m-d H:i:s')),
            ];
        }

        $settings->saveSettings([
            self::SETTING_KEY => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ], $adminId);
    }

    public static function cleanPath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') {
            return '';
        }

        if (! preg_match('#^uploads/legal-documents/[a-z0-9_-]+-[a-z0-9]{12}\.png$#i', $path)) {
            return '';
        }

        return $path;
    }
}
