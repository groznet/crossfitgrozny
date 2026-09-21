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

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`)
and never touch your local MySQL data.

## Deploying to Timeweb

1. Upload the repository to the server (via git or SFTP), excluding
   `vendor/`, `node_modules/`, and `.env`.
2. On the server, install PHP dependencies for production:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Create `.env` on the server (copy from `.env.example`) and set:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://anydomain.ru` (or `https://crossfitgrozny.ru` once the
     production domain is live — this is the **only** line that needs to
     change when moving domains; nothing else in the codebase references the
     domain)
   - `APP_KEY` — generate one if you didn't copy an existing key:
     `php artisan key:generate --force`
   - `DB_*` — the MySQL credentials Timeweb provides for your database
4. Run migrations and seed the initial admin account and prices:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=AdminUserSeeder --force
   php artisan db:seed --class=PlanPriceSeeder --force
   ```
   (Skip `MemberDemoSeeder` in production — it's only for local visual
   testing.)
5. Link storage so uploaded member photos are servable:
   ```bash
   php artisan storage:link
   ```
6. Cache config/routes/views for performance:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. Point the web server's document root at the `public/` directory.
8. Make sure the PHP-FPM user can write to `storage/` and
   `bootstrap/cache/`.

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
