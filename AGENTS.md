# AGENTS.md

## Project Context

Browatt is a manufacturer of products such as evaporative coolers and electric heaters.

Browatt does not sell directly to retail customers. Products are distributed through representatives/dealers, and customers buy from those channels.

This project is a customer-facing after-sales service portal. The long-term scope may include:

- Warranty activation
- Active warranty lookup
- Repairs and service requests
- Customer questions/support
- Integration with internal/accounting systems

The first implementation phase is warranty activation only.

Even though the first implementation phase is small, the system is expected to grow soon. Prefer an architecture that can absorb additional after-sales modules without broad refactors.

## Current Product Scope

Customers should be able to:

1. Enter a mobile number.
2. Receive an OTP by SMS.
3. Verify the OTP and log in without a password.
4. See a warranty menu.
5. Use warranty activation.
6. View active warranties.
7. Open the installation training videos for 24,000, 28,000, and 32,000 evaporative cooler models from the dashboard in a new browser tab.

Initial menu structure:

```text
Warranty
- Activate Warranty
- Active Warranties
```

## Technology Decisions

- The project will be PHP-based.
- Laravel 12 is the selected backend framework for the initial project setup.
- Use passwordless authentication with mobile OTP.
- Use Inertia for the frontend bridge.
- Use action-oriented application code with `lorisleiva/laravel-actions`.
- Use `nwidart/laravel-modules` for module boundaries. Project modules live under the `Browatt/` namespace and directory.
- Use `spatie/laravel-permission` for roles and permissions.
- Media/file management is deferred until the project has a concrete upload requirement. Do not install a media-library package prematurely.
- Use `hekmatinasser/verta` for Jalali dates.
- Use `maatwebsite/excel` for spreadsheet import/export.
- Use `laravel/telescope` for local/debug observability.
- Use `tightenco/ziggy` for Laravel route access from Inertia frontend code.
- Use Svelte 5 as the Inertia frontend adapter.
- Use Tailwind CSS v4 for styling.
- Use `shadcn-svelte` as the UI component base, with local/customized components rather than a heavy fixed UI kit.
- Use `@humanspeak/svelte-motion` for meaningful Svelte 5 UI motion such as menu feedback, page/card entrance, hover/tap micro-interactions, and future layout transitions. Keep motion purposeful and respect usability; do not add distracting animations to operational workflows.
- Use `@majidh1/jalalidatepicker` behind shared Svelte wrappers for Jalali date inputs. Do not couple pages directly to datepicker globals.
- The official `shadcn-svelte` agent skill is installed under `.agents/skills/shadcn-svelte` for working with shadcn-style Svelte components, registries, and composition rules. Use `npx shadcn-svelte@latest` / the project package runner and the local `components.json` aliases for implementation. Do not introduce React-specific APIs, `lucide-react`, Radix React assumptions, or `npx shadcn@latest` generated React components into this Svelte project.
- Use `https://www.shadcn-svelte.com/llms.txt` as the quick official index for current `shadcn-svelte` docs and available components when deciding whether an existing component or pattern should be used before building custom UI.
- Do not install Livewire.
- Use MySQL or MariaDB unless a later requirement justifies another database.
- Tests are not required for now unless the user explicitly asks for them.

Suggested stack:

```text
PHP 8.3+
Laravel 12
Inertia
Svelte 5
Tailwind CSS v4
shadcn-svelte
Laravel Actions
nwidart/laravel-modules
Spatie Permission
Verta
Laravel Excel
Telescope
Ziggy
MySQL/MariaDB
Redis for queue/cache if needed
```

## Engineering Quality

This project should feel like it is maintained by a professional engineering team under clear software architecture leadership. Prefer consistency, clear boundaries, and incremental growth so future features require less refactoring.

Engineering rules:

- Prefer the simplest sufficient solution. Refactor or add dependencies only when there is a clear need.
- Keep classes, functions, modules, and services focused on one responsibility.
- Keep implementation decisions consistent across the project so new features do not introduce competing patterns.
- Before creating a new file, find the closest existing example in the project and follow its namespace, naming, structure, and style.
- Use `spatie/laravel-permission` for roles and permissions. Do not implement custom role/permission systems.
- Introduce media/file handling only when a concrete feature requires it, and select the simplest package or focused service that satisfies that requirement.
- Keep external integrations behind interfaces or focused service classes so domain logic does not couple directly to vendor clients.
- Application settings stored in `app_settings.value` are plain JSON for easier operational inspection and manual edits. Do not use encrypted casts for `App\Models\AppSetting`.
- If a project decision, workflow, technology, or implementation direction changes, update this `AGENTS.md` in the same change.

