# Cyberfolio — improved PHP/MySQL portfolio

This upgrade adds short video uploads/playback, a separate client-account flow without email verification, password hashing, session renewal, CSRF protection and rate limiting, plus improved upload restrictions, response headers and deployment guidance.

## Important before publishing

- This is a code upgrade, **not a guarantee of zero bugs or a security audit**. Test on staging first.
- Never commit database passwords, mail credentials, API keys, or Cloudflare Turnstile secret keys. The database settings read environment variables.
- Client registration is open without email verification in this build. Before allowing public registration, configure suitable abuse protection (CAPTCHA, rate limits, HTTPS) and monitor sign-ups.
- Use HTTPS only, PHP 8.2+ (recommended), MySQL 8/MariaDB, PDO MySQL, Fileinfo and cURL. Keep debug output disabled online.
- Set environment values in your hosting control panel: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_KEY` (long random secret), and `APP_DEBUG=0`. Never put production secrets in GitHub.

## Local XAMPP setup

1. Back up the existing site and database.
2. Extract `cyberfolio` into `C:\xampp\htdocs\cyberfolio`.
3. Configure DB values for your local MySQL installation. A default XAMPP may use `root` with an empty password; use your actual settings.
4. Start Apache and MySQL. Open `http://localhost/cyberfolio/check.php` and create/update tables. The migration creates `client_users` and `auth_attempts` too.
5. Open `admin.php` and create the administrator account with a unique strong password.
6. In Admin > Settings, add your details and create projects/lessons.
7. Delete `check.php` after setup and verify it is not publicly accessible.
8. Visit `auth.php?mode=register` to create a normal client account. Email verification is disabled; keep this build on localhost until you configure production abuse protection.

## Short videos

1. Admin > Projects or Cyber lessons > Add/Edit item.
2. Upload MP4, WebM or Ogg videos up to 50 MB each, then Save item.
3. Videos appear on the item detail page with browser controls; they do not autoplay.
4. Compress clips. For larger files or frequent views, use reputable object storage/video CDN rather than basic shared hosting.
5. Keep PHP and hosting request limits aligned. Raising upload limits increases storage and abuse risk.

## Client accounts

- Visit `/auth.php`; client accounts are separate from the admin login.
- Registration requires Turnstile and a deliverable verification email. Configure `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` as server environment variables.
- Test verification links and their 30-minute expiry on staging.
- The current account area is basic. It does not yet include password reset, MFA, resend verification, account deletion, or private client content. Add these before handling sensitive customer data.
- Production email should use authenticated SMTP through a maintained library, not an unconfigured `mail()` call.

## GitHub and hosting

1. Review all files and remove personal data, credentials, real customer data and private documents before publishing.
2. `.gitignore` excludes `.env`, logs, vendor dependencies and uploads. Do not commit real uploads or secrets.
3. GitHub Pages cannot run PHP/MySQL. Deploy to PHP hosting or a VPS; use GitHub as the source repository.
4. Configure HTTPS, backups, a least-privilege DB user, updated PHP, firewall, SMTP, Turnstile and monitoring.
5. Ensure `.sql`, configuration, backups, logs and `.env` files are not served publicly. `.htaccess` applies only to Apache; configure equivalent rules for Nginx.
6. Test pages, uploads, registration, email delivery, expired/invalid links, throttling, CSRF rejection and file rejection before launch. Remove the setup checker.

## Future production work

Add password reset using single-use expiring tokens, MFA for admin, verification resend, audit logs, account deletion, versioned migrations, automated backups, malware scanning for uploads and storage outside the web root. MIME checks do not guarantee files are harmless. Run automated tests, dependency checks, static analysis and an independent security review.

The PHP files in this archive have been syntax-checked. Live database, SMTP, CAPTCHA, browser and hosting behaviour still needs testing in your environment.


## Video playback troubleshooting

- Use MP4 encoded as H.264 video + AAC audio for the widest browser support. Changing only the filename extension does not convert a video codec.
- Upload videos through Admin > Projects or Cyber lessons, then click Save item. Open the item's detail page; videos are not embedded on the listing cards.
- The upload folder includes explicit video MIME types and does not apply a sandbox Content-Security-Policy to media responses.
- If a video still fails, test the “Open video in a new tab” link. If the direct link also fails, check the upload succeeded, the file is not corrupted, and the hosting provider supports HTTP byte ranges. Re-export/convert the clip to MP4 (H.264/AAC) and upload again.
- PHP upload settings must allow the file size: `upload_max_filesize` and `post_max_size` should be greater than the video file size. Keep the application's own 50 MB limit in mind.
