# Deploy Ada Acara ke IDwebhost

Checklist ini dipakai untuk menampilkan update terbaru project di `https://adaacara.com/`.

## 1. File yang perlu diupload

Upload isi project lokal dari:

```text
/Users/mac/Documents/ADAACARA-WEB/adaacara
```

ke folder hosting:

```text
public_html
```

Minimal upload folder/file berikut:

```text
app/
public/
vendor/
writable/
composer.json
composer.lock
spark
.env
```

File SQL:

```text
database/adaacara_schema.sql
```

File SQL tidak wajib berada di hosting. Gunakan untuk import database lewat phpMyAdmin.

## 2. Struktur hosting yang direkomendasikan

Jika cPanel mengizinkan mengatur document root subdomain, arahkan `adaacara.com` ke:

```text
public_html/public
```

Struktur yang benar:

```text
public_html/
├── app/
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── ...
├── vendor/
├── writable/
├── composer.json
├── composer.lock
├── spark
└── .env
```

## 3. Konfigurasi .env hosting

Pastikan `.env` di hosting berisi:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://adaacara.com/'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = adaacara_user
database.default.username = adaacara_user
database.default.password = "password_database_kamu"
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Sesuaikan password dengan password database IDwebhost yang aktif.

## 4. Import database

1. Login ke cPanel IDwebhost.
2. Buka phpMyAdmin.
3. Pilih database `adaacara_user`.
4. Buka tab SQL atau Import.
5. Jalankan isi file:

```text
database/adaacara_schema.sql
```

## 5. Permission folder

Pastikan folder berikut bisa ditulis aplikasi:

```text
writable/
writable/cache/
writable/logs/
writable/session/
writable/uploads/
```

Rekomendasi awal: permission `755`. Jika session/log/cache error, coba `775`.

## 6. Jika document root tidak bisa diarahkan ke public

Gunakan opsi ini hanya jika cPanel tidak bisa mengarahkan subdomain ke `public_html/public`.

1. Pindahkan isi folder `public/` ke root `public_html/`.
2. Sesuaikan path di `public_html/index.php` agar menunjuk ke folder project CI4.
3. Pastikan folder `app/`, `vendor/`, dan `writable/` tidak bisa diakses publik secara langsung.

Opsi terbaik tetap document root ke `public/`.

## 7. Cek setelah upload

Buka:

```text
https://adaacara.com/
https://adaacara.com/register
https://adaacara.com/login
```

Setelah login:

```text
https://adaacara.com/dashboard
https://adaacara.com/templates
```

Jika muncul error database, cek ulang `.env`, nama database, username, password, dan hasil import SQL.
