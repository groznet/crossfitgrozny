# Grozny Gym — CrossFit Member Tracker

A mobile-first, Russian-language web app for the CrossFit trainer Adam to track
which of his CrossFit-program members have paid, whose subscription is about
to expire, and who has lapsed. See `CLAUDE.md` in this repository for the full
product brief.

Built with Laravel + Blade (server-rendered, no SPA), Tailwind CSS and
Alpine.js loaded from CDN (no Node/npm build step), and MySQL.

## Local setup

Requirements: PHP 8.3+, Composer, a local MySQL server.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and point `DB_*` at your local MySQL server (defaults assume a
`crossfitgrozny` database on `127.0.0.1:3306` with the `root` user and no
password — create that database first: `mysql -u root -e "CREATE DATABASE
crossfitgrozny CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`).

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

No `npm install` or build step is needed — Tailwind and Alpine.js load
directly from a CDN in the Blade layout.

### Seeded accounts

The seeder creates one admin account for Adam:

- **Login:** `adam` (or phone `+79639892011`)
- **Password:** printed to the console when you run `php artisan migrate
  --seed` (look for the "Admin account seeded" line), and fixed at
  `50b7fb8d763c` in `database/seeders/AdminUserSeeder.php` for this build.

There is no change-password screen in this MVP. To set a different password
later, use `php artisan tinker`:

```php
$user = \App\Models\User::where('username', 'adam')->first();
$user->update(['password' => \Illuminate\Support\Facades\Hash::make('new-password')]);
```

The seeder also creates 10 demo members spanning every subscription status
(active, expiring soon, expired, no payments), plus a couple of pending
public-form submissions and one archived member, so every screen has
something to show right after a fresh `migrate --seed`.

### SMS phone verification (public form) — currently switched off

