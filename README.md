# 📋 Dokumentasi Lengkap Project adaacara

## 🏗️ Gambaran Umum

**adaAcara** adalah platform web **pembuatan undangan digital** berbasis **CodeIgniter 4** (PHP).  
Platform ini memungkinkan pengguna membuat, mengedit, dan mempublikasikan undangan digital (pernikahan, ulang tahun, dsb.) secara online dengan sistem editor berbasis drag-and-drop (Fabric.js).

> **URL Produksi:** https://adaacara.com  
> **Framework:** CodeIgniter 4 (PHP)  
> **Database:** MySQL (nama DB: `adaacara_user`)  
> **Email Service:** Brevo (SMTP & API)  
> **Payment Gateway:** Midtrans (snap) + Transfer Manual  
> **OAuth:** Google Login  
> **Remove BG:** Poof.bg API  

---

## 📁 Struktur Folder Utama

```
adaacara/
├── app/
│   ├── Config/         → Konfigurasi (Routes, SEO, Database, Email, dll.)
│   ├── Controllers/    → Logic utama tiap halaman / fitur
│   ├── Models/         → Interaksi database (Query Builder)
│   ├── Views/          → Template HTML yang ditampilkan ke user
│   ├── Libraries/      → Service / helper class kustom
│   ├── Filters/        → Middleware (auth, guest, admin check)
│   ├── Helpers/        → Fungsi bantu global
│   └── Language/       → File terjemahan
├── database/           → File SQL migrasi skema database
├── public/             → Entry point (index.php), assets publik
├── scripts/            → Script CLI / utilitas
├── writable/           → Cache, log, session
└── vendor/             → Dependensi Composer
```

---

## 🔐 Sistem Autentikasi (`AuthController.php`)

Mengatur semua proses login, registrasi, dan keamanan akun.

| Fungsi | Deskripsi |
|---|---|
| `register()` | Tampilkan halaman daftar akun |
| `attemptRegister()` | Proses registrasi: validasi, hash password, simpan user, kirim email verifikasi |
| `login()` | Tampilkan halaman login |
| `attemptLogin()` | Proses login: cek email+password, cek verifikasi email, buat session |
| `googleRedirect()` | Redirect ke halaman OAuth Google (buat state CSRF) |
| `googleCallback()` | Proses callback dari Google: verifikasi state, ambil profil, login atau buat akun baru |
| `logout()` | Hancurkan session, redirect ke login |
| `forgotPassword()` | Tampilkan form lupa password |
| `sendPasswordReset()` | Kirim email reset password via Brevo API (rate-limited: maks 3x/15 menit) |
| `resetPassword()` | Tampilkan form ganti password (validasi token) |
| `updatePassword()` | Simpan password baru, invalidate token lama |
| `verifyEmail()` | Verifikasi email lewat token URL |
| `verificationNotice()` | Halaman instruksi cek email |
| `resendVerificationEmail()` | Kirim ulang email verifikasi |

### 🔑 Mekanisme Token Keamanan
- Token email verifikasi & reset password menggunakan pola **selector.validator** (hex encoded)
- Validator di-hash dengan `sha256` sebelum disimpan ke DB → aman dari timing attack
- Token reset password **expired 1 jam**, token verifikasi email **expired 24 jam**

---

## 🛣️ Routing (`Config/Routes.php`)

Semua URL dikelompokkan menjadi 4 grup besar:

### 1. Route Publik (tanpa login)
| URL | Fungsi |
|---|---|
| `/` | Halaman beranda (Home) |
| `/templates` | Galeri template undangan |
| `/templates/preview/:id` | Preview template |
| `/plans` | Halaman paket harga |
| `/sitemap.xml` | Sitemap untuk SEO |
| `/terms`, `/privacy`, `/cookies` | Halaman legal |
| `/u/:slug` | Halaman undangan yang sudah dipublish |
| `/share-whatsapp` | Fitur share via WhatsApp |

### 2. Route Guest-Only (redirect jika sudah login)
| URL | Fungsi |
|---|---|
| `/register` | Daftar akun baru |
| `/login` | Login |
| `/forgot-password` | Lupa password |
| `/reset-password/:token` | Reset password |
| `/verify-email/:token` | Verifikasi email |
| `/auth/google` | Login Google |

