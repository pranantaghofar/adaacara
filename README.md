# adaAcara Web

> Platform untuk membuat pengalaman digital acara: undangan, photobooth, business profile, galeri klien fotografer, template creator, dan tools pendukungnya.

---

## Snapshot

| Area | Status Singkat |
|---|---|
| Framework | CodeIgniter 4.7 |
| Bahasa | PHP 8.2+, JavaScript |
| Editor | Fabric.js |
| Database | MySQL / MariaDB |
| Payment | Manual Transfer, Midtrans, Lynk |
| Email | Brevo API / SMTP fallback |
| Auth | Email + Google OAuth |
| Hosting | Shared hosting / cPanel friendly, document root `public/` |
| Public URL utama | `/u/:slug` |

Production URL yang biasa dipakai: `https://adaacara.com`

---

## Produk Utama

### 1. Undangan Digital

Flow utama adaAcara: pilih template, edit desain, publish, lalu bagikan link public.

Fitur yang sudah ada:

- Editor Fabric.js drag-and-drop.
- Template undangan dan kategori/subkategori.
- Publish ke `/u/:slug`.
- Opening cover, music, countdown, maps, gift, gallery, social media, tombol aksi.
- Guestbook / buku tamu.
- RSVP/customer guestbook access via token.
- Public renderer untuk membaca hasil publish dari editor.

### 2. Digital Photobooth / Guest Memories

Produk untuk tamu upload foto/momen dari QR.

Fitur:

- Frame photobooth dari editor.
- QR photobooth.
- Upload memory tamu.
- Approval/reject/hide/delete dari admin.
- Print/download code.
- Optional guest wish text.
- Optional guest email untuk kirim kode.
- Custom domain request untuk photobooth.

### 3. Business Profile

Website profil bisnis berbasis engine editor pages, tetapi UX-nya dipisah dari undangan.

Fitur:

- Project type `business_profile`.
- Blank/template flow dari `/templates`.
- Kategori bisnis: `MUA`, `Wedding Organizer`, `Dekorasi`, `Venue`, `Catering`, `Photographer`, `Freelancer`, `UMKM`, `Agency`.
- Editor menampilkan tab Business Profile.
- Left drawer dibatasi agar fitur undangan yang tidak relevan tidak mengganggu.
- Publish Business Profile.
- Pembelian bisa lewat entitlement produk atau checkout per website.

### 4. Galeri Klien Fotografer

Tools untuk fotografer mengirim hasil foto ke klien tanpa masuk editor.

Flow:

```text
Buat Gallery
-> atur project, tanggal, cover, studio, privacy/PIN
-> buat album
-> upload foto
-> client pilih favorit/cetak/sebar/comment
-> fotografer review pilihan dan revisi
-> keluarga bisa buka halaman share khusus
```

Fitur:

- Dashboard fotografer.
- Album bebas + quick add seperti Highlight, Ceremony, Reception, Family.
- Public client gallery di `/gallery/:slug`.
- PIN 4 digit untuk private gallery.
- Client selection untuk cetak.
- Share selection untuk keluarga.
- Favorite lokal di browser.
- Komentar/revisi per foto.
- Download sesuai izin.
- Family gallery di `/gallery/:slug/family/:token`.

### 5. Creator Marketplace

Ekosistem untuk creator template.

Fitur:

- Creator application.
- Creator profile.
- Submit template dari editor.
- Admin review / approve / reject.
- Creator dashboard.
- Earnings dan withdraw.
- Creator Royalty v1, target model 90% creator / 10% platform dari nilai template/license.

### 6. Seller Tools

Tools pendukung untuk seller.

Fitur:

- Seller dashboard.
- Leads CRM.
- WhatsApp templates.
- Promo assets.
- Template detail.
- Earnings dan withdraw.

---

## Struktur Folder

```text
app/
  Config/        Routes, filters, database, app config, translations
  Controllers/   Public, dashboard, editor, payment, admin, seller, creator
  Models/        Model table database
  Views/         PHP views untuk public site, editor, dashboard, admin
  Libraries/     Service custom: AI, SEO, marketplace, royalty
  Helpers/       Helper global, i18n ringan, icon helper

database/        Schema dasar + alter SQL manual
public/          Front controller, public assets, public uploads
assets/          Asset editor/public tambahan
scripts/         Script utilitas
writable/        Cache, logs, sessions, generated files
uploads/         Upload lama/non-public-root
backups/         Zip backup hasil update
vendor/          Composer dependencies
```

