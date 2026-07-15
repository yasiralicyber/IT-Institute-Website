# ITTI Platform — Feature Index

A complete map of everything in the platform. Two apps share one codebase + database:
**Public site / Student LMS** (`itti.com.pk` → `public/`) and **Admin panel** (`admin.itti.com.pk` → `admin/`).

Demo logins (after seeding): **Student** `student@itti.com.pk / student1234` · **Admin** `admin@itti.com.pk / admin1234`.

---

## 1. Public website
- **Home** — image hero, stats, course grid, about/faculty/campus/awards teasers, free-YouTube section, testimonials, CTAs.
- **Courses** — catalog + rich detail pages (curriculum, free vs locked lessons, reviews, enroll card, matching interactive-lab banner).
- **About · Faculty · Campus (gallery/tour) · Awards · Instructor · Contact** (with Google Map).
- **Admission form** (Name, F/Name, Address, Contact, DOB, Form-B, Email, Program, Gender, Photo) → stored for admin.
- **Transparency** (`/transparency`) — fees & durations table + policies (refund, certificate, support, equipment, delivery), admin-editable.
- **Events & News** (`/events`) + **Hall of Fame** (`/hall`) + **Student Project Showcase** (`/projects`) + **per-student Portfolio** (`/portfolio/{id}`).
- **Coding Playground** (`/playground`) — HTML live preview + Python that runs in-browser (Skulpt).
- **Interactive Labs** (`/labs`) — Network Topology builder, CCTV Coverage planner, Cyber Investigation.
- **Certificate verification** (`/verify`) + **Student ID verification** (`/verify-id/{token}`).
- **Noticeboard TV mode** (`/board`) — full-screen auto-rotating display for a monitor at the institute.
- WhatsApp floating button, light/dark mode, fully responsive.

## 2. Student LMS
- **Auth** + email-verify token; **device lock** = 1 mobile + 1 desktop (2nd device blocked → repeat suspends).
- **Dashboard** — progress, pending requests, certificates, **personalised recommendations**.
- **Receipt-upload payments** — pay → upload receipt → admin approves → course unlocks.
- **Custom self-hosted video player** — own controls (play/seek/speed/fullscreen/mute), **chapter markers**, **in-video questions that pause until answered**, watermark (name+email+id), anti-download, resume position, **data-saver** (click-to-load, downloadable .txt notes). YouTube embeds use a click-to-load facade.
- **Lesson notes & bookmarks**, **chapter tests** gating the next chapter, **content drip** (scheduled lesson release).
- **Learning roadmap** (gamified path), **achievements** (XP, levels, badges, leaderboard).
- **Certificates** — chapter + course, with QR + public verification.
- **Project portfolio** (submit → approved → public), **community Q&A**, **concept search**, **QR attendance check-in**, **devices** management.

## 3. Admin panel (grouped sidebar + command palette `Ctrl/⌘-K` + global search)
- **Dashboard** + **Operations Control Center** (`/ops`, live big-screen view).
- **LMS** — Courses/chapters/lectures/quizzes builder (+ MP4 upload, free toggle, drip date, **🎬 interactive editor** for markers & in-video questions), Reviews moderation, Community replies, **Projects** moderation.
- **Students** — list/detail, suspend, **reset device locks**, **ID card**, **fees**, **support timeline + notes**, guardian access; **At-Risk** alerts; **Admissions**; **Online Payments** approve/decline.
- **Institute** — Batches, Staff, Classrooms, **Attendance** (manual + QR + biometric CSV import + guide).
- **Finance** — **Fees** (admission/monthly/installments/discounts/scholarships, printable receipts, ledger, bulk monthly generation).
- **Content** — Events/News, Notices, **Settings** (payment/contact/hero/policies → live on site).
- **System** — **Bulk Import** (CSV/Excel/Sheets wizard w/ mapping, validation, rollback, history, templates, export), **Automations** (visual workflow builder), **Audit Log**, **Recycle Bin** (restore + password-protected purge), **Backups** (DB + media zip, download, cron).

## 4. Guardian / Parent portal (`/guardian`)
Login by student Reg-No + PIN → read-only **attendance, fees, test results, progress, notices, timetable**. No lesson content or private discussions.

## 5. Platform-wide
Audit logging on key actions · soft-delete recycle bin · backups + cron · role gate (admin) · CSRF, output escaping, secure file streaming, formula-injection-safe exports · SQLite (dev) / MySQL (prod) via one driver-aware migration.

See **README.md** to run locally and **DEPLOY.md** to deploy on Hostinger.
