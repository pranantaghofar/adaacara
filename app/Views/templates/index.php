<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --text: #172033;
            --muted: #687089;
            --line: #dce1eb;
            --primary: #176b87;
            --primary-dark: #104c60;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .topbar {
            border-bottom: 1px solid var(--line);
            background: var(--panel);
        }

        .topbar-inner,
        .page {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            margin: 0;
            font-size: 20px;
        }

        .page {
            padding: 32px 0;
        }

        .heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .heading h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .heading p {
            margin: 0;
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .template-card {
            display: grid;
            gap: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            background: var(--panel);
        }

        .preview {
            min-height: 170px;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
            background: #f8fafc;
        }

        .preview iframe {
            width: 100%;
            height: 170px;
            border: 0;
            display: block;
            pointer-events: none;
            background: #fff;
        }

        .template-title {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .template-description {
            margin: 0;
            color: var(--muted);
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 12px;
        }

        label {
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 11px 12px;
            font: inherit;
        }

        input:focus {
            outline: 3px solid rgba(23, 107, 135, 0.18);
            border-color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            padding: 0 14px;
            color: #fff;
            background: var(--primary);
            font: inherit;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-dark);
            color: #fff;
            text-decoration: none;
        }

        .alert {
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .alert-danger {
            color: var(--danger);
            background: #fff3f0;
            border: 1px solid #ffdad4;
        }

        .alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .empty {
            border: 1px dashed #b8c0d2;
            border-radius: 8px;
            padding: 28px;
            background: rgba(255, 255, 255, 0.62);
            color: var(--muted);
        }

        @media (max-width: 820px) {
            .grid,
            .heading {
                grid-template-columns: 1fr;
            }

            .heading {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <h1 class="brand">Ada Acara</h1>
            <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        </div>
    </header>

    <main class="page">
        <section class="heading">
            <div>
                <h1>Pilih Template</h1>
                <p>Pilih template aktif, isi detail acara, lalu buat landing page baru.</p>
            </div>
        </section>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <?php if ($templates === []): ?>
            <div class="empty">Belum ada template aktif. Tambahkan template aktif ke database terlebih dahulu.</div>
        <?php else: ?>
            <section class="grid">
                <?php foreach ($templates as $template): ?>
                    <?php
                        $html = $template['html'] ?? '';
                        $css = $template['css'] ?? '';
                        $preview = '<!doctype html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>' . $html . '</body></html>';
                    ?>
                    <article class="template-card">
                        <div class="preview">
                            <iframe title="<?= esc($template['name']) ?> preview" srcdoc="<?= esc($preview, 'attr') ?>"></iframe>
                        </div>

                        <div>
                            <h2 class="template-title"><?= esc($template['name']) ?></h2>
                            <p class="template-description"><?= esc($template['description'] ?: 'Template landing page siap pakai.') ?></p>
                        </div>

                        <form action="<?= site_url('templates/create') ?>" method="post">
                            <input type="hidden" name="template_id" value="<?= esc((string) $template['id']) ?>">

                            <div class="field">
                                <label for="title-<?= esc((string) $template['id']) ?>">Judul Acara</label>
                                <input id="title-<?= esc((string) $template['id']) ?>" name="title" type="text" value="<?= esc(old('title')) ?>" placeholder="Contoh: Wedding Sarah & Dimas" required>
                            </div>

                            <div class="field">
                                <label for="slug-<?= esc((string) $template['id']) ?>">Slug URL</label>
                                <input id="slug-<?= esc((string) $template['id']) ?>" name="slug" type="text" value="<?= esc(old('slug')) ?>" placeholder="contoh: wedding-sarah-dimas">
                            </div>

                            <div class="field">
                                <label for="event-date-<?= esc((string) $template['id']) ?>">Tanggal Event</label>
                                <input id="event-date-<?= esc((string) $template['id']) ?>" name="event_date" type="date" value="<?= esc(old('event_date')) ?>">
                            </div>

                            <button class="btn" type="submit">Buat Landing Page</button>
                        </form>
                    </article>
                <?php endforeach ?>
            </section>
        <?php endif ?>
    </main>
</body>
</html>
