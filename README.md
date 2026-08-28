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
install.php            First-time setup. DELETE IT once the centre is live.
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

## Tested

An automated suite signs in as each of the six roles and requests every page:
99 checks pass on SQLite and 99 on MariaDB 10.11, with no PHP warnings. It also
confirms the super admin stays invisible to an administrator, that the kitchen
account is refused everywhere except the kitchen, and that a POST without a CSRF
token is rejected.

See `INSTALL.md` to set it up.

# Installing Elimu Yetu SRMS

Two paths. Read the first one; only use the second when you are ready.

---

## Path 1 — the quick way (SQLite, recommended to start)

Nothing to configure. The whole database is one file.

1. Install PHP 8 and Apache on the office computer:

   ```bash
   sudo apt update
   sudo apt install apache2 php php-sqlite3 php-mbstring libapache2-mod-php
   sudo a2enmod headers expires
   sudo systemctl restart apache2
   ```

2. Copy this folder into the web root and let Apache own it:

   ```bash
   sudo cp -r srms /var/www/html/
   sudo chown -R www-data:www-data /var/www/html/srms/storage
   sudo chmod -R 775 /var/www/html/srms/storage
   ```

   `storage` **must** be writable — that is where the database, the photos and
   the backups live. Nothing else needs write access.

3. Open `http://localhost/srms/install.php` in the browser.

4. Fill in the centre name, then the super admin's name, email and password.
   The email is the username. Use a password only that one person knows.

5. Leave **"Add sample data"** ticked the first time. It creates three
   departments, four courses, twelve students with attendance history, a
   timetable, lesson plans, monthly reports and two weeks of kitchen records —
   enough to train staff on before real data goes in. Every sample account uses
   the password `Elimu@2026`.

6. **Delete `install.php`.**

   ```bash
   sudo rm /var/www/html/srms/install.php
   ```

   Leaving it there is the one serious mistake you can make at this stage.

7. Sign in at `http://localhost/srms/`.

### Clearing the sample data before real use

Sign in as the super admin and delete the sample staff accounts from *Staff
accounts*. To wipe everything and start empty, delete the database file and run
the installer again with the tick box **off**:

```bash
sudo rm /var/www/html/srms/storage/database/elimu_srms.sqlite
```

---

## Path 2 — full LAMP (MySQL / MariaDB)

This is what the proposal specifies. Use it when the centre wants MySQL tooling.

1. Install MySQL as well:

   ```bash
   sudo apt install mysql-server php-mysql
   ```

2. Create the database and a user that is not root:

   ```sql
   CREATE DATABASE elimu_srms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'srms'@'localhost' IDENTIFIED BY 'eydo';
   GRANT ALL PRIVILEGES ON elimu_srms.* TO 'srms'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Edit `app/config.php`:

   ```php
   define('DB_DRIVER', 'mysql');   // was 'sqlite'
   define('DB_NAME', 'elimu_srms');
   define('DB_USER', 'srms');
   define('DB_PASS', 'eydo');
   ```

4. Run `install.php` as above, then delete it.

The schema is written once and emitted for whichever driver is set, so both
paths produce the same 18 tables. Switching later means moving the data across
yourself — the driver setting does not migrate anything.

---

## Letting other computers on the network use it

The system is designed for a small LAN with no internet.

1. Find the computer's address:

   ```bash
   hostname -I
   ```

   Something like `192.168.1.20`.

2. Give that computer a fixed address in the router, or the number will change
   and everyone's bookmark will break.

3. On any other computer or phone on the same wifi, open
   `http://192.168.1.20/srms/`.

4. Allow it through the firewall if one is on:

   ```bash
   sudo ufw allow from 192.168.1.0/24 to any port 80
   ```

Do not expose port 80 to the internet. There is no HTTPS here, so passwords
would travel in the clear. If the centre ever needs access from outside, put it
behind a VPN or add a certificate first.

---

## Settings to change on day one

Sign in as the super admin and open **Settings**:

- Phone, email and address — these print on every document
- Academic year
- Attendance threshold (default 80%)
- The two certificate signatories and their titles
- ID card validity in months (default 12)
- The kitchen service labels, if the centre calls them something else

To replace the logo, overwrite `assets/img/logo.svg`. Keep it square. A PNG works
too — change the filename in `views/layout_app.php` and `views/layout_print.php`.

---

## Backups

Sign in as the super admin, open **Backup & restore**, press **Create backup**,
then **Download**. Copy the file to a flash disk and keep that disk in a
different room from the computer.

Do this every Friday. It takes two minutes.

**Photos are not in the backup file.** When you archive a term, copy
`storage/uploads` as well.

### Restoring

SQLite — stop using the system, then replace
`storage/database/elimu_srms.sqlite` with the backup, keeping that exact name.

MySQL:

```bash
mysql -u srms -p elimu_srms < elimu-srms-20260806-141500.sql
```

Test a restore once a term on a spare copy of the folder. A backup you have
never restored is only a hope.

---

## Troubleshooting

**"Could not open the database"** — `storage` is not writable.
`sudo chown -R www-data:www-data storage && sudo chmod -R 775 storage`

**Blank white page** — set `define('DEBUG', true);` in `app/config.php`, reload
to read the error, then set it back to `false`. Errors are also logged to
`storage/logs/php-errors.log`.

**Photos do not appear** — check that `storage/uploads` exists and is writable.
Photos are served through `index.php?r=media.photo`, never linked directly.

**Certificates and ID cards print without colour** — turn on "Background
graphics" in the browser print dialog.

**"This form expired"** — the session timed out after 30 minutes idle. Sign in
again. Change `SESSION_IDLE_MINUTES` in `app/config.php` if 30 is too short.

**"Allocation of JIT memory failed"** — some hosts prevent PHP from allocating
executable memory used by PCRE JIT. Prefer fixing this in `php.ini` rather than
editing `install.php` permanently. To disable PCRE JIT for Apache PHP, create
a small config file or add this line to the appropriate `php.ini` for your
SAPI:

```ini
pcre.jit=0
```

Then restart the web server, for example:

```bash
sudo systemctl restart apache2
```

To find which `php.ini` is loaded by the webserver, check `phpinfo()` from a
PHP page served by the webserver or look in `/etc/php/<version>/apache2/php.ini`.

**Locked out after failed sign-ins** — wait a few minutes, or clear the
`login_attempts` table.

**Forgotten super admin password** — there is no email reset, by design. Reset
the hash directly:

```bash
php -r 'echo password_hash("NewPassword123", PASSWORD_DEFAULT), PHP_EOL;'
```

Then, for SQLite:

```bash
sqlite3 storage/database/elimu_srms.sqlite \
  "UPDATE users SET password_hash='<paste the hash>' WHERE email='you@elimuyetu.org';"
```

---

## Requirements

- PHP 8.0 or newer, with `pdo_sqlite` (Path 1) or `pdo_mysql` (Path 2), and `mbstring`
- Apache with `mod_headers`, or nginx (see `nginx.conf.example`)
- Any modern browser
- No Composer, no npm, no internet connection