## Local Development Database

The local Herd URL is:

```text
http://Browatt-Support.test
```

The local development database is MySQL/MariaDB:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=browatt
DB_USERNAME=root
DB_PASSWORD=root
```

## Production Deployment

Production is hosted on cPanel with the following deployment contract:

- Production URL: `https://support.browatt.com`
- `public/.htaccess` redirects production HTTP and `www.support.browatt.com` requests to the canonical HTTPS URL so secure session/CSRF cookies stay on one origin.
- cPanel user: `browattc`
- Laravel application root: `/home/browattc/support.browatt.com`
- Subdomain document root: `/home/browattc/support.browatt.com/public`
- The main Joomla site remains separate under `/home/browattc/public_html`.
- PHP 8.4 binary: `/usr/local/bin/ea-php84`
- Composer binary: `/usr/local/bin/composer`
- `.cpanel.yml` is the source of truth for cPanel deployment tasks.
- Production migrations run automatically with `artisan migrate --force` unless the deployment environment explicitly sets `MIGRATE=0`.
- `Database\Seeders\RoleSeeder` is run manually during the initial installation, not on every deployment.
- Frontend assets must be built locally and committed under `public/build`. Never run `npm install`, `npm ci`, or `npm run build` on the production server.
- A missing `public/build/manifest.json` is a deployment error and must be fixed by building and committing assets locally.
- Route caching is intentionally skipped while `routes/web.php` contains Closure-based routes; `artisan optimize:clear` still clears any stale route cache during deployment.
- Never use destructive database commands such as `migrate:fresh`, `db:wipe`, or reset-style seeding during production deployment.
- Production requires PHP's `soap` extension for MehrSoft synchronization.
- The production `.env` must exist at `/home/browattc/support.browatt.com/.env` before the first cPanel deployment and must never be committed.
- `public/.htaccess` includes the cPanel-managed PHP error-log directives for `/home/browattc/logs/php.error.log`; preserve those directives during deployments.
- `public/.user.ini` and `public/php.ini` are generated by cPanel and remain untracked through `.gitignore`.
- A root-level `error_log` may be generated by cPanel/PHP and remains untracked through `.gitignore`.
- A root-level `FETCH_HEAD` may be generated by cPanel Git operations and remains untracked through `.gitignore`; Git's actual fetch metadata stays under `.git/FETCH_HEAD`.
- Deployment permissions are `775` for directories and `664` for files under `storage` and `bootstrap/cache`; do not recursively mark files executable because that dirties tracked `.gitignore` placeholders.

The deployment order is maintenance mode (for an existing install), production Composer install, package discovery, writable directory and storage-link setup, non-destructive migrations, cache clearing, production cache rebuild, queue restart, and maintenance mode off. Migrations run before `optimize:clear` because the default database cache table does not exist until the initial migrations have run. On the initial install, maintenance mode is skipped until Composer has created `vendor/autoload.php`, because Artisan is not available before then. The deployment script uses an exit trap so the application is brought back online if a later deployment task fails after maintenance mode starts.

## Access Control

Initial roles:

- `general_manager`: a small number of global managers.
- `customer`: normal customer users.

Use `spatie/laravel-permission` for role assignment and permission checks. The `App\Models\User` model uses `HasRoles`.

Seed the initial roles with `Database\Seeders\RoleSeeder`. The initial `general_manager` users are:

- `09144004385`: مهدی مکاریان
- `09900940019`: پرویز الیاس زاده
- `09148064984`: مهرداد مهردادی
- `09146585966`: مجید نوروزی

## Frontend Direction

Most users are expected to access the system from mobile devices. Frontend work must be mobile-first, touch-friendly, RTL-friendly, and fully responsive.

The application is Persian-first and RTL by default. Use `fa` locale defaults, `dir="rtl"` markup, and the bundled YekanBakh font as the default UI font.

Final frontend direction:

```text
Inertia + Svelte 5 + Tailwind CSS v4 + shadcn-svelte
```

Do not install Vue, React, Ionic, PrimeVue, shadcn-vue, daisyUI, Metronic, or another UI framework unless the user explicitly changes this direction.

Frontend production assets are built locally and committed under `public/build` before deployment. The production server is not expected to run `npm install` or `npm run build`.

Frontend structure should follow the broad folder pattern reviewed from `D:\Herd\Brandiol-Automation`: shared Svelte components live under `resources/js/Components`, Inertia pages live under `resources/js/Pages`, and page groups start with `Auth` and `Dashboard`. Use this as a structural reference only; do not copy Brandiol-specific UI code or Metronic assumptions into Browatt.

## Frontend / UI Quality

The UI should stay consistent with the existing project design, not drift into page-by-page styles.

Frontend rules:

- UI must follow the existing project patterns. Do not give each page its own unrelated visual style.
- Before building custom UI, first check whether the selected framework, UI library, or installed component registry already provides the needed component or behavior. For example, use `shadcn-svelte` components such as `InputOTP` for OTP entry when suitable. Build a custom component only when the existing option is unavailable, insufficient for the required UX, or would create worse integration/maintenance tradeoffs.
- Before building a new page, find the closest existing page or component and use it as the template for structure, naming, layout, and interaction patterns.
- Forms, actions, feedback, and navigation must be designed for operational use: clear, low-error, touch-friendly, and easy to scan.
- Responsive behavior must be intentional and polished across mobile, tablet, and desktop, not merely non-broken.
- For list pages, prefer a datatable pattern with server-side pagination, sorting, and filtering.
- Table columns should be short, useful, and scan-friendly. Prefer identifiers, titles, statuses, owner/operator fields, dates, and compact summary values.
- Keep action buttons and state feedback consistent across pages.
- If frontend files change, build frontend assets locally and commit the updated `public/build` output with the same change.

## SMS / OTP

The SMS provider is FarazSMS / IranPayamak.

Reference file:

- `ForGPT/farazsms-otp.md`

Use the FarazSMS pattern endpoint for OTP messages:

```text
POST https://api.iranpayamak.com/ws/v1/sms/pattern
```

OTP implementation rules:

- In production, store only hashed OTP codes.
- In non-production environments, do not send real SMS by default. Store the generated OTP in `otp_codes.debug_code` only for local/debug use so developers can read it from the database. In production, `OTP_SEND_SMS` must be true and `OTP_STORE_DEBUG_CODE` must be false so only `code_hash` is stored.
- Keep OTP lifetime short, for example 2 minutes.
- Limit resend frequency.
- Limit failed verification attempts.
- Mark OTP records as used after successful verification.
- Rate limit by both mobile number and IP address.
- Do not expose whether a mobile number already exists.

OTP implementation lives in `App\Services\Otp\OtpService`. SMS delivery is behind `App\Contracts\SmsProvider`; the default provider is selected by `OTP_SMS_PROVIDER`, currently `farazsms`. FarazSMS delivery lives in `App\Services\Sms\FarazSmsProvider` and uses the pattern endpoint from `ForGPT/farazsms-otp.md`.

After a warranty is successfully activated and MehrSoft synchronization is marked `synced`, send the transactional activation pattern through `App\Services\Warranty\WarrantyActivationSmsService` and the shared `SmsProvider`. Register the send with Laravel's `defer` helper so it runs immediately after the HTTP response without a queued job and the customer does not wait for the SMS provider. SMS delivery failure must never roll back or change an already successful warranty activation. Configure the activation pattern attributes independently from the OTP pattern: `ptitle` is the product title, `pserial` is the product serial, and `wdate` is the Jalali warranty expiry date in `Y/m/d` format. Keep the feature disabled until the pattern is approved and configured.

When adding another SMS panel, add a new `SmsProvider` implementation and update the binding in `App\Providers\AppServiceProvider`; do not couple OTP, auth, or warranty code directly to a vendor client.

## MehrSoft Integration

Warranty activation must integrate with MehrSoft accounting / after-sales web service.

Reference file:

- `ForGPT/mehrsoft-webservice.md`

MehrSoft service URLs:

```text
https://www.mehrsofts.com/webservice/mehraccws.asmx
https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL
```

