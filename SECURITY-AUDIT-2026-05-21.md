# Security Audit — UEDF SENTINEL v5.0

**Date:** 2026-05-21
**Auditor:** Claude Code (read-only structural audit via `/learn-codebase`)
**Scope:** Every source file in `/home/cbartaria1/my-projects/sentinel/` excluding `vendor/`, `.git/`, `logs/`, `uploads/`.
**Status:** 9 issues identified. None exploited. **MUST RESOLVE BEFORE PRODUCTION DEPLOYMENT.**

---

## Critical (block production)

### 1. Hardcoded test credentials in production code
**File:** `modules/login.php`
**Finding:** `commander/commander123` test credentials baked into the login handler.
**Risk:** Any attacker who finds `modules/login.php` (or reads it via path traversal / leaked source) gets commander-level access. The `Password123!` bcrypt seed in `database/schema.sql` for the 4 default users (commander/operator/analyst/viewer) compounds this.
**Fix:** Remove the hardcoded path. Delete or rotate the 4 default users in production seed. Force password reset on first login. Add a CI gate that `grep`s for any password literal in `modules/`.

### 2. Hardcoded API key in source
**File:** `api/config.php`
**Finding:** `uedf-sentinel-mobile-2026` API key embedded as a string literal, exposed to anyone reading the source or curl'ing `/api/config.php` if it's publicly served.
**Risk:** Mobile clients with this key get authenticated access. Key cannot be rotated without code change + deploy.
**Fix:** Move to `$_ENV['SENTINEL_MOBILE_API_KEY']` loaded from `.env`. Add to `.gitignore`. Document rotation procedure. Add a `gitleaks` pattern for the literal.

### 3. Hardcoded DB credentials fallback
**File:** `includes/functions.php:38`
**Finding:** PDO connection hardcodes `localhost` host and empty password as the fallback when env vars are unset.
**Risk:** Silent fallback to insecure local DB on misconfiguration; if production env vars are accidentally unset, the app connects to whatever happens to be on localhost:3306 with empty password.
**Fix:** Raise a fatal error if `SENTINEL_DB_*` env vars are unset. No silent fallback.

---

## High (resolve before public deployment)

### 4. CORS wildcard
**File:** `api/auth.php`
**Finding:** `Access-Control-Allow-Origin: *` allows any origin to make authenticated requests. This is incompatible with the Bearer token + cookie auth pattern used elsewhere.
**Risk:** Browser-based clients on arbitrary origins can exfiltrate Bearer tokens to attacker-controlled sites. Allows CSRF via XHR from third-party origins.
**Fix:** Use the `SENTINEL_CORS_ORIGINS` env var allowlist pattern already established in `api/gateway.php`. Explicitly enumerate trusted origins; reject all others.

### 5. Mixed PDO prepared statements with raw queries
**Files:** Some routes in `cron/` and (per audit notes) some module endpoints
**Finding:** Codebase mostly uses PDO prepared statements, but `cron/` (e.g., `daily_report.php`) and a few module endpoints use `$db->query()` with string interpolation.
**Risk:** SQL injection surface in any path where user-supplied data reaches the raw-query callsites.
**Fix:** Grep for `->query(` across the codebase. Convert every callsite to `->prepare()` + parameter binding. Add a CI gate that fails on `->query(` followed by `$` in the same statement.

### 6. Session not rotated on login
**File:** `src/Auth.php` (SentinelAuth)
**Finding:** Session ID persists across login boundaries.
**Risk:** Session fixation. Attacker plants a known session ID via a phishing link, victim logs in, attacker reuses the now-authenticated session.
**Fix:** Call `session_regenerate_id(true)` immediately after successful authentication in `login()`.

### 7. `display_errors: 1` in production config
**File:** `config/settings.php`
**Finding:** PHP errors render directly to the response body.
**Risk:** Stack traces leak file paths, function signatures, SQL fragments, environment variable names. Aids reconnaissance.
**Fix:** `display_errors: 0` + `log_errors: 1` to `error_log`. Gate the verbose behavior behind `SENTINEL_DEBUG=1`.

### 8. No CSP headers on API responses
**File:** `api/*.php`
**Finding:** API responses lack `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security`.
**Risk:** If any API response is rendered in a browser context (e.g., error pages, JSON loaded into an iframe), XSS becomes exploitable. No defense-in-depth against MIME confusion or clickjacking.
**Fix:** Add a middleware that emits CSP/STS/XCTO/XFO headers on all API responses. Mirror the `gui/server.py` security-headers pattern from the sister project `agentic-uav-stack`.

### 9. Incomplete login lockout logic
**File:** `src/Auth.php` (`SentinelAuth::login`)
**Finding:** Login attempts are tracked in `login_attempts` table with 5-attempt / 15-min lockout intent, but the lockout enforcement code path may be incomplete (attempts logged but not gating subsequent login() calls).
**Risk:** Brute force not actually rate-limited despite the schema suggesting it is.
**Fix:** Audit `login()` to confirm: `SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > NOW() - INTERVAL 15 MINUTE` returns ≥5 → reject before bcrypt check. Add an integration test that loops 6 failed logins and confirms the 6th gets 429.

---

## Verification

After fixes are applied, verify with:

```bash
# 1. No hardcoded credentials
git grep -niE 'password\s*=\s*["\'](?!.*\$)' modules/ src/ api/
git grep -niE 'api[_-]key\s*=\s*["\'](?!.*\$)' modules/ src/ api/

# 2. No raw queries
git grep -nE '->query\(.*\$' .

# 3. CORS allowlist enforced
curl -i -H "Origin: https://attacker.example" http://localhost/sentinel/api/auth.php?action=me
# Expect: NO Access-Control-Allow-Origin header in response

# 4. Display errors off
php -r 'echo ini_get("display_errors") . PHP_EOL;'
# Expect: 0 or empty

# 5. Lockout enforced
for i in 1 2 3 4 5 6; do
  curl -X POST -d 'username=test&password=wrong' http://localhost/sentinel/api/auth.php?action=login
done
# Expect: 6th request returns HTTP 429
```

## Notes on scope

This audit was structural (reading source) — no runtime exploitation was performed. Findings #5 and #9 should be re-verified by running the test suite plus targeted manual probes; line-level confirmation may differ from what the source-read suggested.

The default seed in `database/schema.sql` (15 drones, 8 threats, 15 nodes, 4 users with `Password123!` bcrypt) is intentional for development. Production deploy MUST:
- Run with a clean schema (no `--with-seed` flag, or equivalent)
- Use a different bcrypt seed for the bootstrap superadmin account
- Disable or remove the 4 default user templates entirely

## Related
- Sister project `agentic-uav-stack` has a 9-persona vulnerability scanner (`coworkers/vuln_scan/`) — running the `red_team`, `blue_team`, and `compliance` personas against this repo would catch most of the above plus likely surface additional issues.
- `agentic-uav-stack/SECURITY.md` documents the suppression annotation policy (`# noqa: vuln-scan-secrets`) — adopt the equivalent in sentinel for any documented exceptions.
