# Elimu Yetu SRMS

Student Registration & Management System for **Elimu Yetu Kituo cha Jamii**.
*Ninaweza, nitafanya, najiamini.*

Version 1.0.0 · PHP 8 · runs on one computer on the centre's own network, with no
internet connection and no monthly cost.

---

## What it replaces

| Paper today | In the system |
|---|---|
| Registration forms in a box file | Student register with photo, guardian, national ID and an automatic number (`EY-2026-0001`) |
| Handwritten attendance sheets | Daily register per class, with a rate per student and a flag below 80% |
| Lesson plan notebooks handed to the office | Lesson plans submitted on screen; the line manager marks them reviewed or asks for changes |
| Monthly reports typed in Word | A fixed monthly form per class, acknowledged by the line manager, printable for filing |
| ID cards made in a shop | ID cards printed at real card size (85.6 × 54 mm) from the student's own record |
| Certificates typed one by one | Certificates with a serial, a verification code and a public check page |
| Kitchen counts on a wall chart | Daily tea and meal counts, split between teachers and students, with a monthly report |

---

## The six roles

| Role | Sees |
|---|---|
| **Super Admin** | Everything, plus backups. **Invisible to every other role** — never listed, never counted, and its activity does not appear in anyone else's audit log. Administrators cannot create one. |
| **Administrator** | Departments, courses, registrations, staff accounts, timetable, ID cards, certificates, reports, settings. |
| **Line Manager** | One department only — its courses, students, timetables, and the lesson plans and monthly reports of its facilitators. |
| **Facilitator** | Only the classes assigned to them: attendance, marks, lesson plans, the monthly report, notices. |
| **Student** | Their own courses, timetable, attendance, marks, certificate and the notice board. |
| **Kitchen Admin** | The kitchen pages only. Its section sits at the bottom of the sidebar. |

Access is decided in `index.php` from the route table *before* the page file is
loaded, so a page cannot be reached by guessing its address.

---

## What each role does daily

**Office (Administrator)** — registers a student, prints the registration slip
for signature, enrols them in a course, prints the ID card.

**Facilitator** — opens *Attendance*, taps "Mark all present", changes the few
who are absent, saves. Writes a lesson plan before the lesson. On the last day
of the month, submits the monthly report; the form shows the figures already in
the register so the report and the register agree.

**Line Manager** — clears the review queue on the dashboard: lesson plans and
monthly reports waiting for a response. Watches the list of students below the
attendance threshold.

**Kitchen Admin** — after the morning tea and the afternoon meal, enters four
numbers: cups for teachers, cups for students, plates for teachers, plates for
students. Totals appear while typing.

**Student** — checks the timetable, attendance rate, marks and notices.

---

## Folder layout

```
index.php              Single entry point. Every request goes through here.

app/
  config.php           Database choice and centre details — the one file you edit
  routes.php           Every page, who may open it, and the sidebar
  lib/
    db.php             PDO connection and query helpers
    schema.php         All 18 tables, written once for both databases
    helpers.php        Escaping, CSRF, dates, numbering, photos, audit
    auth.php           Sessions, roles, capabilities, department scoping
pages/                 One file per page
views/                 Layouts (app, auth, print, public) and the icon set
assets/                CSS, one small JS file, logo. No CDN, no web fonts.
storage/
  database/            The SQLite file lives here
  uploads/             Student photos
  backups/             Backup files
  logs/                PHP errors
```

Nothing is downloaded at run time, so the system works with the network cable
unplugged.

---

## Printing

There is no PDF library. Documents are laid out with print CSS and printed from
the browser — choose **Save as PDF** in the print dialog to keep a copy.

| Document | Paper |
|---|---|
| Registration slip, monthly report, most reports | A4 portrait |
| Class register, marks sheet | A4 landscape |
| Certificate | A4 landscape |
| ID card | CR80 card, 85.6 × 54 mm |

In the print dialog, set margins to **Default** and turn **Background graphics
on**, or the colour ribbon will not appear.

---

## Certificate verification

Every certificate carries a serial (`EYC-2026-0001`) and a verification code
(`ABC-1234`, with no letters that can be confused for digits). Anyone can check
a code at `index.php?r=verify` **without signing in**. They see the holder's
name, the course and the issue date — nothing more. Print that address on the
certificate if you want employers to use it.

---

## Security

- Passwords stored with `password_hash()` (bcrypt). No password is ever written to a log.
- Every database query uses a prepared statement.
- Every output is escaped with `e()`.
- Every form carries a CSRF token; a POST without one is refused with 419.
- Sessions are HttpOnly, SameSite=Lax, and expire after 30 minutes idle — which matters on a shared office computer.
- Five failed sign-ins lock that email for a while.
- Uploaded photos are checked with `getimagesize()`, renamed randomly, stored outside the web root and served through a PHP page.
- Sign-ins, registrations, attendance, marks, reviews, certificates and settings changes are all written to the audit log.
- `.htaccess` files deny direct access to `/app` and `/storage` (an `nginx.conf.example` is included too).

---

## What is deliberately not in version 1.0

Listed honestly, so nobody expects them:

- **No Kiswahili interface yet.** The screens are in English. The structure for a
  translation layer is not in place; adding it is a real piece of work, not a toggle.
- **No CSV import** of students. Export works; import is riskier and needs a preview step.
- **No SMS or email.** No Beem Africa or Africa's Talking integration. Notices are on-screen only.
-
- **No photo capture from a webcam** — photos are uploaded as files.
- **No automatic backups.** Backup is one button, pressed by a person.

---



