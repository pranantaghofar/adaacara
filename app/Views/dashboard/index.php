<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Ada Acara</title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --text: #172033;
            --muted: #687089;
            --line: #dce1eb;
            --primary: #176b87;
            --primary-dark: #104c60;
            --success: #067647;
            --warning: #946100;
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
            color: inherit;
        }

        .topbar {
            border-bottom: 1px solid var(--line);
            background: var(--panel);
        }

        .topbar-inner {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
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

        .user-meta {
            color: var(--muted);
            font-size: 14px;
        }

        .logout {
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: var(--text);
            padding: 10px 14px;
            font: inherit;
            cursor: pointer;
        }

        .logout:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .page {
            width: min(1120px, calc(100% - 32px));
            margin: 32px auto;
        }

        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 28px;
        }

        .hero h2 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 14px;
            background: #fff;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        .btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            text-decoration: none;
        }

        .btn-primary {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .btn-disabled {
            pointer-events: none;
            color: #98a2b3;
            background: #f2f4f7;
        }

        .section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin: 28px 0 14px;
        }

        .section-head h3 {
            margin: 0;
            font-size: 20px;
        }

        .section-head p {
            margin: 4px 0 0;
            color: var(--muted);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }

        th,
        td {
            padding: 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: var(--muted);
            font-size: 13px;
            font-weight: 750;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .title {
            font-weight: 750;
        }

        .muted {
            color: var(--muted);
            font-size: 14px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            border-radius: 999px;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 750;
        }

        .badge-draft {
            color: var(--warning);
            background: #fff7db;
        }

        .badge-published {
            color: var(--success);
            background: #ecfdf3;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .empty {
            border: 1px dashed #b8c0d2;
            border-radius: 8px;
            padding: 28px;
            background: rgba(255, 255, 255, 0.62);
        }

        .empty h3 {
            margin: 0 0 8px;
        }

        .empty p {
            margin: 0 0 18px;
            color: var(--muted);
        }

        .alert {
            border-radius: 6px;
            padding: 12px 14px;
            margin: 0 0 18px;
            color: var(--success);
            background: #ecfdf3;
            border: 1px solid #abefc6;
        }

        @media (max-width: 760px) {
            .topbar-inner,
            .hero,
            .section-head {
                align-items: stretch;
                flex-direction: column;
            }

            .hero {
                padding: 22px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div>
                <h1 class="brand">Ada Acara</h1>
                <div class="user-meta"><?= esc($userName) ?> · <?= esc($userEmail) ?></div>
            </div>
            <form action="<?= site_url('logout') ?>" method="post">
                <button class="logout" type="submit">Logout</button>
            </form>
        </div>
    </header>

    <main class="page">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>

        <section class="hero">
            <div>
                <h2>Dashboard Landing Page</h2>
                <p>Kelola landing page event yang terhubung dengan akun kamu.</p>
            </div>
            <a class="btn btn-primary" href="<?= site_url('templates') ?>">Buat Landing Page Baru</a>
        </section>

        <section>
            <div class="section-head">
                <div>
                    <h3>Landing Page Saya</h3>
                    <p><?= count($landingPages) ?> halaman ditemukan.</p>
                </div>
            </div>

            <?php if ($landingPages === []): ?>
                <div class="empty">
                    <h3>Belum ada landing page</h3>
                    <p>Mulai dari template, lalu edit konten event dengan visual builder.</p>
                    <a class="btn btn-primary" href="<?= site_url('templates') ?>">Buat Landing Page Baru</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Slug</th>
                                <th>Tanggal Event</th>
                                <th>Status</th>
                                <th>Update Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($landingPages as $page): ?>
                                <?php
                                    $status = $page['status'] ?? 'draft';
                                    $isPublished = $status === 'published';
                                ?>
                                <tr>
                                    <td>
                                        <div class="title"><?= esc($page['title']) ?></div>
                                        <div class="muted">ID #<?= esc((string) $page['id']) ?></div>
                                    </td>
                                    <td><?= esc($page['slug']) ?></td>
                                    <td><?= esc($page['event_date'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge <?= $isPublished ? 'badge-published' : 'badge-draft' ?>">
                                            <?= $isPublished ? 'Published' : 'Draft' ?>
                                        </span>
                                    </td>
                                    <td><?= esc($page['updated_at'] ?: $page['created_at'] ?: '-') ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn" href="<?= site_url('editor/' . $page['id']) ?>">Edit</a>
                                            <a class="btn" href="<?= site_url('preview/' . $page['id']) ?>">Preview</a>
                                            <?php if ($isPublished): ?>
                                                <a class="btn" href="<?= site_url('u/' . $page['slug']) ?>" target="_blank" rel="noopener">Buka Link</a>
                                            <?php else: ?>
                                                <span class="btn btn-disabled">Belum Publish</span>
                                            <?php endif ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </section>
    </main>
</body>
</html>
