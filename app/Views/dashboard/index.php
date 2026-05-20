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
            margin: 0 0 20px;
            color: var(--muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border-radius: 6px;
            padding: 0 16px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            font-weight: 750;
        }

        .btn:hover {
            background: var(--primary-dark);
            text-decoration: none;
        }

        .placeholder {
            margin-top: 24px;
            border: 1px dashed #b8c0d2;
            border-radius: 8px;
            padding: 22px;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.58);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <h1 class="brand">Ada Acara</h1>
            <form action="<?= site_url('logout') ?>" method="post">
                <button class="logout" type="submit">Logout</button>
            </form>
        </div>
    </header>

    <main class="page">
        <section class="hero">
            <h2>Halo, <?= esc($userName) ?></h2>
            <p><?= esc($userEmail) ?> sudah login. Berikutnya kita tambahkan pilihan template dan landing page builder.</p>
            <a class="btn" href="#">Pilih Template</a>
        </section>

        <section class="placeholder">
            Belum ada landing page. Pada tahap berikutnya, daftar halaman event kamu akan tampil di sini.
        </section>
    </main>
</body>
</html>
