# Build Prompt — ITTI LMS Platform

> Paste everything below the line into Claude Code to start the build.
> Build in **two phases**: Phase 1 = public website + student LMS. Phase 2 = admin panel.

---

## ROLE & GOAL

Build a **production-grade, secure Learning Management System (LMS)** for an IT training institute that sells paid video courses. The platform must look **world-class** (Top-Notch UI/UX — modern, beautiful, fast, fully responsive, light/dark mode, smooth micro-interactions) and run on **Hostinger shared hosting**.

**Brand / Institute:** *IT Training Institute, Kumber Maidan* (short: **ITTI**)
**Public site domain:** `itti.com.pk`
**Admin panel domain:** `admin.itti.com.pk` (separate subdomain, separate login)
**Logo:** provided in this folder — use it across header, footer, certificates, favicon, login screens, and emails. Derive the brand color palette from the logo.

> ⚠️ **Hosting constraint:** Hostinger shared hosting has **no Node runtime**. Use a stack that deploys cleanly there. **Recommended: Laravel (PHP 8.2+) + MySQL + Blade/Livewire (or Inertia), Tailwind CSS compiled to static assets.** Pick the best deployable stack and document the deploy steps for Hostinger (public_html structure, `.htaccess`, DB import, cron for queue/certificates). If you propose a different stack, justify it against the no-Node constraint.

Before coding, **scaffold the full architecture and a clear folder structure**, then implement. Keep code clean, commented for a non-technical maintainer, and seed the database with all courses so the site is demo-ready.

---

## CONTENT TO PULL FROM RESOURCES

Research and incorporate real details from these (about the instructor & institute), written in polished marketing copy:

- **Facebook page:** https://www.facebook.com/share/17wMmKSqSf/?mibextid=wwXIfr
- **Free YouTube channel / playlist** (feature prominently — "Learn for free on our YouTube channel" with embedded playlist): https://youtube.com/playlist?list=PLOh6YaRDYnhxc0nlxjgUcwTMti2jm-95U
- **Google listing:** https://share.google/VhMvY3jczvZnEouCq
- **Google Maps** — embed the institute's map/location + directions in the Contact section (pull address, hours, reviews if available).

**Instructor section:** write a rich "About the Instructor" bio (experience, expertise, teaching style) and an "About the Institute" section. Mention the **free YouTube channel** as a way to try the teaching style before buying.

**Contact details:** instructor + institute contact info, address, map, email, social links.

**WhatsApp floating button** (sticky, on every page): click-to-chat to **+92 305 8382085** → `https://wa.me/923058382085`.

---

## COURSES (seed these; first **5 lectures of every course are FREE**, the rest are **paid**)

1. CCNA (R&S) 200-301 V1.1
2. Ethical Hacking
3. Cyber Security
4. CCTV Camera Installation
5. C++
6. OOP (Object-Oriented Programming)
7. HTML
8. Java
9. Python

Each course = chapters → lectures (video + resources). First 5 lectures preview-free for everyone; remaining lectures locked until purchase is approved.

---

## PHASE 1 — PUBLIC WEBSITE + STUDENT LMS

### 1. Marketing site
- Stunning landing page: hero, value props, course catalog with rich cards (level, duration, price, free-preview badge), featured/free YouTube section, instructor & institute story, testimonials/reviews, stats, FAQ, contact + Google Map, footer.
- Course detail pages: curriculum outline, what you'll learn, free preview lectures playable inline, locked lectures with a "Get Access" CTA, instructor card, student reviews.
- SEO-friendly, fast, accessible.

### 2. Auth & accounts
- Student registration/login, email verification, password reset, profile with photo.

### 3. Payment via receipt upload (manual approval)
- Student selects a course → sees payment instructions (bank/JazzCash/Easypaisa details, configurable from admin) → **uploads payment receipt (image/PDF)** + reference no.
- Creates a **purchase request** with status `pending`. Student sees status (Pending / Approved / Declined) on their dashboard.
- On admin **approval**, the course unlocks for that student automatically; on **decline**, show reason. Email + in-app notification on every status change.

### 4. Course player & learning flow
- Clean video player for unlocked lectures, progress tracking, resume where left off, lecture resources/notes, mark-complete.
- **Chapter tests (quizzes):** after each chapter's lectures, a test is required. **Student must PASS (configurable pass %) to unlock the next chapter.** Show score, allow retakes (configurable attempts).

### 5. Certificates (chapter-wise + full course)
- Auto-generate a **chapter completion certificate** and a **full course completion certificate** (beautiful PDF with logo, student name, course, date, unique credential ID + QR code).
- **Public verification page** on the site: anyone can verify a certificate by its credential ID/QR → shows validity, name, course, date.

### 6. Community / Q&A
- A community space where students post questions/queries per course; threaded discussion. (Admin replies handled in Phase 2.) Build the student-facing posting + reading UI now with a placeholder for admin replies.

### 7. Reviews
- Students can write a **course review + star rating** (only for courses they own). Display on course pages after approval.

