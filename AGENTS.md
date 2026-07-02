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
- Use `spatie/laravel-medialibrary` for media/file management.
- Use `hekmatinasser/verta` for Jalali dates.
- Use `maatwebsite/excel` for spreadsheet import/export.
- Use `laravel/telescope` for local/debug observability.
- Use `tightenco/ziggy` for Laravel route access from Inertia frontend code.
- Use Svelte 5 as the Inertia frontend adapter.
- Use Tailwind CSS v4 for styling.
- Use `shadcn-svelte` as the UI component base, with local/customized components rather than a heavy fixed UI kit.
- Use `@humanspeak/svelte-motion` for meaningful Svelte 5 UI motion such as menu feedback, page/card entrance, hover/tap micro-interactions, and future layout transitions. Keep motion purposeful and respect usability; do not add distracting animations to operational workflows.
- The official `shadcn/ui` agent skill is installed under `.agents/skills/shadcn` for reference when working with shadcn-style components, registries, and composition rules. This project is `shadcn-svelte`, so treat React/TSX examples from that skill as conceptual guidance only. Do not introduce React-specific APIs, `lucide-react`, Radix React assumptions, or `npx shadcn@latest` generated React components into this Svelte project. Prefer `shadcn-svelte` documentation/CLI and the local `components.json` aliases for actual implementation.
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
Spatie Media Library
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
- Use `spatie/laravel-medialibrary` for media/file handling. Do not implement custom media management unless a specific requirement proves the package is insufficient.
- Keep external integrations behind interfaces or focused service classes so domain logic does not couple directly to vendor clients.
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

For MehrSoft failures, prefer storing the local warranty activation with a sync status such as `pending`, then retrying through a queue, unless business rules explicitly require rejecting activation when MehrSoft is unavailable.

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
- recipient_first_name nullable
- recipient_last_name nullable
- mobile nullable
- province nullable
- city nullable
- district nullable
- postal_code nullable
- address nullable
- latitude nullable
- longitude nullable
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