### 3. Route Authenticated (wajib login)
| URL | Fungsi |
|---|---|
| `/dashboard` | Dashboard user |
| `/editor/:id` | Editor undangan |
| `/plans` + `/checkout/:slug` | Pembelian paket |
| `/orders` | Riwayat pesanan |
| `/seller/*` | Dashboard penjual (butuh membership seller) |
| `/creator/*` | Dashboard creator (butuh status creator aktif) |

### 4. Route Admin (role admin)
| URL | Fungsi |
|---|---|
| `/admin` | Dashboard admin |
| `/admin/users` | Manajemen user |
| `/admin/orders` | Manajemen pesanan |
| `/admin/templates` | Manajemen template |
| `/admin/seller-templates` | Review template dari creator |
| `/admin/creator-applications` | Review lamaran creator |
| `/admin/payment-settings` | Konfigurasi metode pembayaran |
| `/admin/editor-ads` | Iklan dalam editor |
| `/admin/custom-fonts` | Font kustom |

---

## 🎨 Editor Undangan (`EditorController.php`)

Ini adalah **inti dari aplikasi** — editor berbasis Fabric.js untuk mendesain undangan digital.

| Fungsi | Deskripsi |
|---|---|
| `edit(id)` | Load halaman editor dengan data undangan, subscription info, template categories, font kustom, dan iklan editor |
| `save(id)` | Auto-save desain (HTML, CSS, JS, JSON editor, thumbnail) |
| `publish(id)` | Publish undangan ke URL publik (validasi membership, slug, limit publish) |
| `unpublish(id)` | Kembalikan undangan ke status draft |
| `checkSlug()` | Cek apakah URL slug tersedia |
| `assets(id)` | Ambil daftar gambar milik user untuk di-insert ke editor |
| `uploadAsset(id)` | Upload gambar baru untuk dipakai di editor |
| `preview(id)` | Preview undangan (tanpa cache) |

### 🔒 Logika Hak Akses Publish
1. **Admin** → bebas publish apa saja
2. **Creator aktif** → bisa publish (menggunakan fitur premium editor)
3. **User dengan membership aktif** → bisa publish sesuai limit paket
4. **User Free** + template free → boleh publish 1 undangan gratis selama 1 bulan
5. **User Free** + template premium → **wajib beli paket**

### 💾 Fitur Penyimpanan Gambar
- **Inline images (base64)** dari editor otomatis dikonversi ke file fisik di `uploads/editor-inline/`
- **Thumbnail dashboard** disimpan di `uploads/editor-thumbnails/`
- Gambar dioptimasi saat upload

---

## 🏠 Dashboard User (`DashboardController.php`)

| Fungsi | Deskripsi |
|---|---|
| `index()` | Tampilkan semua undangan user, status subscription, saldo creator, batas publish |
| `guestbook(id)` | Lihat daftar tamu untuk undangan tertentu |
| `shareWhatsapp(id)` | Redirect ke halaman share WhatsApp |
| `deletePage(id)` | Hapus undangan beserta data tamu |

**Data yang ditampilkan di dashboard:**
- Jumlah undangan (total, draft, expired)
- Status membership & tanggal kadaluarsa
- Jumlah total tamu di buku tamu
- Saldo wallet creator
- Akses ke Seller/Creator Dashboard

---

## 💳 Sistem Pembayaran (`PaymentController.php`)

Mendukung **2 mode pembayaran**: Transfer Manual & Midtrans (otomatis).

| Fungsi | Deskripsi |
|---|---|
| `plans()` | Tampilkan halaman paket harga + status subscription user |
| `checkout(slug)` | Form checkout untuk paket tertentu |
| `storeCheckout(slug)` | Proses pembuatan invoice, redirect ke Midtrans atau halaman manual |
| `orders()` | Daftar invoice/pesanan user |
| `detail(id)` | Detail satu pesanan |
| `uploadProof(id)` | Upload bukti transfer (jpg/png/webp, maks 2MB) |
| `midtransNotification()` | **Webhook** dari Midtrans (verifikasi signature SHA-512) |
| `midtransNotificationStatus()` | Health check endpoint Midtrans |

