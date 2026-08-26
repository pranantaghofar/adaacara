<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kode Print Photobooth</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;color:#111827;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:28px 26px 18px;">
                            <p style="margin:0 0 10px;color:#6d28d9;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;">Kode Print Photobooth</p>
                            <h1 style="margin:0;color:#111827;font-size:26px;line-height:1.2;">Halo <?= esc($guestName ?? 'Tamu') ?>,</h1>
                            <p style="margin:14px 0 0;color:#4b5563;font-size:15px;line-height:1.7;">
                                Ini kode akses untuk print/download foto photobooth kamu di <?= esc($pageTitle ?? 'undangan') ?>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 26px 8px;">
                            <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:16px;padding:20px;text-align:center;">
                                <p style="margin:0 0 8px;color:#6d28d9;font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;">Kode akses</p>
                                <p style="margin:0;color:#111827;font-size:34px;font-weight:900;letter-spacing:.08em;"><?= esc($printCode ?? '') ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 26px 28px;">
                            <p style="margin:0 0 18px;color:#4b5563;font-size:14px;line-height:1.7;">
                                Buka galeri memories, pilih foto kamu, lalu masukkan kode ini saat menekan Print / Download.
                            </p>
                            <a href="<?= esc($memoriesUrl ?? '#', 'attr') ?>" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;border-radius:12px;padding:13px 18px;font-size:14px;font-weight:800;">Buka Galeri Memories</a>
                            <p style="margin:22px 0 0;color:#9ca3af;font-size:12px;line-height:1.6;">
                                Abaikan email ini jika kamu tidak mengupload foto photobooth di AdaAcara.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