The public "join" form at `/profile` can require the visitor to verify their
phone number via an SMS code (via [sms.ru](https://sms.ru)) before their
submission is saved. **This is switched off by default** (`SMS_VERIFICATION_
ENABLED=false` in `.env`) — the form saves the submission directly, the same
way it always has. The code is fully built and tested; flip one setting to
turn it back on:

```
SMS_VERIFICATION_ENABLED=true
SMSRU_API_ID=your-api-id-from-sms.ru
SMSRU_TEST_MODE=true
```

- Get `SMSRU_API_ID` from your sms.ru account's home page. **Never commit a
  real key** — it stays in `.env` only (already git-ignored).
- Leave `SMSRU_TEST_MODE=true` for local development and staging: sms.ru
  simulates the send (no real text message, no balance spent) while the rest
  of the flow — code generation, storage, verification — works exactly as in
  production. Set it to `false` only in the real production `.env`.
- With `SMSRU_API_ID` blank, sending silently fails and is logged to
  `storage/logs/laravel.log`, but the verification flow still works
  end-to-end for local testing: read the generated code straight from the
  cache instead of a text message —
  `php artisan tinker --execute 'dd(Cache::get("profile-otp:+79991234567"));'`
  (swap in the phone number you submitted).

How it behaves once enabled: a code is valid for 5 minutes and allows 5
wrong guesses before it's invalidated; a visitor can request a new code once
per 60 seconds; and sending/resending a code is rate-limited to 3 requests
per minute per phone number (falling back to per-IP if no phone was given
yet), to stop someone from running up your SMS balance or spamming a
stranger's phone.

### Duplicate pending requests are blocked either way

Regardless of the SMS-verification setting, the public form always rejects a
phone number that already has an unreviewed (`pending`) submission waiting
for Adam — otherwise the same person spamming "submit" would pile up
duplicate entries in his New Requests queue. A phone written as `8 963 ...`
or `+7 963 ...` is treated as the same number for this check (and
everywhere else), since `8` is just the domestic dialing prefix for the same
`+7` country code. This does **not** block a phone that belongs to an
existing active member — that case is instead surfaced to Adam in New
Requests as a possible duplicate to merge, per section 5.3 of the brief.

### Math challenge on the public form

The public form also shows a plain-text arithmetic question (e.g. "сколько
будет 4 + 7?") that must be answered correctly before it submits, on top of
the invisible honeypot field. Like the honeypot, this is a lightweight
deterrent against generic/naive spam scripts, not a real CAPTCHA — a bot
written specifically to scrape and solve simple sums would get past it. The
correct answer is generated fresh per page load and stored server-side in
the session (`profile_captcha_answer`), never exposed to the client.

### A member's own status page, and a public community directory

Two more public, no-login pages exist beyond the join form:

- **`/m/{token}`** — a read-only page showing one member's own name, photo,
  and current subscription status, plus a "Написать Магомеду" WhatsApp button
  (using Adam's own phone number, per section 3 of the brief — this is the
  first place that number is actually surfaced to a member). There's no
  login and no lookup by name/phone; the only way to reach a specific
  member's page is to already have their link. Every member gets a random,
  unguessable 32-character `public_token` automatically the moment their
  row is created (`Member::booted()`), and `Member::publicUrl()` builds the
  full link from it. Adam can copy a member's link from their admin page
  (`members.show`) — a "Личная ссылка участника" field with a copy button —
  to send it to them once they're approved. There is deliberately no
  "browse all members and pick one" page anywhere that links out to
  individual `/m/{token}` pages — that would let anyone crawl and discover
  every member's supposedly-private link, defeating the point.
- **`/community`** — a public grid of every currently-active member's name
  and photo (a colorful initial avatar if they have no photo), with a "join
  us" call to action at the bottom linking to `/profile`. This page shows
  **no subscription/payment status at all**, on purpose: it's a community
  showcase for a Russian-language gym app, not a "who's paid" leaderboard —
  publicly broadcasting who has or hasn't paid felt like the wrong call
  even though the brief doesn't say either way. Pending and archived
  members never appear here. Each card links to that member's public
  profile (below).
- **`/u/{username}`** (or **`/u/{id}`** until a username is set) — a short,
  shareable public profile with only the member's name, photo, and "member
  since" month. Like `/community`, it never shows payment status, which is
  why it's safe for this address to be short and guessable. The member picks
  their username themselves on their private `/m/{token}` page (Latin
  letters, digits, `_`; 3–30 characters; not all digits so it can't clash
  with an id; unique, case-insensitive). Once a username is set, `/u/{id}`
  redirects to it. Only active members have a public profile.

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`)
and never touch your local MySQL data.

## Deploying to Timeweb

Prerequisites on the server: PHP 8.3+ with the **`gd`** and **`exif`**
extensions enabled (the `gd`/`exif` functions in
`app/Services/PhotoUploadService.php` use them directly to resize and
re-encode member photos — there's no Composer package covering this, so
check the hosting panel's PHP extension list if uploads ever fail).

1. Clone the repository into the project directory on the server (SSH):
   ```bash
   git clone https://github.com/groznet/crossfitgrozny.git .
   ```
   (run inside the empty directory the hosting panel created for the site;
   for updates later, `git pull` from the same directory).
2. Install PHP dependencies for production:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Many shared-hosting panels (including Timeweb) fix a site's document
   root to a folder literally named `public_html`, which can't be pointed
   at Laravel's `public/` directory directly. Rather than renaming
   `public/` (which would fight with future `git pull`s), symlink it:
   ```bash
   ln -s public public_html
   ```
   `bootstrap/app.php` auto-detects whichever of `public_html`/`public`
   exists and points Laravel's public-path resolution at it, so
   `storage:link`, asset URLs, etc. all resolve correctly either way. Then
   set the site's document root, in the hosting panel, to this
   `public_html` folder.
4. Create `.env` on the server (copy from `.env.example`) and set:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://anydomain.ru` (or `https://crossfitgrozny.ru` once the
     production domain is live — this is the **only** line that needs to
     change when moving domains; nothing else in the codebase references the
     domain)
   - `APP_KEY` — generate one if you didn't copy an existing key:
     `php artisan key:generate --force`
   - `DB_*` — the MySQL credentials Timeweb provides for your database
     (double-check `DB_USERNAME` and `DB_HOST` in the hosting panel's
     database page — the username isn't always identical to the database
     name, and the host isn't always `localhost`)
5. Run migrations and seed the initial admin account and prices:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=AdminUserSeeder --force
   php artisan db:seed --class=PlanPriceSeeder --force
   ```
   (Skip `MemberDemoSeeder` in production — it's only for local visual
   testing.)
6. Link storage so uploaded member photos are servable:
   ```bash
   php artisan storage:link
   ```
7. Cache config/routes/views for performance:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
8. Make sure the PHP-FPM user can write to `storage/` (including
   `storage/app/public/photos`) and `bootstrap/cache/`:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

### Updating a live deployment

```bash
git pull
composer install --no-dev --optimize-autoloader   # only if composer.lock changed
php artisan migrate --force                        # only if new migrations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Assumptions

Where the product brief (`CLAUDE.md`) left something unspecified, this build
chose the simplest option and records it here:

- **`members.phone` is not a unique database column**, even though section 7
  of the brief lists it as unique. A pending submission (from the public
  form) must be allowed to share a phone number with an existing active
  member — that's exactly the case section 5.3 asks us to flag as a possible
  duplicate. `phone` is indexed for lookup speed, and uniqueness is only
  checked at the application level (when approving/merging a request).
- **Rejecting a pending request permanently deletes it.** The data model has
  no `rejected` status, so there's nowhere else to keep it.
- **Editing or deleting a payment does not recompute other payments'
  `valid_until`.** Only the payment being changed is recalculated against
  its siblings; this keeps the mental model simple at the cost of not
  cascading a backdated correction through later payments (a rare edge case
  for this gym's workflow).
- **Payments can't be backdated into the future** (`paid_at` must be today or
  earlier).
- **Adam's login session uses Laravel's "remember me" cookie** rather than a
  very long `SESSION_LIFETIME`, to satisfy "keep the session long" without
  weakening session expiry for any other reason it might matter.
- **Member/profile photos are resized and compressed with PHP's built-in GD
  extension**, not a third-party image library, since GD alone is enough for
  "resize to a sane size and re-encode as JPEG" and the brief prefers few
  dependencies.
- **Archived members are excluded from the main list by default** and are
  reachable via a "show archive" toggle (`?archived=1`), rather than being
  shown inline with a different badge.
- **The admin account reuses Laravel's stock `users` table**, with
  `username` and `phone` columns added, rather than pulling in a full auth
  starter kit — there's only ever one admin in this MVP.
- **A photo uploaded during the public form is stored immediately**, before
  phone verification completes, so its path can be stashed alongside the
  rest of the pending submission (an `UploadedFile` can't be kept across
  requests any other way). If the visitor never verifies their code, that
  photo file is orphaned on disk — the submission never becomes a `Member`
  row, but the file isn't cleaned up automatically. Low-volume/low-cost
  enough to leave as a manual `storage/app/public/photos` cleanup if it ever
  matters.
- **The OTP code, its expiry, and the stashed form data all live in
  Laravel's cache** (`profile-otp:{phone}`, 5-minute TTL), not a database
  table — there's no audit trail of verification attempts, which is fine for
  a low-volume, low-stakes intake form.