### 🔄 Alur Pembayaran Manual
```
Checkout → Pilih metode → Buat Invoice (status: pending)
→ Upload bukti bayar (status: waiting_approval)
→ Admin approve (status: paid) → Subscription aktif
```

### 🔄 Alur Pembayaran Midtrans
```
Checkout → Midtrans Snap Token dibuat via API
→ Redirect ke halaman Midtrans
→ Callback webhook (verifikasi signature)
→ Status: paid → Subscription aktif otomatis
```

### 💼 Paket yang Tersedia
| Slug | Tier |
|---|---|
| `buat-pakai-sendiri` / `basic` | Basic (1 undangan) |
| `buat-coba-jualan` / `premium` | Premium (3 undangan + fitur seller) |
| `buat-niat-jualan` / `business` | Business (10 undangan + komisi lebih kecil) |
| `creator` | Creator (tidak terbatas + seller marketplace) |

---

## 👑 Panel Admin (`AdminController.php`)

| Fungsi | Deskripsi |
|---|---|
| `dashboard()` | Statistik: total user, halaman, order, tamu buku tamu |
| `orders()` | Daftar semua pesanan dengan filter status/metode/paket |
| `approveOrder(id)` | Setujui pembayaran → aktifkan subscription |
| `rejectOrder(id)` | Tolak pembayaran |
| `updatePlan(id)` | Edit detail paket (nama, harga, masa aktif, limit halaman) |
| `togglePlan(id)` | Aktifkan/nonaktifkan paket |
| `users()` | Daftar semua user dengan filter role/pencarian |
| `pages()` | Daftar semua undangan yang dibuat user |
| `guestbooks()` | Daftar semua entri buku tamu |
| `creatorApplications()` | Daftar lamaran creator |
| `approveCreatorApplication(id)` | Setujui lamaran → update role ke `creator`, buat profil creator |
| `rejectCreatorApplication(id)` | Tolak lamaran dengan alasan |
| `paymentSettings()` | Halaman pengaturan pembayaran |
| `updatePaymentSettings()` | Simpan konfigurasi Midtrans (dilindungi password admin) |

### 🔔 Badge Notifikasi Admin
Admin secara otomatis melihat badge angka untuk:
- Order `waiting_approval`
- User baru hari ini
- Template pending review
- Lamaran creator pending
- Request withdraw pending

---

## 🛍️ Seller & Creator Dashboard (`SellerTemplateController.php`)

### Seller (membership premium/business)
| Fungsi | Deskripsi |
|---|---|
| `dashboard()` | Overview leads & statistik penjualan |
| `leads()` | CRM: daftar prospek customer |
| `storeLead()` | Tambah lead baru |
| `leadDetail(id)` | Detail lead + template pesan WhatsApp |
| `updateLead(id)` | Update status & data lead |
| `whatsappTemplates()` | Template pesan WhatsApp siap pakai |
| `promoAssets()` | Aset promosi untuk seller |

### Creator (role creator)
| Fungsi | Deskripsi |
|---|---|
| `creatorDashboard()` | Dashboard creator: saldo, ringkasan template |
| `templates()` | Daftar template yang dibuat creator |
| `templateDetail(id)` | Detail template + riwayat komisi |
| `resubmit(id)` | Kirim ulang template yang direject untuk review |
| `archive(id)` | Arsipkan template |
| `earnings()` | Laporan pendapatan & riwayat withdraw |
| `storeWithdrawRequest()` | Ajukan request withdraw (butuh verifikasi password) |
| `saveFromEditor()` | Submit template baru dari editor (untuk review admin) |

---

## 💰 Sistem Komisi Creator (`SellerTemplateService.php`)

Ini adalah service paling kompleks — mengurus komisi creator.

