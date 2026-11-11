# Missing credentials and configuration

Nothing here was invented. Each item needs a value from the client before go-live. See also `analysis/09-missing-information.md` for missing *content*.

| # | Item | Status | What works without it | What to do |
|---|---|---|---|---|
| 1 | **CCAvenue payment gateway** — Merchant ID, Access Code, Working Key | Not supplied. CCAvenue was confirmed as the original store's only payment method. | Checkout, order creation, e-mails and the admin order workflow all work. Orders are saved as **Pending payment / Unpaid**, the customer is told nothing was charged, and an administrator can record a payment received offline. No order is ever marked paid automatically. | Put the three values in `.env` (`ccavenue.merchantId`, `ccavenue.accessCode`, `ccavenue.workingKey`), set `ccavenue.mode` to `test` or `live`, and register the redirect/cancel URLs in the CCAvenue panel (see `SETUP.md` §6). Then place a real test-mode payment end to end. |
| 2 | **SMTP (outgoing e-mail)** | Not supplied. | Every e-mail (order confirmation, status update, welcome, password reset, admin notifications) is written to `writable/emails/` as an HTML file instead of being sent. | Fill `email.SMTPHost`, `SMTPUser`, `SMTPPass`, `SMTPPort`, `SMTPCrypto`, `fromEmail` in `.env`. |
| 3 | **Production database user** | Local uses XAMPP `root` with no password. | — | Create a dedicated user with a strong password on the live server. |
| 4 | **Live domain / HTTPS** | Local: `http://localhost:8080/`. | — | Set `app.baseURL` and `app.forceGlobalSecureRequests = true`. |
| 5 | **GSTIN and FSSAI licence number** | Not published on the original site. | Footer simply omits them. | Admin → Settings → Shop & shipping. |
| 6 | **Social-media links** | None on the original site. | Icons are hidden while empty. | Admin → Settings → Social media. |
| 7 | **Analytics / tracking IDs** | None found on the original site. | — | Not added. Can be added to `app/Views/layouts/site.php` if required. |

## How the payment integration was verified without live credentials

The gateway code implements CCAvenue's documented scheme (AES-128-CBC, key = MD5 of the working key, hex-encoded payload). With a dummy working key the automated test confirms that:

- the payment page posts an encrypted request to the CCAvenue **test** endpoint and never exposes the key;
- an undecryptable callback is rejected;
- a "Success" callback whose amount does not match the order does **not** mark it paid;
- a "Failure" callback marks the payment failed;
- only a decrypted "Success" callback with the matching order number and amount marks the order paid and moves it to Processing.

What could **not** be tested: a real transaction against CCAvenue's servers. That needs the client's merchant account.
