# ITTI LMS — Hostinger Deployment Guide

This app is **plain PHP 8 + MySQL** with a **pre-compiled** Tailwind CSS file — there is **no Node/build step on the server**. It runs on Hostinger shared hosting as-is.

---

## 1. What you need
- A Hostinger plan with **PHP 8.1+** and **MySQL**.
- Required PHP extensions (all standard on Hostinger): **pdo_mysql, gd, mbstring, zlib, zip, dom**.
  `gd` powers the ID-card/certificate **QR codes**; without it QR generation is skipped (everything else still works).
- Domain **ittimaidan.com** and subdomain **app.ittimaidan.com** (create the subdomain in hPanel → Subdomains).
- The project folder (this whole directory), **including `vendor/`** (the PDF + QR libraries — FPDI, FPDF, chillerlan/php-qrcode). There is **no Composer step on the server**; just upload the `vendor/` folder that ships with the project.

> Do **not** upload the dev-only helpers: `dev/bin/`, `composer.phar`, and the `dev/*.php` scripts are for building CSS / testing locally and are not needed live (they're in `.gitignore`).

## 2. Folder layout on the server
Upload the **entire project** to your account, e.g. to `/home/uXXXXXX/itti/`. The app code (`app/`, `config/`, `database/`, `resources/`, `storage/`, `.env`) stays **outside** the web roots; only `public/` and `admin/` are served.

```
/home/uXXXXXX/itti/
├── app/  config/  database/  resources/  storage/   ← NOT web-accessible
├── .env                                              ← your real settings
├── public/     ← document root for  ittimaidan.com
└── admin/      ← document root for  app.ittimaidan.com
```

### Point the document roots (hPanel)
- **ittimaidan.com** → set its document root to `…/itti/public`
- **app.ittimaidan.com** → set its document root to `…/itti/admin`

(hPanel → Websites → Manage → "Change document root", or set it when creating the subdomain. If you cannot move the root, put the project in `public_html`'s parent and point the roots accordingly.)

## 3. Create the database
1. hPanel → **Databases → MySQL** → create a database + user, note the name/user/password.
2. Create your config file: copy `.env.example` to `.env` and fill in:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://ittimaidan.com
   ADMIN_URL=https://app.ittimaidan.com
   APP_KEY=<paste 40+ random characters>
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_NAME=...   DB_USER=...   DB_PASS=...
   # Mail (Hostinger SMTP) + payment + contact details…
   ```

## 4. Build the tables + seed the courses
Run the two scripts **once**. Pick whichever your plan supports:

- **SSH (Business plan and up):**
  ```bash
  cd ~/itti
  php database/migrate.php     # creates all tables in MySQL
  php database/seed.php        # loads the 9 courses + a demo admin
  ```
- **No SSH (Premium):** hPanel → **Advanced → Cron Jobs**, add a one-off cron:
  `php /home/uXXXXXX/itti/database/migrate.php` (let it run once, then the same for `seed.php`, then delete the cron).

### Automatic backups (recommended)
Add a recurring **Cron Job**: `php /home/uXXXXXX/itti/database/backup.php` (e.g. daily at 2 AM). It writes a zip of the database + media to `storage/backups` (outside the web root) and keeps the latest 14. Admins can also create/download backups any time from **Admin → System → Backups**. (Requires the PHP `zip` extension — standard on Hostinger; without it, a database-only `.sql`/`.sqlite` backup is produced instead.)

> ⚠️ `migrate.php` **drops and recreates** tables — run it only on first install, never again on live data.

After seeding, the default admin login is **admin@ittimaidan.com / admin1234** — change this password immediately (or edit the seed before running).

## 5. Permissions
Make the upload/log folders writable (hPanel File Manager → Permissions, or chmod):
```
chmod -R 775 storage
```
`storage/uploads/` holds receipts, student photos and uploaded videos (kept out of the web root and streamed through PHP so they can't be hot-linked or downloaded directly).

## 6. Go live checklist
- [ ] `.env` has `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`.
- [ ] Real payment details + WhatsApp/contact set (or set them later in **Admin → Settings**).
- [ ] SSL enabled for both domains (hPanel → SSL).
- [ ] Logged in to **app.ittimaidan.com**, changed the admin password, added a real course/lectures.
- [ ] Submitted a test admission + a test receipt and approved it.

---

## Bulk Import (Admin → System → Bulk Import)
Admins can import Students, Staff, Courses, Batches, Admissions, Attendance, Fees, Notices, etc. from **CSV, Excel (.xlsx), a pasted table, or a Google Sheet**, with a guided wizard (choose → upload → map columns → validate → import) plus per-section templates, export, history and one-click **rollback**.
- **Google Sheets** uses the sheet's public CSV export — just share the sheet as *"Anyone with the link"* and paste its URL. **No Google Cloud API key is required** for this method. (If you later want authenticated private-sheet sync, that needs a Google service-account JSON — tell us and we'll wire it up.)
- Uploaded import files are stored privately in `storage/imports` (outside the web root). Exports are protected against spreadsheet formula-injection.

## Editing the design later
The CSS is already compiled to `public/assets/css/app.css` (and mirrored in `admin/assets/css/`). If you change HTML classes in `resources/views`, rebuild locally with `dev/build-css.cmd` and re-upload those two CSS files. Day-to-day content (courses, lectures, prices, timetable, payment info, hero text) is all editable from the **admin panel** — no code needed.

## Notes on content security
Lessons are protected with layered, best-effort deterrents (documented in code): tokenless access requires a logged-in session; uploaded videos stream through an access-checked PHP route with **no download header**; a **moving watermark** shows each viewer's name/email/ID so any leak is traceable; right-click, dev-tools and screenshot shortcuts are blocked; and the **1 mobile + 1 desktop device lock** suspends shared accounts. For maximum protection on shared hosting, host paid videos as **unlisted** streams or upload MP4s (served via the protected route) rather than public links.
