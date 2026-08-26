<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

class SeedBusinessProfileMuaTemplate extends BaseCommand
{
    protected $group = 'AdaAcara';
    protected $name = 'templates:seed-business-mua';
    protected $description = 'Create or update the default editable Business Profile MUA template.';
    protected $usage = 'templates:seed-business-mua [--assets-only]';

    private string $assetBaseUrl = '/uploads/templates/business-profile/mua-soft-glam';
    private int $assetSize = 900;

    public function run(array $params)
    {
        $assets = $this->writeAssets();
        $editorJson = $this->buildEditorJson($assets);
        $this->writeEditorJsonArtifact($editorJson);

        if ((bool) CLI::getOption('assets-only')) {
            CLI::write('Asset dan editor-json template dibuat di ' . $this->assetBaseUrl, 'green');
            return;
        }

        try {
            $db = Database::connect();

            if (! $db->tableExists('templates')) {
                CLI::error('Tabel templates belum tersedia. Jalankan SQL modul template terlebih dahulu.');
                return;
            }

            $columns = $db->getFieldNames('templates');
            if (! in_array('project_type', $columns, true)) {
                CLI::error('Kolom templates.project_type belum tersedia. Jalankan database/alter_business_profile_project_type.sql.');
                return;
            }

            if (! in_array('tags', $columns, true)) {
                $db->query('ALTER TABLE `templates` ADD COLUMN `tags` TEXT NULL AFTER `description`');
                $columns = $db->getFieldNames('templates');
            }

            $now = date('Y-m-d H:i:s');
            $payload = [
                'category_id' => null,
                'project_type' => 'business_profile',
                'name' => 'MUA Soft Glam Studio',
                'slug' => 'mua-soft-glam-studio',
                'description' => 'Template Business Profile MUA bergaya soft glam, wedding, engagement, portfolio, paket layanan, testimoni, dan booking.',
                'tags' => 'business profile,MUA,mua,makeup,make up artist,soft glam,wedding makeup,bridal makeup',
                'preview_url' => '',
                'thumbnail' => ltrim($assets['thumbnail'], '/'),
                'html' => '',
                'css' => '',
                'js' => '',
                'editor_json' => $editorJson,
                'editor_type' => 'fabric',
                'grapesjs_json' => $editorJson,
                'is_premium' => 0,
                'status' => 'active',
                'is_active' => 1,
                'owner_user_id' => null,
                'created_by_role' => 'admin',
                'seller_plan_name' => null,
                'review_status' => 'not_required',
                'public_status' => 'public',
                'submitted_at' => null,
                'approved_at' => $now,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
                'source_invitation_id' => null,
                'usage_count' => 0,
                'publish_count' => 0,
                'updated_at' => $now,
            ];

            $payload = array_intersect_key($payload, array_flip($columns));
            $builder = $db->table('templates');
            $existing = $builder->where('slug', 'mua-soft-glam-studio')->get()->getRowArray();

            if (is_array($existing)) {
                $builder->where('id', (int) $existing['id'])->update($payload);
                CLI::write('Template Business Profile MUA diperbarui: mua-soft-glam-studio', 'green');
                return;
            }

            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = $now;
            }

            $builder->insert($payload);
            CLI::write('Template Business Profile MUA dibuat: mua-soft-glam-studio', 'green');
        } catch (Throwable $error) {
            CLI::error('Asset berhasil dibuat, tetapi seed database gagal: ' . $error->getMessage());
        }
    }

    private function buildEditorJson(array $assets): string
    {
        $objects = [];
        $objects[] = $this->rect(0, 0, 1080, 4760, '#fff8f7');
        $objects[] = $this->rect(8, 8, 1064, 4744, 'rgba(255,255,255,0)', 0, '#f7c4cc', 3);

        foreach ([620, 1188, 1762, 2300, 2828, 3224, 3732, 4152] as $lineTop) {
            $objects[] = $this->rect(0, $lineTop, 1080, 2, '#f6c6ce');
        }

        $objects[] = $this->image($assets['hero'], 545, 62, 455, 455);
        $objects[] = $this->image($assets['vanity'], 865, 402, 165, 165);
        $objects[] = $this->text('Nama MUA Studio', 52, 70, 365, 44, '#c84d68', 34, 'Georgia', 'bold');
        $objects[] = $this->text('Soft glam, wedding,\nengagement, dan\nspecial event makeup', 54, 185, 430, 154, '#8b3546', 42, 'Georgia', 'bold');
        $objects[] = $this->text('Cantik natural, percaya diri\nsepanjang hari.', 56, 350, 360, 55, '#8c6069', 22);
        $objects[] = $this->button('Book Now', 52, 442, 330, 62, '#df4c73');
        $objects[] = $this->button('View Portfolio', 52, 522, 330, 56, '#ffffff', '#df4c73');
        $objects[] = $this->miniFeature('Hasil Tahan Lama', 92, 603);
        $objects[] = $this->miniFeature('Produk Premium', 330, 603);
        $objects[] = $this->miniFeature('Layanan Profesional', 590, 603);

        $objects = array_merge($objects, $this->sectionHeader('PORTFOLIO', 'Makeup Portfolio', 'Tampilkan beberapa hasil makeup terbaik dengan\njudul singkat dan gaya makeup.', 675));
        $portfolioTitles = ['Soft Glam', 'Wedding Glam', 'Natural Look', 'Engagement Look'];
        foreach ($portfolioTitles as $index => $title) {
            $left = 52 + ($index * 250);
            $objects[] = $this->card($left, 850, 210, 275);
            $objects[] = $this->image($assets['portfolio' . ($index + 1)], $left + 15, 866, 180, 162);
            $objects[] = $this->text($title, $left + 20, 1052, 170, 24, '#d64d71', 19, 'Georgia', 'bold', 'center');
            $objects[] = $this->text('Portfolio ' . ($index + 1), $left + 20, 1090, 170, 22, '#624047', 17, 'Arial', 'bold', 'center');
        }

        $objects = array_merge($objects, $this->sectionHeader('TRANSFORMASI', 'Before / After', 'Bandingkan foto sebelum dan sesudah makeup\ndalam layout dua kolom.', 1225));
        $objects[] = $this->card(125, 1405, 350, 350);
        $objects[] = $this->card(605, 1405, 350, 350);
        $objects[] = $this->image($assets['before'], 145, 1423, 310, 248);
        $objects[] = $this->image($assets['after'], 625, 1423, 310, 248);
        $objects[] = $this->pill('BEFORE', 145, 1682, 310, 54, '#fff8f8', '#8b3546');
        $objects[] = $this->pill('AFTER', 625, 1682, 310, 54, '#fff8f8', '#8b3546');
        $objects[] = $this->circle(540, 1548, 34, '#df4c73');
        $objects[] = $this->text('>', 526, 1530, 34, 38, '#ffffff', 34, 'Arial', 'bold', 'center');

        $objects = array_merge($objects, $this->sectionHeader('PAKET LAYANAN', 'Makeup Packages', 'Susun paket makeup, benefit utama, dan harga\nmulai.', 1810));
        $packages = [
            ['Basic', 'Rp650.000', ['Makeup natural', 'Produk premium', 'Tahan hingga 8 jam']],
            ['Premium', 'Rp1.250.000', ['Makeup flawless', 'Produk premium', 'Hairdo', 'Touch up']],
            ['Signature', 'Rp1.850.000', ['Makeup luxury', 'False lashes', 'Hairdo', 'Touch up']],
        ];
        foreach ($packages as $index => $package) {
            $left = 52 + ($index * 340);
            $objects[] = $this->card($left, 1972, 300, 330, $index === 1 ? '#fff1f4' : '#ffffff', '#f4a3b3');
            if ($index === 1) {
                $objects[] = $this->pill('REKOMENDASI', $left + 76, 1950, 150, 34, '#df4c73', '#ffffff', 18);
            }
            $objects[] = $this->text($package[0], $left + 38, 2015, 224, 42, '#c84d68', 32, 'Georgia', 'bold', 'center');
            foreach ($package[2] as $lineIndex => $line) {
                $objects[] = $this->text('- ' . $line, $left + 46, 2088 + ($lineIndex * 32), 220, 25, '#6f4e56', 18);
            }
            $objects[] = $this->text('Mulai dari', $left + 64, 2218, 170, 24, '#d64d71', 18, 'Arial', 'bold', 'center');
            $objects[] = $this->text($package[1], $left + 42, 2248, 215, 40, '#d64d71', 30, 'Georgia', 'bold', 'center');
        }

        $objects[] = $this->image($assets['artist'], 72, 2360, 390, 390);
        $objects = array_merge($objects, $this->sectionHeader('LAYANAN', 'Artist Profile', 'Perkenalkan artist, pengalaman,\ndan ciri khas layanan.', 2375, 520));
        $objects[] = $this->text('Nama Artist', 545, 2530, 360, 46, '#8b3546', 42, 'Georgia', 'bold');
        $objects[] = $this->text('Make Up Artist profesional untuk wedding, engagement, prewedding, dan special event.', 548, 2590, 420, 86, '#6f4e56', 21);
        $objects[] = $this->text('Ciri khas:', 548, 2700, 140, 26, '#c84d68', 20, 'Arial', 'bold');
        $objects[] = $this->text('Soft glam, flawless, natural glowing,\ndan tahan lama.', 548, 2732, 405, 54, '#6f4e56', 20);
        $objects[] = $this->circle(560, 2808, 22, '#df4c73');
        $objects[] = $this->circle(625, 2808, 22, '#df4c73');
        $objects[] = $this->circle(690, 2808, 22, '#df4c73');

        $objects = array_merge($objects, $this->sectionHeader('TIM KAMI', 'Team MUA', 'Tampilkan anggota tim dan peran masing-masing.', 2880));
        $team = ['Ayu Lestari', 'Dewi Anggraini', 'Rina Amelia'];
        foreach ($team as $index => $name) {
            $left = 112 + ($index * 315);
            $objects[] = $this->card($left, 3058, 235, 215);
            $objects[] = $this->image($assets['team' . ($index + 1)], $left + 32, 3076, 170, 132);
            $objects[] = $this->text($name, $left + 24, 3220, 187, 24, '#8b3546', 19, 'Arial', 'bold', 'center');
            $objects[] = $this->text($index === 1 ? 'Hairdo Specialist' : 'Makeup Artist', $left + 30, 3248, 175, 22, '#d64d71', 16, 'Arial', 'normal', 'center');
        }

        $objects = array_merge($objects, $this->sectionHeader('PRODUK YANG DIGUNAKAN', 'Products Used', 'Cantumkan brand atau produk makeup\nyang sering digunakan.', 3320));
        $products = [
            ['Base', 'Estee Lauder,\nMAC, NARS'],
            ['Complexion', 'Dior, Armani,\nMake Up For Ever'],
            ['Eyes', 'Huda Beauty,\nToo Faced'],
            ['Lips', 'Charlotte Tilbury,\nMaybelline'],
            ['Tools', 'Real Techniques,\nSigma'],
            ['Skin Prep', 'Skinceuticals,\nCeraVe'],
        ];
        foreach ($products as $index => $product) {
            $left = 52 + (($index % 3) * 340);
            $top = 3508 + ((int) floor($index / 3) * 145);
            $objects[] = $this->card($left, $top, 300, 130, '#fffdfc', '#f6bec8');
            $objects[] = $this->text($product[0], $left + 112, $top + 26, 158, 28, '#d64d71', 25, 'Georgia', 'bold');
            $objects[] = $this->text($product[1], $left + 112, $top + 64, 160, 52, '#624047', 18);
            $objects[] = $this->image($assets['product'], $left + 28, $top + 26, 68, 68);
        }

        $objects = array_merge($objects, $this->sectionHeader('TESTIMONI', 'Apa Kata Klien', '', 3820));
        $reviews = ['Makeupnya flawless dan tahan lama banget.', 'Pelayanannya ramah, hasilnya melebihi ekspektasi.', 'Terima kasih sudah bikin aku percaya diri.'];
        foreach ($reviews as $index => $review) {
            $left = 72 + ($index * 315);
            $objects[] = $this->card($left, 3975, 270, 205);
            $objects[] = $this->text('*****', $left + 70, 4008, 130, 26, '#f3b038', 23, 'Arial', 'bold', 'center');
            $objects[] = $this->text('"' . $review . '"', $left + 34, 4056, 205, 64, '#624047', 17, 'Arial', 'normal', 'center');
            $objects[] = $this->text('- Klien ' . ($index + 1), $left + 74, 4130, 130, 26, '#8b3546', 18, 'Arial', 'bold', 'center');
        }

        $objects = array_merge($objects, $this->sectionHeader('HUBUNGI & BOOKING', 'Contact & Booking', 'Kami siap membantumu hari spesialmu.', 4215));
        $objects[] = $this->text('0821-1234-5678\n@namamuastudio\nJakarta, Indonesia\nSenin - Minggu\n08.00 - 20.00 WIB', 86, 4375, 420, 170, '#624047', 24, 'Arial', 'bold');
        $objects[] = $this->image($assets['contact'], 565, 4340, 410, 220);
        $objects[] = $this->button('Book Now', 565, 4572, 410, 72, '#df4c73');
        $objects[] = $this->text('Nama MUA Studio', 0, 4668, 1080, 34, '#8b3546', 25, 'Georgia', 'bold', 'center');
        $objects[] = $this->text('Soft glam, flawless, unforgettable.', 0, 4708, 1080, 28, '#8c6069', 18, 'Arial', 'normal', 'center');

        $payload = [
            'renderer' => 'fabric',
            'projectIntent' => 'business_profile',
            'editMode' => 'pages',
            'mode' => 'website-pages',
            'activePageIndex' => 0,
            'pages' => [[
                'id' => 'business-profile-mua-soft-glam',
                'title' => 'Business Profile MUA',
                'objects' => $objects,
                'background' => '#fff8f7',
                'backgroundColor' => '#fff8f7',
                'artboard' => ['width' => 1080, 'height' => 4760],
                'hidden' => false,
                'renderer' => 'fabric-page',
                'version' => '5.3.0',
            ]],
            'guestbook' => ['enabled' => false],
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function writeAssets(): array
    {
        $dir = FCPATH . ltrim($this->assetBaseUrl, '/');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $assets = [
            'hero' => $this->portraitSvg('#f7c9c9', '#9b4b56', 'hero'),
            'portfolio1' => $this->portraitSvg('#f6d4d0', '#8d454f', 'soft'),
            'portfolio2' => $this->portraitSvg('#f8c8be', '#7a3d48', 'crown'),
            'portfolio3' => $this->portraitSvg('#f3c2bc', '#84444e', 'glam'),
            'portfolio4' => $this->portraitSvg('#f3d6cf', '#775058', 'hijab'),
            'before' => $this->portraitSvg('#ead6ca', '#6f5050', 'bare'),
            'after' => $this->portraitSvg('#f6c8c8', '#8a4250', 'after'),
            'artist' => $this->portraitSvg('#f5c7c9', '#8b3546', 'artist'),
            'team1' => $this->portraitSvg('#f4c9c6', '#8b4651', 'team'),
            'team2' => $this->portraitSvg('#f2d0c7', '#70464d', 'hijab'),
            'team3' => $this->portraitSvg('#f5d0cb', '#80404c', 'team'),
            'product' => $this->productSvg(),
            'vanity' => $this->vanitySvg(),
            'contact' => $this->contactSvg(),
            'thumbnail' => $this->thumbnailSvg(),
        ];

        $paths = [];
        foreach ($assets as $key => $svg) {
            $path = $this->assetBaseUrl . '/' . $key . '.svg';
            file_put_contents(FCPATH . ltrim($path, '/'), $svg);
            $paths[$key] = $path;
        }

        $referencePath = FCPATH . ltrim($this->assetBaseUrl . '/reference.png', '/');
        if (is_file($referencePath)) {
            $paths['reference'] = $this->assetBaseUrl . '/reference.png';
            $paths['thumbnail'] = $paths['reference'];
        }

        return $paths;
    }

    private function writeEditorJsonArtifact(string $editorJson): void
    {
        $path = FCPATH . ltrim($this->assetBaseUrl . '/editor-json.json', '/');
        file_put_contents($path, json_encode(json_decode($editorJson, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function rect(float $left, float $top, float $width, float $height, string $fill, float $radius = 0, string $stroke = '', float $strokeWidth = 0): array
    {
        return [
            'type' => 'rect',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
            'fill' => $fill,
            'stroke' => $stroke,
            'strokeWidth' => $strokeWidth,
            'rx' => $radius,
            'ry' => $radius,
            'selectable' => true,
            'evented' => true,
        ];
    }

    private function card(float $left, float $top, float $width, float $height, string $fill = '#ffffff', string $stroke = '#f7c4cc'): array
    {
        return $this->rect($left, $top, $width, $height, $fill, 24, $stroke, 2);
    }

    private function circle(float $left, float $top, float $radius, string $fill): array
    {
        return [
            'type' => 'circle',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'radius' => $radius,
            'fill' => $fill,
        ];
    }

    private function text(string $text, float $left, float $top, float $width, float $height, string $fill, int $fontSize = 22, string $fontFamily = 'Arial', string $fontWeight = 'normal', string $textAlign = 'left'): array
    {
        return [
            'type' => 'textbox',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
            'fill' => $fill,
            'fontSize' => $fontSize,
            'fontFamily' => $fontFamily,
            'fontWeight' => $fontWeight,
            'textAlign' => $textAlign,
            'lineHeight' => 1.16,
            'text' => $text,
            'splitByGrapheme' => false,
        ];
    }

    private function image(string $src, float $left, float $top, float $width, float $height): array
    {
        return [
            'type' => 'image',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'width' => $this->assetSize,
            'height' => $this->assetSize,
            'scaleX' => $width / $this->assetSize,
            'scaleY' => $height / $this->assetSize,
            'src' => $src,
            'crossOrigin' => 'anonymous',
        ];
    }

    private function button(string $label, float $left, float $top, float $width, float $height, string $fill, string $textColor = '#ffffff'): array
    {
        return [
            'type' => 'group',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
            'objects' => [
                $this->rect(-$width / 2, -$height / 2, $width, $height, $fill, 28, '#df4c73', 3),
                $this->text($label, -$width / 2, -13, $width, 28, $textColor, 22, 'Arial', 'bold', 'center'),
            ],
        ];
    }

    private function pill(string $label, float $left, float $top, float $width, float $height, string $fill, string $textColor, int $fontSize = 20): array
    {
        $group = [
            'type' => 'group',
            'version' => '5.3.0',
            'originX' => 'left',
            'originY' => 'top',
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
            'objects' => [
                $this->rect(-$width / 2, -$height / 2, $width, $height, $fill, $height / 2, '#f0a8b6', 1),
                $this->text($label, -$width / 2, -($fontSize / 2), $width, $fontSize + 4, $textColor, $fontSize, 'Arial', 'bold', 'center'),
            ],
        ];

        return $group;
    }

    private function miniFeature(string $label, float $left, float $top): array
    {
        return $this->text($label, $left - 52, $top + 48, 160, 24, '#9b6c73', 16, 'Arial', 'bold', 'center');
    }

    private function sectionHeader(string $eyebrow, string $title, string $subtitle, int $top, int $left = 0): array
    {
        $center = $left > 0 ? $left : 0;
        return [
            $this->text($eyebrow, $center, $top, 1080 - $center, 28, '#df6d86', 19, 'Arial', 'bold', 'center'),
            $this->text($title, $center, $top + 34, 1080 - $center, 58, '#7c3441', 45, 'Georgia', 'bold', 'center'),
            $this->text($subtitle, $center, $top + 100, 1080 - $center, 36, '#6f4e56', 20, 'Arial', 'normal', 'center'),
        ];
    }

    private function portraitSvg(string $skin, string $line, string $variant): string
    {
        $hair = in_array($variant, ['bare'], true) ? '#5b3a35' : '#6d382e';
        $veil = in_array($variant, ['hijab'], true) ? '<path d="M248 142 C120 200 122 610 242 732 C412 724 574 646 628 490 C690 302 540 122 362 112 C314 106 278 118 248 142Z" fill="#eec8c4" opacity=".88"/>' : '';
        $crown = in_array($variant, ['crown', 'hero', 'after'], true) ? '<path d="M490 214 l28 -38 l22 48 l52 -8 l-36 40 l34 42 l-54 -6 l-24 48 l-26 -48 l-52 6 l34 -42 l-34 -40Z" fill="#fff5f6" stroke="#d48b96" stroke-width="6"/>' : '';
        $brush = $variant === 'artist' ? '<path d="M590 560 L760 740" stroke="#754246" stroke-width="22" stroke-linecap="round"/><path d="M720 712 L794 794" stroke="#e5a1ab" stroke-width="42" stroke-linecap="round"/>' : '';

        return $this->svg(900, 900, <<<SVG
<defs>
<radialGradient id="bg" cx="45%" cy="28%" r="68%"><stop offset="0" stop-color="#fff7f8"/><stop offset="1" stop-color="#f8cdd2"/></radialGradient>
<linearGradient id="dress" x1="0" x2="1"><stop offset="0" stop-color="#f7b9c3"/><stop offset="1" stop-color="#fff2f3"/></linearGradient>
</defs>
<rect width="900" height="900" rx="48" fill="url(#bg)"/>
<circle cx="450" cy="430" r="330" fill="#fff9f8" opacity=".52"/>
{$veil}
<path d="M256 344 C238 220 334 118 468 138 C594 156 656 260 632 390 C610 514 532 604 420 596 C312 588 254 480 256 344Z" fill="{$hair}"/>
<path d="M304 354 C310 242 392 174 496 206 C586 234 628 330 594 448 C558 574 464 642 368 584 C316 552 292 464 304 354Z" fill="{$skin}" stroke="{$line}" stroke-width="4"/>
<path d="M338 332 C396 228 520 222 592 320 C580 220 506 156 414 166 C326 176 262 252 274 360 C296 354 318 344 338 332Z" fill="{$hair}"/>
<path d="M376 410 C404 394 432 394 460 410 M504 410 C532 394 560 394 588 410" fill="none" stroke="#5b3138" stroke-width="9" stroke-linecap="round"/>
<path d="M476 432 C466 482 458 506 444 532" fill="none" stroke="#bd7882" stroke-width="7" stroke-linecap="round"/>
<path d="M406 558 C454 586 512 586 560 548" fill="none" stroke="#c65370" stroke-width="10" stroke-linecap="round"/>
<circle cx="368" cy="455" r="20" fill="#f0a4ad" opacity=".42"/>
<circle cx="584" cy="455" r="20" fill="#f0a4ad" opacity=".42"/>
<path d="M230 850 C272 672 632 650 692 850Z" fill="url(#dress)"/>
<path d="M280 720 C230 662 224 594 270 552 C318 646 392 688 502 680 C434 720 362 742 280 720Z" fill="#fff1f2" opacity=".65"/>
{$crown}
{$brush}
<g opacity=".72"><path d="M144 202 l18 34 l36 16 l-36 16 l-18 34 l-18 -34 l-36 -16 l36 -16Z" fill="#f2a9b6"/><path d="M742 148 l14 28 l30 12 l-30 14 l-14 28 l-14 -28 l-30 -14 l30 -12Z" fill="#f2a9b6"/></g>
SVG);
    }

    private function productSvg(): string
    {
        return $this->svg(900, 900, <<<SVG
<rect width="900" height="900" rx="48" fill="#fff8f7"/>
<rect x="130" y="230" width="190" height="420" rx="28" fill="#edc1aa" stroke="#c98089" stroke-width="8"/>
<rect x="165" y="140" width="120" height="120" rx="22" fill="#7c3441"/>
<circle cx="525" cy="430" r="150" fill="#f2d0c8" stroke="#d77b8f" stroke-width="10"/>
<circle cx="525" cy="430" r="94" fill="#fff3f2"/>
<path d="M640 275 L780 208 L814 286 L674 352Z" fill="#d9909b" stroke="#7c3441" stroke-width="9"/>
<path d="M720 322 L802 514" stroke="#7c3441" stroke-width="24" stroke-linecap="round"/>
SVG);
    }

    private function vanitySvg(): string
    {
        return $this->svg(900, 900, <<<SVG
<rect width="900" height="900" rx="48" fill="#fff6f5"/>
<circle cx="570" cy="360" r="190" fill="#fff" stroke="#d78c98" stroke-width="16"/>
<circle cx="570" cy="360" r="132" fill="#f4d2d3"/>
<rect x="188" y="500" width="150" height="270" rx="32" fill="#e7a0ab" stroke="#8b3546" stroke-width="9"/>
<rect x="218" y="418" width="90" height="120" rx="22" fill="#8b3546"/>
<path d="M406 675 C466 570 588 566 640 675Z" fill="#f2c0c8"/>
<path d="M146 250 C230 194 314 192 392 250 C314 306 230 306 146 250Z" fill="#f5b3bf" stroke="#d86b84" stroke-width="7"/>
SVG);
    }

    private function contactSvg(): string
    {
        return $this->svg(900, 900, <<<SVG
<defs><linearGradient id="g" x1="0" x2="1"><stop offset="0" stop-color="#f9d8d6"/><stop offset="1" stop-color="#fff8f6"/></linearGradient></defs>
<rect width="900" height="900" rx="42" fill="url(#g)"/>
<rect x="60" y="78" width="340" height="320" rx="28" fill="#fff" opacity=".62"/>
<circle cx="644" cy="244" r="170" fill="#fff" stroke="#e7a1ad" stroke-width="12"/>
<rect x="520" y="210" width="250" height="190" rx="26" fill="#eeb8bd"/>
<path d="M120 320 C190 200 290 200 360 320" fill="none" stroke="#d86b84" stroke-width="16" stroke-linecap="round"/>
<circle cx="160" cy="238" r="46" fill="#f7b5bf"/><circle cx="240" cy="216" r="54" fill="#f9c9cf"/><circle cx="320" cy="244" r="42" fill="#f3a5b3"/>
SVG);
    }

    private function thumbnailSvg(): string
    {
        return $this->svg(1080, 1400, <<<SVG
<rect width="1080" height="1400" fill="#fff8f7"/>
<circle cx="760" cy="360" r="270" fill="#f8cdd2"/>
<text x="70" y="165" font-family="Georgia" font-size="70" font-weight="700" fill="#c84d68">Nama MUA Studio</text>
<text x="70" y="365" font-family="Georgia" font-size="86" font-weight="700" fill="#8b3546">Soft glam</text>
<text x="70" y="460" font-family="Georgia" font-size="64" font-weight="700" fill="#8b3546">wedding makeup</text>
<rect x="70" y="570" width="340" height="82" rx="41" fill="#df4c73"/>
<text x="240" y="622" text-anchor="middle" font-family="Arial" font-size="32" font-weight="700" fill="#fff">Book Now</text>
<rect x="70" y="790" width="940" height="500" rx="34" fill="#fff" stroke="#f4bac5" stroke-width="4"/>
<text x="540" y="900" text-anchor="middle" font-family="Georgia" font-size="62" font-weight="700" fill="#7c3441">Makeup Portfolio</text>
<rect x="120" y="980" width="190" height="220" rx="24" fill="#f3c2bc"/><rect x="330" y="980" width="190" height="220" rx="24" fill="#f6d4d0"/><rect x="540" y="980" width="190" height="220" rx="24" fill="#f2d0c7"/><rect x="750" y="980" width="190" height="220" rx="24" fill="#f5d0cb"/>
SVG);
    }

    private function svg(int $width, int $height, string $body): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">' . $body . '</svg>';
    }
}
