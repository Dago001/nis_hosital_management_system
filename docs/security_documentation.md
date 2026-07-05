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
