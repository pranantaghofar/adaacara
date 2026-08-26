# adaAcara Web

Dokumentasi ringkas proyek adaAcara. File ini menjelaskan arsitektur, fitur utama, alur penting, dan catatan operasional agar developer berikutnya bisa masuk tanpa menebak-nebak.

> Catatan: source code, route, model, SQL di `database/`, dan konfigurasi aktual tetap menjadi sumber kebenaran utama. README ini adalah peta cepat, bukan pengganti audit source.

## Ringkasan Produk

adaAcara adalah aplikasi web berbasis CodeIgniter 4 untuk membuat dan menjual pengalaman digital acara. Awalnya fokus pada undangan digital, lalu berkembang menjadi beberapa produk:

- Undangan Digital: editor Fabric.js, template, publish ke `/u/:slug`, buku tamu, RSVP, musik, gift, countdown, galeri foto, social media, tombol aksi, dan elemen interaktif.
- Digital Photobooth / Guest Memories: frame photobooth, QR, upload kenangan tamu, approval admin, print/download code, custom domain request.
- Business Profile: website profil bisnis berbasis flow editor pages, template kategori bisnis, publish Business Profile, dan entitlement sekali beli.
- Galeri Klien Fotografer: dashboard fotografer tanpa editor, album, upload banyak foto, PIN/private gallery, client selection, favorit, pilihan cetak, share ke keluarga, komentar/revisi, download, dan halaman public family gallery.
- Creator Marketplace: creator application, template submission/review, creator dashboard, earnings, withdraw, dan royalty v1.
- Seller tools: leads CRM, WhatsApp templates, promo assets, template detail, earnings, withdraw.

URL produksi yang biasa dipakai: `https://adaacara.com`

## Stack

- Backend: PHP 8.2+, CodeIgniter 4.7.
- Frontend: PHP server-rendered views, JavaScript, Fabric.js.
- Database: MySQL/MariaDB.
- Assets editor: Fabric.js lokal di `assets/js/fabric.min.js`.
- Payment: manual transfer, Midtrans Snap/webhook, Lynk payment URL/webhook.
- Email: Brevo API/SMTP fallback untuk verifikasi email dan reset password.
- OAuth: Google Login.
- AI/Media: OCR, Magic Layer, Acara AI, remove background via provider seperti Poof.bg/remove.bg/rembg/Gemini/OpenAI-compatible sesuai konfigurasi.
- Deployment: shared hosting/cPanel style, document root ideal di `public/`.

## Struktur Folder

```text
app/
  Config/        Konfigurasi CodeIgniter, routes, filters, database, translations
  Controllers/   Controller publik, dashboard, editor, payment, admin, seller/creator
  Models/        Model table database
  Views/         View PHP untuk public site, editor, dashboard, admin, payment
  Libraries/     Service custom seperti AI, SEO, marketplace, royalty
  Helpers/       Helper global, i18n ringan, icon helper
database/        Schema dasar dan file alter manual
public/          Front controller, public assets, public uploads
assets/          Asset statis/editor yang juga dapat dipakai dari public view
scripts/         Script utilitas
writable/        Cache, logs, sessions, generated artifacts
uploads/         Upload lama/non-public-root, perlu verifikasi path sebelum dipindah
backups/         Zip backup hasil update manual/Codex
vendor/          Dependency Composer
```

## Route Penting

Route utama didefinisikan eksplisit di `app/Config/Routes.php`.

Publik:

- `/` - home.
- `/templates` - gateway template: Undangan Digital, Digital Photobooth, Business Profile.
- `/templates/preview/:id` - preview template.
- `/plans` - paket membership dan produk sekali beli.
- `/fitur/photobooth-digital` - landing page Photobooth Digital.
- `/fitur/galeri-klien-fotografer` - landing page Galeri Klien Fotografer.
- `/fitur/galeri-klien-fotografer/preview` - demo statis, PIN demo `1234`.
- `/creator` - halaman edukasi Creator Marketplace.
- `/u/:slug` - published invitation / public business profile fallback URL.
- `/u/:slug/guestbook` - POST buku tamu public.
- `/gallery/:slug` - public client gallery fotografer.
- `/gallery/:slug/family/:token` - halaman keluarga untuk foto yang dibagikan.
- `/rsvp/:token` - customer RSVP/guestbook access.
- `/terms`, `/privacy`, `/cookies`, `/sitemap.xml`.

Authenticated:

- `/dashboard`
- `/editor/:id`
- `/preview/:id`
- `/checkout/:slug`
- `/orders`
- `/business-profile/:landingPageId/checkout`
- `/photographer-galleries`
- `/photographer-galleries/create`
- `/photographer-galleries/:id`
- `/seller/*`
- `/creator/apply`, `/creator/dashboard`, `/creator/templates`, `/creator/earnings`

Admin:

- `/admin`
- `/admin/users`
- `/admin/orders`
- `/admin/payment-settings`
- `/admin/editor-ai-settings`
- `/admin/pages`
- `/admin/guestbooks`
- `/admin/guest-memories`
- `/admin/templates`
- `/admin/template-subcategories`
- `/admin/seller-templates`
- `/admin/creator-applications`
- `/admin/creator-royalties`
- `/admin/photobooth-domains`
- `/admin/business-profile-orders`
- `/admin/publish-requests`
- `/admin/custom-fonts`
- `/admin/indexnow`

## Modul Utama

### Public Site, SEO, Header, Footer

Implementasi utama:

- `app/Controllers/Home.php`
- `app/Controllers/SeoLandingController.php`
- `app/Controllers/GuideController.php`
- `app/Controllers/LegalController.php`
- `app/Views/home*.php`
- `app/Views/seo/*`
- `app/Views/components/public_site_header.php`
- `app/Views/components/site_footer.php`

Public header/footer memuat menu produk untuk Undangan Digital, Digital Photobooth, Business Profile, Galeri Klien Fotografer, Creator, dan halaman bantuan/legal. Theme light/dark punya override di beberapa view public.

### Authentication

Implementasi:

- `app/Controllers/AuthController.php`
- `app/Models/UserModel.php`
- `app/Models/PasswordResetTokenModel.php`
- `app/Models/EmailVerificationTokenModel.php`

Fitur:

- Register, login, logout.
- Google OAuth.
- Email verification.
- Forgot/reset password.
- Token selector/validator, validator disimpan hash SHA-256.
- Rate limit pengiriman reset password.
- Brevo API/SMTP fallback.

### Templates dan Creation Flow

Implementasi:

- `app/Controllers/TemplateController.php`
- `app/Controllers/AdminTemplateController.php`
- `app/Controllers/AdminTemplateSubcategoryController.php`
- `app/Models/TemplateModel.php`
- `app/Models/CategoryModel.php`
- `app/Models/TemplateSubcategoryModel.php`

Catatan penting:

- `templates.project_type` membedakan `invitation`, `photobooth`, dan `business_profile`.
- Undangan Digital tetap memakai `categories` dan `template_subcategories`.
- Business Profile memakai kategori bisnis via `templates.tags`: `MUA`, `Wedding Organizer`, `Dekorasi`, `Venue`, `Catering`, `Photographer`, `Freelancer`, `UMKM`, `Agency`.
- Photobooth dan Business Profile menghindari fallback ke kategori undangan.
- Admin template create/edit sudah memisahkan tiga project type.
- Save as Template dari editor menyimpan project type sesuai project aktif.
- Built-in MUA template dapat dibuat lewat command `php spark templates:seed-business-mua`.

SQL terkait:

- `database/alter_business_profile_project_type.sql`
- `database/alter_templates_tags.sql`

### Fabric Editor

Implementasi:

- `app/Controllers/EditorController.php`
- `app/Views/editor/index.php`
- `app/Views/editor/partials/*`
- `assets/js/fabric.min.js`

Fitur penting:

- Editor pages berbasis Fabric.js.
- Save, autosave, preview, publish, unpublish.
- Upload asset, media library, inline base64 image extraction.
- Thumbnail dashboard.
- OCR, Magic Layer OCR, Acara AI.
- Remove background.
- Custom fonts.
- Elements interaktif: music, countdown, YouTube, gallery, social media, guestbook, sticker, link/copy text, scroll-next, zoomable photo, dan elemen Business Profile.
- Publish modal memilih surface: Undangan, Photobooth frame, atau Business Profile sesuai project.
- Photobooth intent menjaga editor hanya menampilkan surface Photobooth, kecuali legacy hybrid project.
- Business Profile intent memakai engine pages yang sama dengan undangan, tetapi UI tab/left rail dibatasi agar tidak membingungkan.

Hal yang sensitif:

- Jangan mengubah struktur `editor_json`, `published_editor_json`, `grapesjs_json`, atau `published_*` tanpa audit.
- Renderer public harus tetap kompatibel dengan JSON lama.
- Object Fabric tertentu punya kontrak `customType`; jangan rename sembarangan.

### Public Invitation Renderer dan Guestbook

Implementasi:

- `app/Controllers/PublicPageController.php`
- `app/Views/public/render.php`
- `app/Models/GuestBookModel.php`
- `app/Controllers/GuestbookAccessController.php`
- `app/Models/GuestbookAccessLinkModel.php`

Catatan:

- URL public utama tetap `/u/:slug`.
- Renderer memprioritaskan `published_editor_json`, lalu fallback ke `editor_json` / `grapesjs_json`, dan legacy HTML/CSS/JS.
- Renderer mendukung opening gate, music autoplay setelah klik user, social media, scroll-next, countdown, photo gallery, YouTube, link/copy hotspot, zoomable photo, GIF overlay/background, image stroke/effect, object animation, guestbook DOM controls, dan guest-name replacement.
- Business Profile public dapat skip opening gate.
- Auto reload/cache-bust pernah dipakai sebagai workaround font/text agar akses awal lebih selaras dengan editor. Jangan hapus tanpa audit visual.
- Lenis smooth scroll global pernah dihapus dari `render.php` karena berat di mobile; jangan aktifkan ulang tanpa alasan kuat.
- Guestbook POST ke `/u/:slug/guestbook` memakai CSRF dan cache komentar di `writable/comments`.

### Guest Memories / Digital Photobooth

Implementasi:

- `app/Controllers/GuestMemoryController.php`
- `app/Controllers/AdminGuestMemoryController.php`
- `app/Models/GuestMemoryModel.php`
- `app/Models/PhotoboothCustomDomainModel.php`
- `app/Models/PhotoboothCustomDomainOrderModel.php`
- `app/Views/guest_memory/*`
- `assets/guest-memory/*`

Fitur:

- Guest upload memory dari QR.
- Frame photobooth dari editor.
- Admin approve/reject/hide/delete.
- Per-user Guest Memories enablement.
- Optional wish text.
- Print/download code per memory.
- Guest email untuk kirim kode.
- Public gallery auto-refresh ringan saat panel terbuka.
- Custom domain request untuk photobooth dengan workflow semi-manual admin.

SQL terkait:

- `database/alter_guest_memories.sql`
- `database/alter_guest_memories_wish_text.sql`
- `database/alter_photobooth_custom_domains.sql`
- `database/alter_photobooth_custom_domain_payments.sql`
- `database/alter_photobooth_custom_domain_orders.sql`

### Business Profile

Business Profile adalah website profil bisnis yang menggunakan engine pages editor, tetapi product/UX dipisah dari undangan.

Fitur:

- Product gateway di `/templates`.
- Blank create flow dengan `project_intent=business_profile`.
- Project disimpan sebagai `landing_pages.project_type=business_profile` jika kolom tersedia.
- Editor menampilkan tab tunggal Business Profile sebagai alias pages mode.
- Left drawer dibatasi agar tidak memunculkan fitur undangan yang tidak relevan.
- Elements berdasarkan kategori bisnis.
- Publish Business Profile aktif.
- Pembelian dapat lewat checkout per website atau product entitlement di `/plans`.

SQL terkait:

- `database/alter_business_profile_project_type.sql`
- `database/alter_business_profile_website_entitlements.sql`
- `database/alter_product_entitlements.sql`

### Photographer Gallery / Galeri Klien Fotografer

Implementasi:

- `app/Controllers/PhotographerGalleryController.php`
- `app/Controllers/PhotographerGalleryPublicController.php`
- `app/Models/PhotographerGalleryModel.php`
- `app/Models/PhotographerGalleryAlbumModel.php`
- `app/Models/PhotographerGalleryPhotoModel.php`
- `app/Models/PhotographerGallerySelectionModel.php`
- `app/Models/PhotographerGalleryCommentModel.php`
- `app/Views/photographer_galleries/*`

Flow:

1. Fotografer membuat project gallery.
2. Mengatur nama project, tanggal, cover, studio, privacy, PIN 4 digit, limit pilihan foto, dan download.
3. Membuat album bebas, dengan quick add seperti Highlight, Ceremony, Reception, Family.
4. Upload foto per file/batch.
5. Client membuka `/gallery/:slug`, memasukkan PIN jika protected.
6. Client bisa favorit, pilih untuk dicetak, pilih untuk disebar, komentar/revisi foto, download bila diizinkan.
7. Fotografer melihat pilihan cetak dan komentar/revisi di dashboard.
8. Share ke keluarga membuat/memperbarui halaman `/gallery/:slug/family/:token`.

Catatan:

- Foto `hidden` dan `deleted` tidak tampil ke client.
- Delete foto tahap awal adalah soft-delete status `deleted`, file tidak dihapus fisik.
- Tab album public hanya idealnya muncul jika album punya foto visible.
- Selection print/share memakai `photographer_gallery_selections.selection_type`.
- Share keluarga memakai token gallery-level `__gallery_family__` untuk satu link utama per gallery, dengan fallback legacy token.
- AI image search direncanakan sebagai layer terpisah, dimulai dari caption/tag indexing, bukan langsung vector search.

SQL terkait:

- `database/alter_photographer_galleries.sql`

### Payments, Plans, dan Product Entitlements

Implementasi:

- `app/Controllers/PaymentController.php`
- `app/Controllers/AdminController.php`
- `app/Models/OrderModel.php`
- `app/Models/PlanModel.php`
- `app/Models/UserSubscriptionModel.php`
- `app/Models/ProductEntitlementModel.php`
- `app/Models/PaymentSettingModel.php`

Payment mode:

- Manual transfer.
- Midtrans Snap dan webhook notification.
- Lynk payment URL dan webhook.

Jenis plan/product:

- `membership`: paket subscription yang mengaktifkan `user_subscriptions`.
- `creator`: aktivasi role/flow creator.
- `business_profile`: sekali beli 1 website Business Profile.
- `photobooth_standalone`: Digital Photobooth standalone 1 tahun.
- `photographer_gallery`: Galeri Klien Fotografer sekali beli/lifetime.

Catatan:

- Non-membership product tidak boleh membuka fitur membership undangan.
- Universal activation branch mengaktifkan subscription atau product entitlement berdasarkan `plans.product_type`.
- Business Profile entitlement dari `/plans` bisa dikonsumsi sebagai credit publish satu website.
- Direct Business Profile checkout lama lewat `/business-profile/:landingPageId/checkout` tetap kompatibel.

SQL terkait:

- `database/alter_product_entitlements.sql`
- `database/alter_plans_lynk_payment_url.sql`
- `database/alter_payment_midtrans_settings.sql`
- `database/alter_payment_manual_module.sql`

### Seller, Creator, Marketplace, dan Royalty

Implementasi:

- `app/Controllers/SellerTemplateController.php`
- `app/Controllers/CreatorController.php`
- `app/Controllers/AdminSellerController.php`
- `app/Controllers/AdminMarketplaceController.php`
- `app/Libraries/SellerTemplateService.php`
- `app/Libraries/MarketplaceReviewService.php`
- `app/Libraries/CreatorRoyaltyService.php`

Fitur:

- Seller dashboard, leads CRM, WhatsApp templates, promo assets.
- Creator application dan profile.
- Creator dashboard, templates, earnings, withdraw.
- Template submission/review/archive/resubmit.
- Marketplace activity and notification.
- Creator Royalty v1: target model 90% creator / 10% platform dari nilai template/license.

Catatan:

- Legacy commission flow masih ada selama transisi.
- Royalty refund/reversal dan cutover final masih perlu QA.

SQL terkait:

- `database/alter_creator_marketplace_stage1.sql`
- `database/alter_creator_royalty_model.sql`
- `database/alter_seller_template_flow.sql`
- `database/alter_seller_commission_paid_plan.sql`

### Media, Asset Library, dan AI

Implementasi:

- `app/Controllers/EditorMediaController.php`
- `app/Controllers/EditorAssetController.php`
- `app/Controllers/AdminEditorAiSettingsController.php`
- `app/Libraries/*AI*`
- `scripts/generate_editor_asset_library.php`

Fitur:

- Upload media editor.
- Soft delete media.
- Asset library dengan tags/metadata.
- SVG safety checks.
- Magic Layer temp upload/delete.
- OCR text.
- Remove background.
- AI provider settings/test di admin.

### Admin Panel

Admin mengelola:

- Users dan roles.
- Orders, plans, payment settings.
- Pages/project type.
- Templates dan subcategories.
- Guestbooks.
- Guest Memories.
- Photobooth domain requests.
- Business Profile orders.
- Publish requests/domain aliases.
- Creator applications.
- Seller templates/review.
- Creator royalty QA.
- Withdraw requests.
- Editor ads.
- Custom fonts.
- Legal documents.
- IndexNow.
- Editor AI settings.

## Database dan Schema Contract

Proyek ini memakai SQL manual di `database/`, bukan satu sistem migration CI4 yang lengkap. Schema aktual biasanya terdiri dari:

- `database/adaacara_schema.sql` sebagai fondasi.
- Banyak `database/alter_*.sql` untuk fitur yang ditambahkan kemudian.

Sebelum deploy fitur schema-sensitive, cek apakah table/column sudah ada di production.

File SQL penting yang sering relevan:

- `alter_business_profile_project_type.sql`
- `alter_business_profile_website_entitlements.sql`
- `alter_product_entitlements.sql`
- `alter_templates_tags.sql`
- `alter_guest_memories.sql`
- `alter_guest_memories_wish_text.sql`
- `alter_photographer_galleries.sql`
- `alter_published_domains.sql`
- `alter_published_domains_publish_requests.sql`
- `alter_photobooth_custom_domains.sql`
- `alter_photobooth_custom_domain_payments.sql`
- `alter_photobooth_custom_domain_orders.sql`
- `alter_creator_marketplace_stage1.sql`
- `alter_creator_royalty_model.sql`
- `alter_custom_fonts.sql`
- `alter_editor_ads.sql`
- `alter_guestbook_access_links.sql`
- `alter_plans_lynk_payment_url.sql`

## Environment Variables

Local `.env` pada beberapa copy project bisa kosong. Jangan menganggap nilai lokal sama dengan production.

Key yang umum:

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

Payment settings utama sebagian disimpan di database table `payment_settings`, bukan hanya `.env`.

## Deployment Notes

- Preferred document root: `public/`.
- Clean URL aktif, `Config\App::$indexPage` kosong.
- `.htaccess` penting untuk rewrite dan akses asset/upload.
- CORS untuk assets/upload perlu tetap mendukung `adaacara.com` dan subdomainnya agar Fabric render bisa memuat image/font lintas host.
- Jangan memindahkan `uploads/`, `public/uploads/`, atau asset path tanpa audit payload database.
- DB-client/temp public_html copy bisa berbeda dari workspace; bandingkan file spesifik sebelum overwrite.

## Compatibility Rules

Jangan sembarang mengubah:

- Public URL `/u/:slug`.
- `landing_pages.editor_json`, `grapesjs_json`, `published_editor_json`, `published_html`, `published_css`, `published_js`.
- `templates.editor_json`, `templates.project_type`, `templates.tags`.
- Payment status dan provider fields Midtrans/Lynk.
- Product entitlement vs membership activation.
- Guest Memory file/code behavior.
- Photographer Gallery selection tables.
- Custom Fabric `customType` contract.
- Public renderer opening/music behavior.

## Verifikasi Dasar

Perintah yang biasa dipakai:

```bash
composer install
php spark --version
php -l app/Views/public/render.php
php -l app/Controllers/PaymentController.php
php spark routes
```

Jika ada perubahan view besar, lakukan browser QA manual untuk:

- `/`
- `/templates`
- `/plans`
- `/u/:slug`
- `/preview/:id`
- `/fitur/photobooth-digital`
- `/fitur/galeri-klien-fotografer`
- `/photographer-galleries`
- `/gallery/:slug`

## Alur User Utama

Undangan Digital:

```text
Register/Login
-> pilih template / blank
-> editor
-> save/publish
-> public /u/:slug
-> tamu buka, isi guestbook/RSVP
-> owner cek dashboard
```

Business Profile:

```text
Pilih Business Profile dari /templates atau blank
-> editor pages dengan tab Business Profile
-> publish Business Profile
-> jika butuh pembayaran, pakai entitlement/checkout
-> public tetap punya fallback /u/:slug dan optional subdomain alias
```

Digital Photobooth:

```text
Pilih/create Photobooth
-> editor surface Photobooth/frame
-> publish QR/frame jika akses aktif
-> guest upload memory
-> admin/user review
-> gallery/print/download
```

Galeri Klien Fotografer:

```text
Create Photographer Gallery
-> set project, privacy/PIN, selection limit, download
-> buat album
-> upload foto
-> client buka /gallery/:slug
-> client favorit/cetak/sebar/comment
-> fotografer review pilihan dan komentar
-> client/family download sesuai izin/PIN
```

Creator:

```text
Apply creator
-> admin review
-> creator buat/save template
-> admin/marketplace review
-> user pakai template
-> royalty/earnings/withdraw
```

## Known Issues / Perlu Audit

- Folder copy ini bisa bukan Git repository; `git status` mungkin gagal.
- Local `.env` bisa kosong.
- Schema production harus diverifikasi dari semua alter SQL.
- Test coverage masih terbatas.
- Public renderer adalah area sensitif karena harus menjaga parity editor, performance mobile, opening/music, fonts, GIF, dan guestbook.
- Auto reload/cache-bust di public renderer pernah dipakai sebagai workaround font/text; jangan hapus tanpa pengganti yang teruji.
- Lenis smooth scroll global sebaiknya tidak diaktifkan di public renderer karena bisa berat pada mobile/Fabric pages.
