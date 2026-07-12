# Sentinel v5.0 — CLAUDE.md

Military C2 platform for UAV fleets. Post-audit hardening complete; awaiting UAT. Institutional buyer profile: defence forces, national security operators.

## Hard lines (non-negotiable)

1. **No raw SQL string concatenation.** All database access uses PDO prepared statements. `mysqli` without prepared params is a bug. Fail any PR that concatenates.
2. **All secrets from environment.** No `.env` in git. No credentials in PHP source. Use `getenv()` with a defensive check that fails closed if the variable is unset (fatal, do not fall back to a default).
3. **RBAC enforced at the request layer, not the UI.** Every API endpoint must call the role check before touching data. Client-side gating is a convenience, not a control.
4. **TOTP 2FA required for Commander and Operator roles.** No exceptions. Session establishment without TOTP is a bug.
5. **Session tokens are bound to IP whitelist for Commander role.** IP change invalidates the session — force re-auth.
6. **Audit log is append-only and hash-chained.** Any tamper attempt (row deletion, timestamp mutation) breaks the chain and must alert. Never truncate the audit log to save space; rotate cold.
7. **Login lockout enforced server-side.** 5 failed attempts → 15-minute lockout tied to username AND source IP. Do not reset by user request without out-of-band verification.
8. **Security headers on every API response.** `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, `Content-Security-Policy` set. Missing header = failing test.

---

## Blocking Gates (must be satisfied before any commit to main)

**Phase 1 — Secret scan. Never proceed past this step until it passes.**
```bash
gitleaks detect --source . --no-git
```
`gitleaks detect` must return 0 findings. Any credential, DB password, or session key in source is an immediate STOP.

**Phase 2 — Dependency audit.**
```bash
php composer.phar audit
```
No critical or high advisories. Any finding blocks the commit until patched or formally deferred with a documented exception.

**Phase 3 — Security audit checklist.**
Re-run the `docs/audit-2025.md` checklist on any PR that touches `api/`, `auth/`, or `middleware/`. All 9 original findings must remain resolved.

**Phase 4 — RBAC test coverage.**
```bash
./vendor/bin/phpunit --testsuite api
```
All tests in `tests/api/*.test.php` must pass. New endpoints require a corresponding test before merge.

---

## Model Routing

Use **Opus 4.7** for security-review conversations, PR audits, and any change to `api/`, `auth/`, or `middleware/`. Sonnet 4.6 is acceptable for UI-only work (dashboards, telemetry views) and documentation. Never Haiku on this repo.

---

## Review Rubric

An agent's work is **APPROVED** only if ALL of the following hold:

| # | Criterion | How to verify |
|---|-----------|---------------|
| a | Every file the agent claims to have touched has a visible diff | `git diff --name-only` matches the agent's report |
| b | Every finding has a fix AND a verification step | Finding IDs map 1-to-1 in the agent record below |
| c | No new secrets committed | `gitleaks detect` → 0 findings |
| d | Every test the agent named actually ran and passed | Test output included in agent record, no skips |
| e | No doc claim contradicts source | Cross-check any prose claim against the actual file before marking done |

A "looks good" without satisfying all five is not an approval.

---

## Agent Record Schema

Each completed task must have a record in YAML format:

```yaml
agent:         # agent ID or session label
model:         # opus | sonnet
phase:         # phase number or name within the task
status:        # DONE | NEEDS_REVIEW | BLOCKED
files_touched:
  - path/to/file.php
findings:
  - id:          F-001
    severity:    high | medium | low | info
    title:       Short description
    fix:         What was changed
    verified_by: Command or manual step that confirmed the fix
tests_run:
  - name: phpunit api suite
    command: ./vendor/bin/phpunit --testsuite api
tests_result: PASS | FAIL | SKIP
residual_risk: >
  Any known limitation, deferred item, or assumption that must be
  revisited before UAT sign-off.
```

---

## What "done" looks like

- `php composer.phar audit` → no critical/high advisories.
- All 9 findings from the original security audit remain resolved. Re-run `docs/audit-2025.md` checklist on any auth-touching PR.
- New endpoints have RBAC test coverage in `tests/api/*.test.php`.
- WebSocket telemetry bridge does not accept unauthenticated connections.

## What "wrong" looks like

- A new endpoint that returns 200 without checking `Auth::require_role()`.
- A `try { ... } catch (Exception $e) { return true; }` — permissive fail-open.
- A commit that adds a query with `"SELECT * FROM x WHERE id = " . $id`.
- Any file matching `.env*` in `git status`.
- A UI role check without a corresponding server-side check.

## Reference

Rules lifted from `~/my-projects/personal/second-brain/about-me.md` and the post-audit resolution in `docs/audit-2025.md`. When those conflict with anything here, they win.
