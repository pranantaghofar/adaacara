<?php
    $items = array_values(array_filter((array) ($items ?? []), static function ($item): bool {
        return is_array($item) && trim((string) ($item['label'] ?? '')) !== '';
    }));
?>
<?php if ($items !== []): ?>
    <style>
        .aa-breadcrumb-pill {
            display: inline-flex;
            max-width: 100%;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(226, 232, 240, .92);
            border-radius: 999px;
            background: rgba(255, 255, 255, .78);
            padding: 7px 11px;
            color: #64748b;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
            font-size: 12px;
            font-weight: 900;
            line-height: 1;
        }

        .aa-breadcrumb-pill a {
            color: inherit;
            text-decoration: none;
            transition: color .18s ease;
        }

        .aa-breadcrumb-pill a:hover {
            color: #7550c4;
        }

        .aa-breadcrumb-pill-separator {
            color: #cbd5e1;
        }

        .aa-breadcrumb-pill-current {
            color: #0f172a;
        }

        html[data-aa-public-theme="dark"] .aa-breadcrumb-pill {
            border-color: rgba(148, 163, 184, .2);
            background: rgba(15, 23, 42, .68);
            color: #cbd5e1;
            box-shadow: 0 16px 42px rgba(0, 0, 0, .18);
        }

        html[data-aa-public-theme="dark"] .aa-breadcrumb-pill-current {
            color: #f8fafc;
        }

        @media (max-width: 640px) {
            .aa-breadcrumb-pill {
                gap: 6px;
                padding: 7px 10px;
                font-size: 11px;
            }
        }
    </style>
    <nav class="aa-breadcrumb-pill" aria-label="Breadcrumb">
        <?php foreach ($items as $index => $item): ?>
            <?php
                $label = (string) ($item['label'] ?? '');
                $url = trim((string) ($item['url'] ?? ''));
                $isLast = $index === array_key_last($items);
            ?>
            <?php if ($index > 0): ?>
                <span class="aa-breadcrumb-pill-separator" aria-hidden="true">&gt;</span>
            <?php endif ?>
            <?php if (! $isLast && $url !== ''): ?>
                <a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a>
            <?php else: ?>
                <span class="aa-breadcrumb-pill-current" aria-current="<?= $isLast ? 'page' : 'false' ?>"><?= esc($label) ?></span>
            <?php endif ?>
        <?php endforeach ?>
    </nav>
<?php endif ?>
