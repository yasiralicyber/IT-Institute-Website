FIX GUIDE — ittimaidan.com (403) + app.ittimaidan.com (500)
============================================================
For: the developer managing the Hostinger account that hosts ittimaidan.com
Code: https://github.com/yasiralicyber/IT-Institute-Website.git  (branch main, commit 5c56288 — latest, fully tested)

WHAT'S WRONG RIGHT NOW
----------------------
- https://ittimaidan.com        -> 403 Forbidden   (document root has no index.php — wrong docroot or files missing)
- https://app.ittimaidan.com    -> 500 error, PHP 8.5.4  (PHP is executing but crashing — almost certainly the .env file is missing)

This pattern matches "repo pulled onto the server without .env and with the docroot pointing at the repo root instead of its public/ folder."

HOW THIS APP MUST BE LAID OUT (from the repo's DEPLOY.md)
---------------------------------------------------------
The ENTIRE project goes in a folder OUTSIDE the web roots, e.g.:

  /home/uXXXXXX/itti/
  ├── app/  config/  database/  resources/  storage/  vendor/   <- NOT web-accessible
  ├── .env                                                      <- create manually, NOT in git
  ├── public/    <- document root for  ittimaidan.com
  └── admin/     <- document root for  app.ittimaidan.com

hPanel -> Websites -> Manage -> "Change document root":
  ittimaidan.com      -> .../itti/public
  app.ittimaidan.com  -> .../itti/admin

vendor/ ships inside the repo — NO composer install needed. CSS is precompiled — no build step.

STEP-BY-STEP FIX
----------------
1. Get the code in place (git clone or pull of the repo above) following the layout shown.
   If an OLD deployment of this site exists on the account, its .env and database are still valid — keep them.

2. Create/restore the .env file in the project ROOT (copy .env.example from the repo and fill in):
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://ittimaidan.com
     ADMIN_URL=https://app.ittimaidan.com
     APP_KEY=<any 40+ random characters — reuse the old key if the old .env still exists>
     DB_DRIVER=mysql
     DB_HOST=localhost
     DB_NAME=...   DB_USER=...   DB_PASS=...     <- from hPanel -> Databases (reset the DB password there if unknown)
   Plus the MAIL_/PAY_/INSTITUTE_ values from .env.example (site works without them; payment/contact
   details are also editable later from Admin -> Settings).

   IMPORTANT: if the previous working database still exists on the account, point .env at it —
   it contains the institute's real student data. Do NOT create a fresh DB unless none exists.

3. PHP version: the account is currently on PHP 8.5. The app was built/tested on PHP 8.1–8.3.
   Set the site's PHP version to 8.3 in hPanel (per website -> PHP Configuration) to be safe.
   Required extensions (standard on Hostinger): pdo_mysql, gd, mbstring, zlib, zip, dom, fileinfo.

4. Permissions:  chmod -R 775 storage

5. Database upgrade — run these TWO scripts once, from the project root (SSH, or a one-off cron job):
     php database/upgrade_website_content.php
     php database/seed_website_content_from_static.php
   - Script 1 only CREATE TABLE IF NOT EXISTS for 5 new CMS tables (awards, activity_categories,
     activity_photos, hero_slides, facilities). It never drops or alters existing tables. Idempotent.
   - Script 2 backfills those tables from the static images already in public/assets/img/ so the
     site looks identical immediately. Guarded — safe to run twice.
   !! NEVER run database/migrate.php or database/seed.php on this server — migrate.php DROPS ALL
      TABLES including real student data. It is for fresh local installs only.

6. Purge caches: hPanel -> the website -> Speed/CDN -> Purge cache (Hostinger CDN caches for 7 days).

VERIFY (all must pass)
----------------------
- https://ittimaidan.com            -> homepage loads, hero slideshow works
- https://ittimaidan.com/activities -> Activities page (old /campus URL 301-redirects here)
- https://ittimaidan.com/awards     -> awards with images
- https://ittimaidan.com/faculty    -> teachers with photos
- https://app.ittimaidan.com        -> admin login; after login there is a new "Website Content"
  sidebar group (Hero Images, Awards, Activities, Facilities) and expanded Settings
  (hero text, stats, logo upload). Admin default password must already be changed — if this is a
  fresh DB, log in and change it immediately.

WHAT'S NEW IN THIS RELEASE (context)
------------------------------------
Commits 2f26435 + 5c56288: full admin CMS for website content — hero slideshow images, awards,
activity photo categories, facilities, course thumbnails, faculty from the Staff table, homepage
headline/stats/logo via Settings, Campus renamed to Activities (+301 redirect), Home added to nav.
New public image-streaming routes: /hero-image/{id}, /award-image/{id}, /activity-image/{id},
/facility-image/{id}, /course-thumbnail/{id}, /staff-photo/{id}, /site-logo.
