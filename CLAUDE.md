<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
# Grozny Gym — CrossFit Member Tracker

> **Project brief for AI coding agents and humans.**
> Status: ready to start MVP. Read the whole file before writing code.
> Section 8 defines the stack. Section 10 lists the build instructions. Section 11 lists what is still undecided.

---

## 1. One-line summary

A mobile-first, Russian-language web app where the CrossFit trainer **Adam** keeps a list of his CrossFit members and sees, at a glance, who has paid, whose subscription is about to expire, and who has lapsed.

---

## 2. Background

### 2.1 The gym
- **Grozny Gym** has two zones under one subscription:
  - **Weightlifting hall** — members train alone or with other trainers. **Not part of this app.**
  - **CrossFit zone** — the CrossFit program led by Adam. **This app is only for this program.**
- The program includes CrossFit, strength, and explosive training.
- Members may train any day, Monday–Sunday, while their subscription is active.

### 2.2 How payment works today
- Members pay at the **front desk** at the entrance and receive an **access card** for the door scanner.
- The scanner stops opening the door once the subscription expires.
- Payment types:

| Type | Price | Notes |
|---|---|---|
| Per visit | 400 RUB | Less common, but must be tracked |
| Monthly — daytime | 2,500 RUB | Training before 17:00 only |
| Monthly — evening | 3,500 RUB | Any time, including after 17:00 |
| Yearly | 25,000 RUB | Some members use it |

Most members pay **monthly**. **All prices must be editable by the admin** (see 5.6); these are starting values.

### 2.3 The problem
- The front desk and card system know who paid. **Adam does not.**
- Members are told to tell Adam in person after paying. Many forget.
- Adam's only member list is a **WhatsApp group**. He removes people he thinks have not paid, which sometimes removes people who did pay.
- Nobody else tracks who is in the CrossFit program.

### 2.4 Out of scope
- The daily workout program. It is written on a board in the gym and works fine.
- Weightlifting-only members.
- Online payments.
- Integration with the door/card system. The app must work fully on its own, with no dependency on the gym's card software.

---

## 3. Users and roles

| Role | Access | What they do |
|---|---|---|
| **Admin (Adam)** | Logs in | Manages members, records payments, approves new profiles |
| **Member** | No login | Fills a public profile form via a link (see 5.3) |

- Only one admin account in the MVP.
- Adam's contact: **+7 963 989-20-11**. Use it for the admin account setup and as the WhatsApp contact shown to members.

---

## 4. Core workflow

1. A member pays at the front desk.
2. The member tells Adam (in person or via WhatsApp).
3. Adam opens the app and records the payment in a few taps.
4. The app calculates the expiry date and updates the status.
5. Adam checks the list to decide who stays in the WhatsApp group and who to remind.

New members:
1. Adam shares a link to the public profile form (e.g. in the WhatsApp group).
2. The member fills in basic info.
3. The profile appears in Adam's "New requests" list.
4. Adam approves it, and it joins the member list.

---

## 5. MVP features

### 5.1 Member list (main screen)
- Show every approved member with:
  - Name
  - Photo (if provided)
  - Plan type
  - Expiry date
  - Status badge
- Status rules (derived from the latest payment):

| Status | Rule | Colour |
|---|---|---|
| Активен (Active) | Expires in more than 5 days | Green |
| Скоро истекает (Expiring soon) | Expires in 0–5 days | Yellow |
| Просрочен (Expired) | Expiry date has passed | Red |
| Нет оплат (No payments) | No payment recorded | Grey |

- Default sort: expired and expiring members first.
- Filter by status.
- Search by name.
- Counters at the top, e.g. "Active: 34 · Expiring: 5 · Expired: 8".

### 5.2 Record a payment
- Opened from a member's card, in two taps or fewer.
- Fields:
  - Plan type (per visit / monthly daytime / monthly evening / yearly)
  - Payment date (default: today)
  - Amount (pre-filled from plan price, editable)
- Expiry is calculated automatically:
  - Monthly: payment date + 1 calendar month
  - Yearly: payment date + 1 calendar year
  - Per visit: valid for that day only
- A new payment made before expiry extends from the current expiry date, not from today.
- Payment history is shown on the member's page.
- Adam can edit or delete a payment to fix mistakes.

### 5.3 Public member profile form (no login)
- Accessible from one shareable link.
- Fields:
  - Full name (required)
  - Phone / WhatsApp (required)
  - Photo (optional) — helps Adam recognise who is who
  - Date of birth (optional)
  - Preferred training time: daytime / evening (optional)
  - Short note, e.g. experience level or goals (optional)
- Submissions go to a **"New requests"** list. They are not shown as members until Adam approves them.
- Adam can approve, edit, merge with an existing member, or reject a submission.
- Add a simple honeypot field against spam. Do not use a captcha.
- If a phone number already exists, flag it as a possible duplicate.

