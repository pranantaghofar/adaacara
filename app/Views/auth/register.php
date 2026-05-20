<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="brand">Buat Akun</h1>
    <p class="subtitle">Mulai buat landing page event kamu.</p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form action="<?= site_url('register') ?>" method="post">
        <div class="field">
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="<?= old('name') ?>" autocomplete="name" required>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= old('email') ?>" autocomplete="email" required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
        </div>

        <div class="field">
            <label for="password_confirm">Konfirmasi Password</label>
            <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
        </div>

        <button class="btn" type="submit">Daftar</button>
    </form>

    <p class="switch">Sudah punya akun? <a href="<?= site_url('login') ?>">Login</a></p>
<?= $this->endSection() ?>
