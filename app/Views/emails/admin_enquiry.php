<?php ob_start(); ?>
<h1 style="font-family:Georgia,serif;font-weight:normal;font-size:24px;margin:0 0 12px;color:#142a1e;">New website enquiry</h1>
<p><strong><?= esc(trim($enquiry['first_name'] . ' ' . $enquiry['last_name'])) ?></strong><br><?= esc($enquiry['email']) ?><?= $enquiry['phone'] ? ' · ' . esc($enquiry['phone']) : '' ?></p>
<?php if ($enquiry['subject']): ?><p><strong>Subject:</strong> <?= esc($enquiry['subject']) ?></p><?php endif ?>
<p style="white-space:pre-line;background:#fbf8f1;border:1px solid #ddd6c6;padding:14px;border-radius:6px;"><?= esc($enquiry['message']) ?></p>
<p><a href="<?= site_url('admin/enquiries/' . $enquiry['id']) ?>">Open in admin</a></p>
<?php $content = ob_get_clean();
echo view('emails/_layout', ['subject' => $subject, 'content' => $content]);
