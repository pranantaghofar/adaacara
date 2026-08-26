<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reset password AdaAcara</title>
</head>
<body style="margin:0;background:#f3faf7;color:#0f172a;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3faf7;padding:32px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #dbece7;">
                    <tr>
                        <td style="padding:28px 28px 10px;">
                            <h1 style="margin:0;color:#0f172a;font-size:24px;line-height:1.25;">Reset password AdaAcara</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 8px;color:#475569;font-size:15px;line-height:1.7;">
                            Halo <?= esc($name) ?>,<br>
                            kami menerima permintaan untuk mengganti password akun AdaAcara kamu.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;">
                            <a href="<?= esc($resetUrl, 'attr') ?>" style="display:inline-block;background:#07825f;color:#ffffff;text-decoration:none;border-radius:14px;padding:14px 20px;font-weight:700;">Buat password baru</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 20px;color:#64748b;font-size:13px;line-height:1.7;">
                            Link ini berlaku 60 menit dan hanya bisa digunakan satu kali. Jika kamu tidak meminta reset password, abaikan email ini.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px;border-top:1px solid #edf5f2;color:#94a3b8;font-size:12px;line-height:1.6;">
                            Jika tombol tidak bisa diklik, buka link berikut:<br>
                            <span style="word-break:break-all;color:#64748b;"><?= esc($resetUrl) ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