| Fungsi | Deskripsi |
|---|---|
| `isActiveCreator(userId)` | Cek apakah user adalah creator aktif |
| `canSaveTemplate(userId)` | Cek boleh simpan template (hanya creator aktif) |
| `processSellerTemplateCommission(invitationId, publisherUserId)` | **Proses komisi** saat undangan dipublish |
| `calculateSellerCommissionFromOrder(order)` | Hitung komisi: **70% dari harga paket** |
| `walletBalance(userId)` | Ambil saldo wallet: available, pending, withdrawn |
| `createWithdrawRequest(userId, payload)` | Buat request penarikan saldo |
| `updateWithdrawStatus(adminId, requestId, action)` | Admin approve/reject/mark-paid withdraw |
| `createTemplateUsage(invitationId, template, userId)` | Catat penggunaan template oleh user |

### 🔄 Alur Komisi
```
User beli paket "Buat Pakai Sendiri"
→ Pakai template dari creator
→ Publish undangan
→ Sistem cek: ada order yang sudah paid?
→ Hitung komisi 70% dari harga paket
→ Tambah ke wallet creator (status: available)
→ Creator request withdraw
→ Admin proses transfer
```

---

## 🔍 Library SEO (`Libraries/SEO.php`)

Library untuk menghasilkan meta tag SEO secara otomatis.

| Fungsi | Deskripsi |
|---|---|
| `title(string)` | Set title halaman (dengan suffix " - adaAcara") |
| `description(string)` | Set meta description (maks 300 karakter) |
| `canonical(url)` | Set URL canonical |
| `image(url)` | Set OG image |
| `website()` | Mode website — tambah schema Organization, WebSite, WebApplication |
| `faq(items)` | Tambah schema FAQPage |
| `breadcrumb(items)` | Tambah schema BreadcrumbList |
| `product(template)` | Tambah schema Product (untuk template) |
| `event(data)` | Tambah schema Event (untuk undangan) |
| `render()` | Generate semua meta tag + JSON-LD schema (dengan cache) |

**Output yang dihasilkan:**
- `<title>`, `<meta description>`, `<link canonical>`
- Open Graph tags (og:title, og:image, og:description, dll.)
- Twitter Card tags
- JSON-LD structured data

---

## 🗃️ Models (Database)

| Model | Tabel | Deskripsi |
|---|---|---|
| `UserModel` | `users` | Data user (nama, email, password_hash, role, google_id) |
| `LandingPageModel` | `landing_pages` | Data undangan (HTML, CSS, JS, slug, status) |
| `TemplateModel` | `templates` | Template undangan (publik & creator) |
| `CategoryModel` | `categories` | Kategori template |
| `OrderModel` | `orders` | Invoice/pesanan pembelian paket |
| `PlanModel` | `plans` | Data paket harga |
| `UserSubscriptionModel` | `user_subscriptions` | Subscription aktif user |
| `GuestBookModel` | `guest_books` | Buku tamu undangan |
| `CreatorProfileModel` | `creator_profiles` | Profil creator (display name, bio, slug) |
| `CreatorApplicationModel` | `creator_applications` | Lamaran menjadi creator |
| `SellerWalletLedgerModel` | `seller_wallet_ledger` | Riwayat transaksi wallet creator |
| `SellerWithdrawRequestModel` | `seller_withdraw_requests` | Request penarikan saldo |
| `MediaModel` | `media` | File gambar yang diupload user |
| `NotificationModel` | `notifications` | Notifikasi dalam aplikasi |
| `EditorAdModel` | `editor_ads` | Iklan yang tampil di dalam editor |
| `PaymentSettingModel` | `payment_settings` | Konfigurasi metode pembayaran |
| `FreePublishEntitlementModel` | `free_publish_entitlements` | Hak publish gratis user |
| `InvitationTemplateUsageModel` | `invitation_template_usages` | Rekam penggunaan template untuk komisi |
| `PasswordResetTokenModel` | `password_reset_tokens` | Token reset password |
| `EmailVerificationTokenModel` | `email_verification_tokens` | Token verifikasi email |

---

## 🛡️ Middleware / Filters

| Filter | Deskripsi |
|---|---|
| `AuthFilter` | Pastikan user sudah login. Jika belum → redirect ke `/login` |
| `GuestFilter` | Pastikan user belum login. Jika sudah → redirect ke `/dashboard` |
| `AdminFilter` | Pastikan user memiliki role `admin`. Jika bukan → 404 |

---

## 📧 Sistem Email

Menggunakan **Brevo API** (bukan SMTP biasa) untuk:
- Email verifikasi akun (link valid 24 jam)
- Email reset password (link valid 1 jam)