---

## Route Map

### Public

| Route | Fungsi |
|---|---|
| `/` | Home |
| `/templates` | Gateway template dan product picker |
| `/templates/preview/:id` | Preview template |
| `/plans` | Paket membership dan produk sekali beli |
| `/fitur/photobooth-digital` | Landing page photobooth |
| `/fitur/galeri-klien-fotografer` | Landing page galeri fotografer |
| `/fitur/galeri-klien-fotografer/preview` | Demo gallery, PIN `1234` |
| `/creator` | Edukasi Creator Marketplace |
| `/u/:slug` | Public invitation / business profile fallback |
| `/u/:slug/guestbook` | POST guestbook public |
| `/gallery/:slug` | Client gallery fotografer |
| `/gallery/:slug/family/:token` | Family gallery |
| `/rsvp/:token` | Customer RSVP/guestbook access |
| `/terms`, `/privacy`, `/cookies` | Legal pages |
| `/sitemap.xml` | Sitemap |

### Login Required

| Route | Fungsi |
|---|---|
| `/dashboard` | Dashboard user |
| `/editor/:id` | Editor utama |
| `/preview/:id` | Preview project |
| `/checkout/:slug` | Checkout plan/product |
| `/orders` | Riwayat order |
| `/business-profile/:landingPageId/checkout` | Checkout Business Profile per website |
| `/photographer-galleries` | Dashboard gallery fotografer |
| `/photographer-galleries/create` | Buat gallery baru |
| `/photographer-galleries/:id` | Detail/upload/manage gallery |
| `/seller/*` | Seller dashboard |
| `/creator/apply` | Daftar creator |
| `/creator/dashboard` | Dashboard creator |
| `/creator/templates` | Template creator |
| `/creator/earnings` | Earnings creator |

### Admin

| Route | Fungsi |
|---|---|
| `/admin` | Dashboard admin |
| `/admin/users` | User & roles |
| `/admin/orders` | Orders & plans |
| `/admin/payment-settings` | Manual/Midtrans/Lynk settings |
| `/admin/editor-ai-settings` | AI provider settings |
| `/admin/pages` | Landing pages/project type |
| `/admin/templates` | Template management |
| `/admin/template-subcategories` | Subkategori undangan |
| `/admin/guestbooks` | Data guestbook |
| `/admin/guest-memories` | Moderasi photobooth memories |
| `/admin/photobooth-domains` | Request domain photobooth |
| `/admin/business-profile-orders` | Order Business Profile |
| `/admin/publish-requests` | Subdomain/domain publish requests |
| `/admin/seller-templates` | Review template seller/creator |
| `/admin/creator-applications` | Review creator application |
| `/admin/creator-royalties` | QA creator royalty |
| `/admin/custom-fonts` | Font kustom |
| `/admin/indexnow` | Submit IndexNow |

---

## Modul dan File Penting

### Public Site

| File | Peran |
|---|---|
| `app/Controllers/Home.php` | Home dan wildcard host render |
| `app/Controllers/SeoLandingController.php` | Landing page fitur/produk |
| `app/Views/home*.php` | Tampilan home |
| `app/Views/seo/*` | Landing page SEO |
| `app/Views/components/public_site_header.php` | Header public |
| `app/Views/components/site_footer.php` | Footer public |

### Auth

| File | Peran |
|---|---|
| `app/Controllers/AuthController.php` | Register, login, OAuth, reset password |
| `app/Models/UserModel.php` | User |
| `app/Models/PasswordResetTokenModel.php` | Reset token |
| `app/Models/EmailVerificationTokenModel.php` | Verification token |

### Template

| File | Peran |
|---|---|
| `app/Controllers/TemplateController.php` | Public template flow |
| `app/Controllers/AdminTemplateController.php` | Admin template CRUD |
| `app/Controllers/AdminTemplateSubcategoryController.php` | Subcategory admin |
| `app/Models/TemplateModel.php` | Template model |
| `app/Commands/SeedBusinessProfileMuaTemplate.php` | Seed template MUA |

