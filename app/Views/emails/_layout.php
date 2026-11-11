<?php /** Table-based e-mail shell. Expects $subject and $content (HTML). */ ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($subject) ?></title></head>
<body style="margin:0;background:#f3ecdd;font-family:Arial,Helvetica,sans-serif;color:#1e241f;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3ecdd;padding:28px 12px;"><tr><td align="center">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fffdf8;border-radius:8px;overflow:hidden;">
        <tr><td style="background:#1f3a2b;padding:22px 32px;color:#fbf8f1;font-family:Georgia,serif;font-size:24px;"><?= esc(setting('site_name', 'Sugarlif')) ?></td></tr>
        <tr><td style="padding:32px;font-size:15px;line-height:1.6;"><?= $content ?></td></tr>
        <tr><td style="padding:20px 32px;background:#fbf8f1;border-top:1px solid #ddd6c6;font-size:12px;color:#5e6a60;line-height:1.6;">
            <?= esc(setting('company_name')) ?> · <?= esc(setting('address_line1')) ?>, <?= esc(setting('address_city')) ?> <?= esc(setting('address_postcode')) ?><br>
            <?= esc(setting('contact_phone')) ?> · <?= esc(setting('contact_email_primary')) ?>
        </td></tr>
    </table>
</td></tr></table>
</body></html>
