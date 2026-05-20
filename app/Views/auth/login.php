<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="brand">Login</h1>
    <p class="subtitle">Masuk ke dashboard Ada Acara.</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form action="<?= site_url('login') ?>" method="post">
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= old('email') ?>" autocomplete="email" required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>

        <button class="btn" type="submit">Login</button>
    </form>

    <p class="switch">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar</a></p>
<?= $this->endSection() ?>
