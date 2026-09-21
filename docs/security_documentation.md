# Security & MFA Documentation

This document explains the security measures, policies, and authorization matrices implemented in the NIS HMS.

## Security Controls

1. **Role-Based Access Control (RBAC)**: Enforced via the `CheckRoleOrPermission` middleware. Users are mapped to roles, and roles possess explicit permissions.
2. **Multi-Factor Authentication (MFA)**: Built-in TOTP/OTP generation and validation via `MfaService`. Once enabled, logins yield a redirect that triggers a 6-digit OTP passcode validation before yielding a Sanctum API bearer token.
3. **Audit Trail Logging**: Mutating HTTP requests (POST, PUT, PATCH, DELETE) are automatically intercepted by the `LogAuditAction` middleware. Sensitive variables like passwords are masked, and details (IP address, user-agent, user profile, and payload changes) are stored in the database.
4. **Data Protection**: Encryption of personal files and database integrity with secure password hashing via PHP's default secure Argon2id / bcrypt drivers.

## Permission Matrix Reference

| Module | Super Admin | Doctor | Nurse | Cashier | Lab Scientist | Records Officer |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **User Access** | ✓ | | | | | |
| **Demographics Registration**| ✓ | | | | | ✓ |
| **Vitals Capture** | ✓ | | ✓ | | | |
| **SOAP Consultations** | ✓ | ✓ | | | | |
| **Laboratory results** | ✓ | | | | ✓ | |
| **Billing Checkout** | ✓ | | | ✓ | | |
| **Logs View** | ✓ | | | | | |

## Application Hardening (Defense-in-Depth)

Layered controls that protect the platform against common web attacks:

### Injection & Data Integrity
- **SQL injection**: All persistence goes through Eloquent/Query Builder with parameter bindings. The few raw fragments (`whereRaw`/`selectRaw`) use `?` placeholders or hardcoded column literals only — no user input is ever concatenated into SQL.
- **Mass assignment**: Models declare explicit `$fillable`, and Eloquent runs in strict mode (`preventSilentlyDiscardingAttributes`, `preventAccessingMissingAttributes`).
- **XSS**: Blade auto-escapes all output. The `XssSanitizer` middleware additionally strips HTML/script markup, dangerous URI schemes, inline event handlers, null bytes and control characters from incoming request data (excluding credentials and free-form clinical narratives).

### Authentication & Brute-Force Resistance
- **Credential lockout**: The `login` rate limiter caps attempts at 5/min per email+IP and 20/min per IP. After repeated failures the account is locked out (HTTP 429) with a `Retry-After` window.
- **No user enumeration**: Login always performs a bcrypt verification (against a dummy hash for unknown emails) so response timing is constant, and both "unknown user" and "wrong password" return an identical message. The MFA endpoint returns the same generic error whether or not the account exists.
- **Strong passwords**: `Password::defaults()` enforces min-8, mixed case, numbers and symbols; production additionally requires the password not to appear in known-breach corpora (`uncompromised`).
- **Token lifetime**: Sanctum bearer tokens auto-expire (default 8h, `SANCTUM_TOKEN_EXPIRATION`).

### Transport & Session
- **HTTPS enforced**: In production all generated URLs are forced to `https` (`URL::forceScheme`), and HSTS is emitted over secure connections.
- **Secure cookies**: Production template sets `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `HttpOnly`, and `SameSite`.

### HTTP Response Headers (`SafeSecurityHeaders`)
- `Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `X-Permitted-Cross-Domain-Policies`, and HSTS on HTTPS.

### Abuse / DoS Throttling
- A global `api` rate limiter (120 req/min per authenticated user, else per IP) caps every API route. Public endpoints (login, portal lookup, chatbot, appointment requests) carry tighter per-route limits.

### File Upload / Malware Surface
- Uploads are validated by real (content-guessed) MIME **and** client extension, size-capped, and stored with hashed, non-guessable names.
- Patient documents live on a **private** (non-public) disk and are streamed through an authorized controller — never served directly.
- Executable / script extensions (`php`, `phtml`, `phar`, `exe`, `sh`, `svg`, `html`, double extensions, …) are rejected outright; stored display names are stripped of path components and control characters, and the server-guessed MIME type is persisted rather than the client-supplied header.
