# FarazSMS OTP Integration Notes

This project uses FarazSMS / IranPayamak for passwordless mobile login OTP messages.

## Official docs

- Main docs: https://docs.farazsms.com/
- Pattern SMS endpoint: https://docs.farazsms.com/service/post-ws-v1-sms-pattern

## Recommended endpoint for OTP

Use pattern-based SMS, not simple SMS.

```http
POST https://api.iranpayamak.com/ws/v1/sms/pattern
```

FarazSMS documents this endpoint as the fastest path for OTP and transactional template messages.

## Authentication

Send the API key in the request header:

```http
Api-Key: <api-key-from-farazsms-panel>
```

The API key is case-sensitive and should only be stored in environment variables.

## Required request fields

```json
{
  "code": "PATTERN_UID",
  "attributes": {
    "code": "123456"
  },
  "recipient": "09120000000",
  "line_number": "50002178584000",
  "number_format": "english"
}
```

Fields:

- `code`: FarazSMS pattern UID from the panel.
- `attributes`: variables used by the approved pattern.
- `recipient`: target mobile number, for example `09120000000`.
- `line_number`: sender line number accessible in the FarazSMS panel.
- `number_format`: `english` or `persian`; use `english` for OTP unless product decides otherwise.
- `schedule`: optional; for OTP it should normally be omitted or null.

Important: `attributes` keys must exactly match the variables configured in the FarazSMS pattern. If the pattern variable is named `var1`, send `{"var1": "123456"}`. If it is named `code`, send `{"code": "123456"}`.

## Suggested OTP pattern text

The exact variable syntax is configured in the FarazSMS panel. Keep the message short.

```text
کد ورود شما به سامانه خدمات برووات: %code%
```

## Suggested Laravel environment keys

```env
FARAZSMS_API_KEY=
FARAZSMS_BASE_URL=https://api.iranpayamak.com
FARAZSMS_LINE_NUMBER=
FARAZSMS_OTP_PATTERN_CODE=
FARAZSMS_OTP_ATTRIBUTE=code
FARAZSMS_NUMBER_FORMAT=english

OTP_TTL_MINUTES=2
OTP_MAX_ATTEMPTS=5
OTP_RESEND_SECONDS=60
```

## Suggested Laravel abstraction

Keep SMS delivery behind an application interface so authentication and warranty logic do not depend directly on FarazSMS.

```php
interface SmsProvider
{
    public function sendOtp(string $mobile, string $code): void;
}
```

Suggested implementation:

```php
final class FarazSmsProvider implements SmsProvider
{
    public function sendOtp(string $mobile, string $code): void
    {
        // POST /ws/v1/sms/pattern with Api-Key header.
    }
}
```

## OTP flow

```text
User enters mobile
-> normalize and validate mobile
-> enforce rate limits by mobile and IP
-> generate numeric OTP
-> store only hashed OTP in database
-> send OTP through FarazSMS pattern endpoint
-> user submits OTP
-> verify hash, expiry, used_at, and attempts
-> mark OTP as used
-> create or login user
```

## Security rules

- Never store OTP codes in plain text.
- Keep OTP lifetime short, for example 2 minutes.
- Limit resend frequency, for example once per 60 seconds per mobile.
- Limit verification attempts, for example 5 attempts.
- Mark OTP records as used after successful verification.
- Rate limit requests by both mobile number and IP address.
- Do not expose whether a mobile number is already registered.
- Log provider failures without logging OTP codes or API keys.

## Suggested database table

```text
otp_codes
- id
- mobile
- code_hash
- expires_at
- used_at nullable
- attempts default 0
- ip_address nullable
- user_agent nullable
- created_at
- updated_at
```

## Useful FarazSMS endpoints for setup/debugging

- `GET /ws/v1/account/balance`: low-risk API key test.
- `GET /ws/v1/lines/accessible`: list available sender lines.
- `GET /ws/v1/patterns`: list account patterns and find pattern `code`.
- `GET /ws/v1/patterns/{code}`: inspect a pattern and its variables.