Template email ada di `app/Views/emails/`:
- `email_verification.php`
- `password_reset.php`

---

## 🖼️ Fitur Remove Background

Terintegrasi dengan **Poof.bg API** (`POOF_BG_API_KEY`) melalui `EditorMediaController`:
- User bisa upload foto dan otomatis hapus background
- Max file: 5MB, timeout: 45 detik
- Diproses via `editor/media/remove-bg`

---

## 🗺️ Sitemap & IndexNow

| Controller | Fungsi |
|---|---|
| `SitemapController` | Generate `sitemap.xml` otomatis berisi semua halaman undangan yang dipublish + halaman template |
| `AdminIndexNowController` | Submit URL ke **IndexNow** (Bing/Yandex) agar diindex lebih cepat |

---

## 📡 Halaman Publik (`PublicPageController.php`)

Menampilkan undangan yang sudah dipublish di URL `u/{slug}`:
- Render HTML/CSS/JS undangan
- Tampilkan buku tamu
- Form isi buku tamu (nama, pesan, kehadiran: hadir/tidak hadir/ragu)
- Tampilkan stiker ucapan

---

## ⚙️ Konfigurasi Penting (`.env`)

| Key | Fungsi |
|---|---|
| `CI_ENVIRONMENT` | Mode aplikasi: `development` / `production` |
| `app.baseURL` | URL dasar aplikasi |
| `database.*` | Koneksi MySQL |
| `BREVO_API_KEY` | API key untuk kirim email via Brevo |
| `GOOGLE_CLIENT_ID/SECRET` | Kredensial OAuth Google Login |
| `GOOGLE_REDIRECT_URI` | Callback URL setelah login Google |
| `POOF_BG_API_KEY` | API key untuk remove background foto |
| `REMOVE_BG_MAX_BYTES` | Batas ukuran file untuk remove BG (5MB) |

---

## 🗄️ Database Migrations (folder `database/`)

File SQL untuk setup dan alter skema database:

| File | Deskripsi |
|---|---|
| `adaacara_schema.sql` | Skema dasar semua tabel |
| `alter_payment_manual_module.sql` | Tambah tabel orders & payment methods |
| `alter_payment_midtrans_settings.sql` | Tambah konfigurasi Midtrans |
| `alter_seller_template_flow.sql` | Tambah kolom seller/creator di tabel templates |
| `alter_seller_commission_paid_plan.sql` | Tabel komisi seller & wallet ledger |
| `alter_free_publish_entitlements.sql` | Tabel hak publish gratis |
| `alter_editor_ads.sql` | Tabel iklan editor |
| `alter_custom_fonts.sql` | Tabel font kustom |
| `alter_guest_books.sql` | Update schema buku tamu |
| `drop_marketplace_creator_tables_cleanup.sql` | Cleanup tabel lama marketplace |

---

## 🔄 Alur Kerja Lengkap (User Journey)

```
1. DAFTAR AKUN
   Register → Verifikasi Email → Login

2. BUAT UNDANGAN
   Pilih Template → Editor Otomatis Terbuka
   ↳ Desain dengan Fabric.js editor
   ↳ Upload gambar / Remove BG
   ↳ Auto-save otomatis

3. PUBLISH UNDANGAN
   ↳ User Free + Template Free: 1 undangan gratis 1 bulan
   ↳ User Premium: sesuai limit paket
   ↳ Set slug URL (misal: /u/pernikahan-andi-budi)
   ↳ Undangan live dan bisa dibagikan

4. BUKU TAMU
   Tamu isi form di halaman undangan
   Owner lihat daftar tamu di Dashboard

5. BELI PAKET (opsional)
   Pilih paket → Checkout → Transfer/Midtrans
   → Admin approve → Subscription aktif

6. JADI CREATOR (opsional)
   Daftar creator → Buat template → Submit untuk review
   → Admin setujui → Template muncul di galeri
   → User pakai template → Creator dapat komisi 70%
   → Creator request withdraw

7. JADI SELLER (opsional)
   Beli paket premium/business → Akses seller dashboard
   → Kelola leads CRM → Share template ke klien
```