### Editor

| File | Peran |
|---|---|
| `app/Controllers/EditorController.php` | Edit, save, publish, preview |
| `app/Views/editor/index.php` | Shell editor |
| `app/Views/editor/partials/*` | UI, script, drawer, interactions |
| `assets/js/fabric.min.js` | Fabric.js |

### Public Renderer

| File | Peran |
|---|---|
| `app/Controllers/PublicPageController.php` | Render `/u/:slug`, guestbook submit |
| `app/Views/public/render.php` | Renderer public invitation/business profile |
| `app/Models/GuestBookModel.php` | Guestbook |
| `app/Controllers/GuestbookAccessController.php` | RSVP token view |

### Photographer Gallery

| File | Peran |
|---|---|
| `app/Controllers/PhotographerGalleryController.php` | Dashboard fotografer |
| `app/Controllers/PhotographerGalleryPublicController.php` | Client/family gallery |
| `app/Models/PhotographerGallery*Model.php` | Gallery, album, photo, selection, comment |
| `app/Views/photographer_galleries/*` | UI gallery |

### Payment

| File | Peran |
|---|---|
| `app/Controllers/PaymentController.php` | Plans, checkout, orders, Midtrans/Lynk webhook |
| `app/Controllers/BusinessProfileOrderController.php` | Checkout Business Profile per website |
| `app/Models/OrderModel.php` | Orders |
| `app/Models/PlanModel.php` | Plans |
| `app/Models/ProductEntitlementModel.php` | Non-membership entitlements |
| `app/Models/UserSubscriptionModel.php` | Membership subscription |

---

## Kontrak Data yang Sensitif

Jangan ubah sembarangan tanpa audit:

- `/u/:slug` sebagai public URL utama.
- `landing_pages.editor_json`
- `landing_pages.grapesjs_json`
- `landing_pages.published_editor_json`
- `landing_pages.published_html`
- `landing_pages.published_css`
- `landing_pages.published_js`
- `templates.editor_json`
- `templates.project_type`
- `templates.tags`
- Payment status dan provider fields Midtrans/Lynk.
- Product entitlement vs membership subscription.
- `photographer_gallery_selections.selection_type`
- Fabric object `customType`.
- Public renderer opening gate, music autoplay, font workaround, GIF layer, guestbook layer.

---

## Product Types di Plans

`plans.product_type` membedakan cara aktivasi setelah order paid.

| product_type | Aktivasi |
|---|---|
| `membership` | Masuk ke `user_subscriptions` |
| `creator` | Aktivasi creator flow |
| `business_profile` | Entitlement 1 website Business Profile |
| `photobooth_standalone` | Akses Digital Photobooth standalone 1 tahun |
| `photographer_gallery` | Akses Galeri Klien Fotografer |

File SQL:

- `database/alter_product_entitlements.sql`
- `database/alter_plans_lynk_payment_url.sql`

---

## SQL Manual yang Sering Dibutuhkan

Proyek ini tidak memakai satu migration system yang lengkap. Biasanya production perlu `adaacara_schema.sql` plus alter SQL berikut sesuai fitur:

| SQL | Untuk |
|---|---|
| `alter_business_profile_project_type.sql` | `project_type` untuk page/template |
| `alter_business_profile_website_entitlements.sql` | Checkout Business Profile per website |
| `alter_product_entitlements.sql` | Produk non-membership |
| `alter_templates_tags.sql` | Tags kategori Business Profile/Photobooth |
| `alter_guest_memories.sql` | Guest Memories |
| `alter_guest_memories_wish_text.sql` | Wish text photobooth |
| `alter_photographer_galleries.sql` | Galeri Klien Fotografer |
| `alter_published_domains.sql` | Subdomain/domain publish |
| `alter_published_domains_publish_requests.sql` | Admin publish requests |
| `alter_photobooth_custom_domains.sql` | Custom domain photobooth |
| `alter_photobooth_custom_domain_payments.sql` | Payment proof domain |
| `alter_photobooth_custom_domain_orders.sql` | Order domain photobooth |
| `alter_creator_marketplace_stage1.sql` | Creator marketplace |
| `alter_creator_royalty_model.sql` | Royalty v1 |
| `alter_guestbook_access_links.sql` | RSVP/customer guestbook token |