### 5.4 Member page (admin)
- Profile info, payment history, current status.
- A WhatsApp button that opens a chat with the member via a `wa.me` link.
- Edit and archive actions. Archiving hides the member without deleting their data.

### 5.5 Admin login
- Simple login for one admin: phone or username with a password.
- Keep the session long so Adam is not logged out often.

### 5.6 Settings
- Admin can edit the price of each plan.
- A price change applies only to **new** payments. Past payments keep the amount they were recorded with.
- Default prices (seed values): visit 400, monthly daytime 2,500, monthly evening 3,500, yearly 25,000 (RUB).

---

## 6. Later versions (do not build now)

- **V2 — Competitions.** The gym holds a competition every 1–2 months with awards. Record the event, date, workout, participants, results, and winners, and keep an archive.
- **Personal records** per member (lifts, benchmark workouts).
- **Workout leaderboard** for daily workout times.
- **Expiry reminders** to members via WhatsApp.
- **Member self-reported payment.** A member presses "I paid" and Adam confirms it.
- **Card system integration**, if the gym's software allows it.
- **Export to Google Sheets / CSV** of members and payments, for backups and reporting.

---

## 7. Data model

```
Member
  id
  full_name          string, required
  phone              string, required, unique (E.164, e.g. +79631234567)
  photo_url          string, nullable
  birth_date         date, nullable
  preferred_time     enum: day | evening | null
  note               text, nullable
  status             enum: pending | active | archived   -- pending = awaiting approval
  created_at, updated_at

Payment
  id
  member_id          FK -> Member
  plan               enum: visit | month_day | month_evening | year
  amount             integer (RUB)
  paid_at            date
  valid_until        date   -- calculated, stored
  created_at

PlanPrice (editable by admin)
  plan               enum: visit | month_day | month_evening | year
  price              integer (RUB)
  updated_at
```

Subscription status (active / expiring / expired / none) is **computed** from the latest `Payment.valid_until`. It is not stored.

---

## 8. Tech stack and hosting

| Layer | Choice |
|---|---|
| Backend | **Laravel** (PHP), latest stable |
| Templates | **Blade** (server-rendered pages, no SPA) |
| Styling | **TailwindCSS via CDN** (Play CDN script in the Blade layout) — light, simple, readable |
| Interactivity | **Alpine.js** for small UI behaviour (modals, filters, toggles) |
| Custom CSS/JS | Only when Tailwind/Alpine can't cover it |
| Database | **MySQL** |
| Hosting | **Timeweb** (dynamic PHP hosting) |
| Domain | Testing: **anydomain.ru** · Production later: **crossfitgrozny.ru** |
| Auth | Laravel's built-in session auth. No heavy starter kits unless needed |
| File storage | Member photos on local Laravel storage (`storage/app/public`). Resize and compress on upload |

Rules:
- Do **not** use React, Vue, Livewire, or Inertia. Keep it Blade + Alpine.
- Load Tailwind and Alpine.js from **CDN** in the main Blade layout. No Vite, no npm build step, no Node.js needed anywhere for now.
- Use Laravel migrations and seeders for the schema and the default prices.
- Use Laravel validation and form requests for all input, including the public form.
- The app must run on standard shared/VPS PHP hosting at Timeweb. Avoid queues, websockets, or anything needing extra daemons in the MVP.

---

## 9. UI and technical requirements

- **Interface language: Russian only.** All labels, messages, and dates must be in Russian. Use the Russian date format DD.MM.YYYY.
- Store all UI strings in one place, so another language can be added later.
- **Mobile-first.** Adam will use the app on his phone in the gym. Use large tap targets and one-hand-friendly layouts.
- **Styling:** light, simple, and readable. No heavy shadows or decorative animations.
- **Timezone:** Europe/Moscow.
- **Currency:** RUB, shown as "3 500 ₽".
- Make prices configurable from a settings screen, not hard-coded.
- Security: keep it simple at this stage. Admin pages sit behind login, and the public form can only submit data. Nothing beyond Laravel's defaults (CSRF, validation, hashed password) is needed.
- Keep the app small. Prefer few dependencies and a simple deployment.

---

## 10. Instructions for the building agent

1. Build **only section 5 (MVP)**. Do not build section 6.
2. Suggested build order:
   1. Data model and database
   2. Admin login
   3. Member list with statuses
   4. Record payment
   5. Member page
   6. Public profile form and "New requests"
   7. Settings (prices)
3. Seed the database with 10 fake members in mixed statuses so the list can be tested visually.
4. Write a short README covering how to run the app locally and how to deploy it to Timeweb (upload, `.env`, migrations, `storage:link`).
5. Keep the domain in `.env` (`APP_URL`) so moving from anydomain.ru to crossfitgrozny.ru needs no code changes.
6. When something is unclear, choose the simplest option and note it in the README under "Assumptions".

---

## 11. Still open

Nothing blocks the MVP. Deferred for later:
- Which software the front desk / card system uses. Unknown; the app must not rely on it.
