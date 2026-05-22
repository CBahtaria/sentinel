# Security Policy

## Status

UEDF Sentinel v5.0 is currently in **pre-production hardening**. A structural
security audit was completed on 2026-05-21 — see
[`SECURITY-AUDIT-2026-05-21.md`](./SECURITY-AUDIT-2026-05-21.md) for the
full report.

**This system MUST NOT be deployed to production until all Critical and High
findings are resolved.**

## Supported Versions

Only `main` is supported. The `develop` branch may contain unreviewed work.

| Version | Supported |
|---------|-----------|
| `main` (latest) | yes |
| `develop` | no — pre-release only |
| `< 5.0` | no |

## Open Findings Summary

From the 2026-05-21 audit:

| # | Severity | Area | File | Status |
|---|----------|------|------|--------|
| 1 | Critical | Auth / secrets | `modules/login.php` | open |
| 2 | Critical | Secrets mgmt | `api/config.php` | open |
| 3 | Critical | DB fallback | `includes/functions.php:38` | open |
| 4 | High | CORS wildcard | `api/auth.php` | open |
| 5 | High | SQL injection (raw queries) | `cron/*.php`, modules | open |
| 6 | High | Session fixation | `src/Auth.php` | open |
| 7 | High | Info disclosure | `config/settings.php` | open |
| 8 | High | Missing security headers | `api/*.php` | open |
| 9 | High | Brute-force lockout incomplete | `src/Auth.php::login` | open |

Each finding has reproduction, risk, and remediation in the audit document.

## Reporting a Vulnerability

If you find a security issue:

1. **Do not open a public issue.**
2. Use GitHub's [private security advisory](https://github.com/CBahtaria/sentinel/security/advisories/new)
   or contact via the [profile](https://github.com/CBahtaria).
3. Include: affected version/commit, reproduction steps, impact assessment.

Expected response: **72 hours** to acknowledge, **14 days** for initial
assessment. Critical findings get a same-week patch path.

## Hardening Roadmap

In order:

1. Remove hardcoded credentials (findings 1, 2, 3) — blocks all deployments.
2. CORS allowlist + security headers (findings 4, 8) — required before any
   browser-accessible endpoint goes live.
3. Session regeneration + brute-force enforcement (findings 6, 9) — required
   before opening login to non-VPN clients.
4. Raw-query elimination (finding 5) — CI gate added to prevent regression.
5. `display_errors=0` in production (finding 7) — config flag.

## Dependency Hygiene

- `npm audit` runs in CI on every push (server/ WebSocket shim).
- Dependabot is enabled for `npm`, `composer`, and `github-actions` with
  weekly cadence.
- PHP composer dependencies (when added) will gate on `composer audit`.

## Operational Notes

This is **command and control software** for a real defense force. The
threat model assumes:

- Network-resident adversaries (nation-state and criminal).
- Insider risk (privileged user misuse).
- Supply-chain risk on every dependency added.

The audit document captures the structural baseline; runtime hardening
(SIEM integration, anomaly detection, audit log review cadence) is a
separate workstream.
