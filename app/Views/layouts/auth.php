<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Ada Acara') ?></title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7fb;
            --panel: #ffffff;
            --text: #172033;
            --muted: #687089;
            --line: #dce1eb;
            --primary: #176b87;
            --primary-dark: #104c60;
            --danger: #b42318;
            --success: #067647;
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

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 16px;
        }

        .auth-card {
            width: min(100%, 440px);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 28px;
            box-shadow: 0 18px 50px rgba(23, 32, 51, 0.08);
        }

        .brand {
            margin: 0 0 8px;
            font-size: 26px;
            line-height: 1.2;
        }

        .subtitle {
            margin: 0 0 24px;
            color: var(--muted);
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }

        label {
            font-weight: 650;
            font-size: 14px;
        }

        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px 14px;
            font: inherit;
            color: var(--text);
            background: #fff;
        }

        input:focus {
            outline: 3px solid rgba(23, 107, 135, 0.18);
            border-color: var(--primary);
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 6px;
            padding: 12px 14px;
            font: inherit;
            font-weight: 750;
            color: #fff;
            background: var(--primary);
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .alert {
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-danger {
            color: var(--danger);
            background: #fff3f0;
            border: 1px solid #ffdad4;
        }

        .alert-success {
            color: var(--success);
            background: #ecfdf3;
            border: 1px solid #abefc6;
        }

        .alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .switch {
            margin: 18px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <?= $this->renderSection('content') ?>
        </section>
    </main>
</body>
</html>