---

## Environment

Local `.env` pada copy project bisa kosong. Jangan anggap environment lokal sama dengan production.

Key yang umum dicek:

```text
CI_ENVIRONMENT
app.baseURL
database.default.hostname
database.default.database
database.default.username
database.default.password
BREVO_API_KEY
MAIL_FROM_EMAIL
MAIL_FROM_NAME
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URI
POOF_BG_API_KEY
REMOVE_BG_API_KEY
GEMINI_API_KEY
OPENAI_API_KEY
```

Catatan payment:

- Midtrans/Lynk sebagian besar dikontrol dari table `payment_settings`.
- Plan-specific Lynk URL bisa memakai kolom `plans.lynk_payment_url` jika SQL sudah diterapkan.

---

## Public Renderer Notes

`app/Views/public/render.php` adalah area paling sensitif karena harus membaca hasil editor dan tetap ringan di mobile.

Yang sudah/perlu dijaga:

- Render harus tetap kompatibel dengan payload lama.
- Opening button harus menjaga music autoplay tetap berasal dari user gesture.
- Font workaround/cache-bust jangan dihapus tanpa pengganti yang sudah diuji.
- Lenis smooth scroll global sebaiknya tidak diaktifkan lagi di renderer public.
- GIF overlay/background harus tidak menutup semua layer interaktif.
- Guestbook harus tetap DOM-interactive, bukan canvas mati.
- Object animation perlu diaudit jika halaman terasa berat di HP.

---

## Deployment Checklist

Sebelum upload:

- Pastikan document root mengarah ke `public/`.
- Pastikan `.htaccess` root dan `public/.htaccess` masih benar.
- Pastikan `writable/` bisa ditulis server.
- Pastikan `uploads/` dan `public/uploads/` tidak tertukar path.
- Pastikan CORS asset/upload tetap mendukung `adaacara.com` dan subdomainnya.
- Pastikan SQL alter terkait fitur sudah diterapkan di production.
- Pastikan payment settings production tidak tertimpa config lokal.

---

## Quick Verification

Perintah dasar:

```bash
composer install
php spark --version
php spark routes
php -l app/Views/public/render.php
php -l app/Controllers/PaymentController.php
```

QA browser yang disarankan:

- `/`
- `/templates`
- `/plans`
- `/u/:slug`
- `/preview/:id`
- `/fitur/photobooth-digital`
- `/fitur/galeri-klien-fotografer`
- `/photographer-galleries`
- `/gallery/:slug`

---

## Alur User

### Undangan Digital

```text
Register/Login
-> pilih template atau blank
-> edit di Fabric editor
-> publish
-> share /u/:slug
-> tamu isi guestbook/RSVP
-> owner cek dashboard
```

### Business Profile

```text
Pilih Business Profile
-> pilih kategori/template atau blank
-> edit halaman seperti pages editor
-> publish Business Profile
-> entitlement/payment bila dibutuhkan
-> share /u/:slug atau subdomain aktif
```

### Digital Photobooth

```text
Buat/Pilih project Photobooth
-> desain frame
-> publish QR/frame
-> tamu upload memory
-> admin/user review
-> print/download
```

### Galeri Klien Fotografer

```text
Buat gallery
-> set PIN/privacy dan album
-> upload foto
-> client pilih cetak/sebar/favorit/comment
-> fotografer review
-> keluarga buka link share
```

### Creator

```text
Apply creator
-> admin approve
-> creator submit template
-> admin review
-> template dipakai user
-> royalty/earnings/withdraw
```

---

## Hal yang Perlu Diingat

- Folder copy bisa bukan Git repository.
- DB-client/temp `public_html` bisa berbeda dari workspace ini.
- Local `.env` bisa kosong.
- Schema production wajib dicek dari semua file SQL terkait.
- Test coverage masih terbatas.
- Public renderer dan editor adalah bagian paling rawan regresi.
- Untuk update besar, buat zip backup di `backups/`.

