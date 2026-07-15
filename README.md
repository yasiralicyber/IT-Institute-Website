# IT Training Institute — LMS + Campus Platform (ITTI)

A complete **institute management + e-learning platform** for IT Training Institute, Kumber Maidan: a marketing website, a secure paid-video LMS, full physical-campus management (batches, attendance, fees, ID cards, guardian portal), interactive learning tools, and an easy admin panel — all in one.

👉 **See [FEATURES.md](FEATURES.md) for the complete feature index.**

**Stack:** plain PHP 8 + MySQL (PDO), Tailwind compiled to a static file. No framework, no Node at runtime → deploys to **Hostinger shared hosting**. Local dev uses SQLite.

## Run locally
PHP 8.3 is bundled via winget. Start the servers (the preview configs `itti-site` / `itti-admin` do this), or manually:
```
dev/serve.cmd          # public site  → http://127.0.0.1:8090
dev/serve-admin.cmd    # admin panel  → http://127.0.0.1:8091
```
Rebuild CSS after editing views: `dev/build-css.cmd`
Reset the database: `php database/migrate.php && php database/seed.php`

## Demo logins
- **Student:** student@itti.com.pk / student1234 (enrolled in Python, has a certificate)
- **Admin:** admin@itti.com.pk / admin1234

## Structure
```
app/            Core (router, db, auth, view), Controllers, Models
  Controllers/Admin/   the admin panel controllers
config/         config + .env loader (+ DB settings overlay)
database/       migrate.php, seed.php, data/courses.php
resources/views public site, student area, admin, layouts
resources/css   Tailwind source (compiled to public/assets/css/app.css)
public/         itti.com.pk document root
admin/          admin.itti.com.pk document root
storage/        uploads (receipts, photos, videos), sqlite, logs  (not web-served)
```

## Features (summary — full list in [FEATURES.md](FEATURES.md))
- **Public site:** marketing pages, courses with free previews, about/faculty/campus/awards, transparency (fees & policies), events, hall of fame, project showcase, coding playground, interactive labs, noticeboard TV mode, certificate/ID verification.
- **Student LMS:** **1-mobile+1-desktop device lock**, **custom self-hosted video player** with chapter markers + **in-video questions** + watermark + anti-download + data-saver, receipt-upload payments, chapter-test gating + content drip, notes/bookmarks, learning roadmap, **gamification** (XP/badges/leaderboard), QR-verifiable certificates, project portfolio, community, concept search, QR attendance check-in, personalised recommendations.
- **Admin panel:** grouped UI + command palette + global search + **operations control center**; receipt approvals, students (device reset, ID cards, fees, support timeline, **at-risk alerts**), **batches/staff/classrooms/attendance** (manual + QR + biometric CSV), **fee management** (receipts/ledger/installments/scholarships), events/notices/settings, **bulk import** (CSV/Excel/Sheets), **automations** (workflow builder), **audit log + recycle bin + backups**.
- **Guardian portal:** read-only attendance/fees/results/progress/notices for parents.

See **[DEPLOY.md](DEPLOY.md)** for Hostinger deployment and **[FEATURES.md](FEATURES.md)** for the full feature index.