### 8. Institute admission form (public)
Public **admission form** that stores a complete student record in the database (visible later in admin). Fields:
- Name
- Father's Name
- Address
- Contact No
- Date of Birth
- Form B Number
- Email
- Program(s) — multi-select from the course list
- Gender
- **Student photo upload**

Validate all inputs, store securely, send confirmation email, show success state.

### 9. Time Table
- Public **Time Table** section that displays the current schedule (managed from admin in Phase 2). Build the display + data model now; support image upload and/or structured table.

### 10. 🔒 Content security (TOP PRIORITY — protect lectures from leaking)
Implement layered protection (be honest in code comments that browser DRM is best-effort, and combine multiple deterrents):
- **No direct downloads:** stream video via signed, expiring URLs / tokenized access; never expose raw file paths; disable right-click "Save", block the player's download button.
- **Discourage screen recording / leaks:** dynamic **visible + invisible watermark overlay** on every video showing the logged-in student's name, email, and ID (so leaked recordings are traceable); disable text selection on lecture pages; deter common shortcuts (PrintScreen, dev-tools hotkeys) with clear warnings.
- **Domain/referrer-locked** video access; tokens tied to the logged-in session.
- **Single active session per device class — strict device limit:** each account may be logged in on **at most ONE mobile + ONE laptop/desktop** at a time.
  - Register a device fingerprint on login (device type + fingerprint). Block a 3rd device with a clear warning.
  - If sharing/abuse is detected again after the warning, **auto-suspend the account** and flag it for admin review.
  - Student can see their registered devices; admin can reset them (Phase 2).
- Rate-limit, CSRF protection, secure headers, encrypted storage of receipts/photos.

### 11. Platform polish
- WhatsApp floating button (number above), light/dark mode, notifications (in-app + email), loading/empty/error states, mobile-first responsive, fast.

---

## PHASE 2 — ADMIN PANEL (`admin.itti.com.pk`)

Build a **dead-simple admin panel for a non-technical / young admin** — big clear buttons, plain language, confirmations before destructive actions, search/filter everywhere, dashboard with key stats. Separate login from the public site, with roles (Super Admin / Admin).

Core management:
- **Courses, chapters, lectures:** upload / edit / delete / reorder (drag-drop), set free vs paid, attach resources, manage video uploads securely.
- **Quizzes/tests:** create questions per chapter, set pass %, attempts.
- **Students:** view/search all students & their progress, edit, activate/suspend, **reset device locks**, view registered devices.
- **Purchase requests:** view uploaded receipts, **approve / decline (with reason) / verify** → unlock course on approval; bulk actions; payment-instructions editor.
- **Admissions:** browse/search all admission-form submissions (with photo & all fields), export to Excel/PDF, status workflow.
- **Certificates:** view/revoke issued certificates, manage templates.
- **Community moderation:** **admin replies to student queries**, pin/close/delete threads, moderate.
- **Reviews:** approve/hide/delete course reviews.
- **Time Table:** upload/manage the schedule shown publicly.
- **Content/pages & settings:** edit contact info, payment details, hero copy, social links, instructor bio.
- **Dashboard & analytics:** revenue (approved purchases), enrollments, active students, pending requests, completion rates, charts.
- **Notifications/announcements** to students; activity/audit log.

### 🔐 Secure-verification gate (data-security requirement)
For sensitive record lookups/verification, require the requester to enter **Name + Father's Name + CNIC + Date of Birth**; only on an exact match is the record revealed. Apply this verification gate to the relevant lookup/verification flow so personal data isn't exposed without these four matching identifiers.

---

## ADVANCED / "CRAZY" FEATURES (add as many as feasible)
- AI-style course recommendations, "continue learning" rail, streaks & gamified badges/XP, leaderboards.
- Coupon/discount codes; installment tracking on receipt approvals.
- Auto-issued certificates with QR verification (above) + LinkedIn "Add to profile" link.
- Email + WhatsApp/in-app notifications for approvals, new lectures, test results.
- Searchable course content, bookmarks, lecture notes, downloadable resources (non-video only).
- Multi-language ready (English/Urdu), RTL-safe.
- PWA/offline-friendly catalog, fast image/video optimization.
- Audit logging, automated DB backups (cron), maintenance mode.

---

## DELIVERABLES
1. Clean, documented codebase + clear folder structure + README.
2. Database schema/migrations + seeders (all 9 courses, demo chapters/lectures, an admin user).
3. **Hostinger shared-hosting deploy guide** (no Node): how to deploy public site to `itti.com.pk` and admin to `admin.itti.com.pk`, DB import, `.htaccess`, cron jobs, env config.
4. `.env.example` with all keys (mail, payment details, app URLs).
5. Notes on the content-security limitations and exactly what each protection does.

**Start with Phase 1.** First propose the **stack + architecture + folder structure + DB schema**, get it right, then build the public site and student LMS. We'll do Phase 2 (admin panel) after Phase 1 is working.