MehrSoft exposes an ASP.NET ASMX web service. Treat the WSDL/SOAP contract as the source of truth.

The MehrSoft integration requires PHP's `soap` extension to be enabled in the runtime that performs synchronization.

Likely warranty-related methods:

- `Login`
- `Logout`
- `AfterSales_GetProductStatusBySerial`
- `AfterSales_GetWarrantyMonths`
- `AfterSales_GetWarrantySettings`
- `AfterSales_Save`

The full method reference is documented in `ForGPT/mehrsoft-webservice.md`.

## Integration Design Rules

Keep external services behind application interfaces. Do not couple controllers, Livewire components, or domain logic directly to FarazSMS or MehrSoft implementation details.

Suggested interfaces:

```php
interface SmsProvider
{
    public function sendOtp(string $mobile, string $code): void;

    public function sendPattern(string $mobile, string $patternCode, array $attributes): void;
}
```

```php
interface MehrSoftClient
{
    public function getProductStatusBySerial(string $serial): array;

    public function getWarrantyMonths(string $warrantyType, string $goodFullCode): ?int;

    public function getWarrantySettings(): array;

    public function saveAfterSales(array $payload): array;

    public function logout(): void;
}
```

The current MehrSoft integration lives in the `Browatt/MehrsoftIntegration` module. It follows the modular pattern reviewed from `D:\Herd\Brandiol-Automation`, but it must stay Browatt-specific and expose only the MehrSoft after-sales methods needed by this project.

For final MehrSoft warranty activation failures, do not mark the warranty as activated and do not show it in active warranty lists. Keep the inquired local row available for retry, store the failed sync status/error for diagnostics, and show the customer an inline retry error. After more than two final activation failures for the same user/warranty/IP, show the support phone link `tel:02191693797`.

## Initial Data Model Direction

Likely core tables:

```text
users
- id
- mobile
- first_name nullable
- last_name nullable
- registered_at nullable
- deleted_at nullable

user_details
- id
- user_id
- key
- value json nullable
- deleted_at nullable

user_addresses
- id
- user_id
- title nullable
- province nullable
- city nullable
- address nullable
- is_default
- deleted_at nullable

otp_codes
- id
- mobile
- code_hash
- debug_code nullable, local/debug only
- expires_at
- used_at nullable
- attempts
- ip_address nullable
- user_agent nullable
- deleted_at nullable

warranties
- id
- user_id
- product_serial
- product_code nullable
- warranty_type nullable
- warranty_period_months nullable
- activated_at
- starts_at nullable
- expires_at nullable
- mehrsoft_sync_status
- mehrsoft_synced_at nullable
- mehrsoft_document_no nullable
- mehrsoft_fix_no nullable
- mehrsoft_last_error nullable
- deleted_at nullable
```

User profile data is split into general and detailed data. General fields that are shown frequently belong on `users` (`mobile`, `first_name`, `last_name`, `registered_at`). Large or evolving profile data such as national code, province/city preferences, and other detailed attributes should live in `user_details` as key/value JSON records. User addresses are separate records in `user_addresses` because each user can define multiple addresses.

Application-owned Eloquent models should use soft deletes by default. Nothing customer-facing or operational should be permanently deleted unless a future explicit requirement says otherwise.

## Open Business Questions

- What exact customer-entered identifier activates warranty: product serial, warranty code, QR code, or a combination?
- Should warranty start from activation date, purchase date, installation date, or production date?
- Should activation be rejected if MehrSoft is unavailable, or accepted locally as pending sync?
- What should happen if MehrSoft reports the serial already has after-sales/warranty data?
- Which MehrSoft `AfterSales_Save` fields are mandatory for Browatt?
- What exact values should be sent for `TypeTitle`, `Flag`, account codes, warranty type, and detail rows?
- Should customer records also be created/updated as MehrSoft tafsil accounts?

## Repo Notes

- Keep integration documentation in `ForGPT/`.
- Do not keep large raw HTML service exports if a complete Markdown reference exists.
- If any project decision, technology choice, workflow, integration detail, or implementation direction changes, update this `AGENTS.md` file in the same change so future agents follow the current project direction.
- When replying in Persian/Farsi in Codex chat, start each Persian paragraph with an RTL mark (`‫`) so mixed Persian, English words, and numbers render correctly.
