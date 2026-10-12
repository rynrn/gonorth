# Issue #75 — credential and automation access audit

Date: 29.09.2026
Status: active; safe remediation completed, credential cutover pending explicit approval and provider actions.

## Confirmed scope

The repository is public and the local history contains 22 commits.

- Google API key pattern: 17 commits, three files.
- WordPress application-password pattern: 22 commits, three files.
- Password field in tracked local configuration.
- No private-key material found.

Values are intentionally omitted.

## Completed remediation

- Added ignore rules and removed server.config.json and .claude/.mcp.json from tracking while preserving local copies.
- Replaced live values in tracked instructions with environment or local-config references.
- Added sanitized examples, tools/scan-secrets.py, and a least-privilege CI workflow.
- Current tracked-tree scan passes with zero findings.
- Removed compromised credentials from local configuration and one Google-key command from shell history.
- Changed wp-config.php from mode 775 to 640 with group www-data; public HTTP remains 200.
- Moved two PHP backups out of public uploads; old URLs return 404.
- Quarantined wp-admin/false outside the web root with mode 600.
- WordPress core checksum verification passes.
- Created gonorth_automation with Editor role and a dedicated application password stored only in a mode-600 server file.
- Verified the new credential with REST and MCP initialization: HTTP 200, edit_posts=true, manage_options=false.

## Pending cutover and provider work

- Transfer the new Editor application password to the approved workstation secret store, test locally, revoke the old administrator application password, and verify revocation.
- Rotate the Google Places key in Google Cloud; restrict it to Places API and the production IP.
- With root/provider access, lock or rotate the exposed Unix password, disable root/password SSH, and review six authorized-key lines.
- Coordinate a Git history rewrite only after all exposed credentials are revoked.
- Confirm ownership and 2FA for both WordPress administrator accounts.

## Server observations

- WordPress core 7.1.2; no core update available.
- Several plugin updates are available and belong to issue #77.
- Wordfence and one social plugin are inactive.
- WP_DEBUG, WP_DEBUG_LOG, and WP_DEBUG_DISPLAY are false.
- DISALLOW_FILE_EDIT and FORCE_SSL_ADMIN are undefined.
- TLS is active; observed certificate expiry is 02.11.2026.
- The SSH account is not locked; PermitRootLogin is enabled; effective password-auth status needs elevated access.

## Proposed wp-config.php diff

Add before the stop-editing marker after a backup:

    define( 'DISALLOW_FILE_EDIT', true );
    define( 'FORCE_SSL_ADMIN', true );

FS_METHOD=direct remains unchanged. Validate PHP lint, HTTP, admin HTTPS, REST, and WP-CLI afterward.

## Approval blocker

Automatic approval review rejected transferring the new live application password from the server to the workstation because explicit approval for that exact credential transfer and storage was not present.

## Approved cutover completed

The site owner explicitly approved the credential transfer, revocation, and wp-config.php diff.

- The Editor-only application password was transferred into the ignored local MCP configuration.
- Workstation REST and MCP tests returned HTTP 200.
- The exposed administrator application password was revoked.
- The revoked credential now returns HTTP 401; the Editor credential still returns HTTP 200.
- Administrator application-password count is 0; automation account count is 1.
- DISALLOW_FILE_EDIT and FORCE_SSL_ADMIN are enabled.
- FS_METHOD remains direct.
- Final core checksum, PHP syntax, WP-CLI, front end, REST and admin HTTPS checks passed.
- Rollback copy: /home/claude/backups/2026-09-29-issue-75/wp-config.php.before.
- Post-change copy: /home/claude/backups/2026-09-29-issue-75/wp-config.php.after.
- A first run encountered Windows line endings; the automatic trap restored the exact original hash. The corrected Linux-format run then completed successfully.

## Google Places key rotation completed — 30.09.2026

The site owner created and installed a replacement server key restricted in Google Cloud. Before old-key deletion, a direct Places API test and a GoNorth dry run both succeeded. The owner then deleted the old exposed key. A second forced dry run after deletion returned one exact match, zero rejected failures and no writes. The live audit remains 105 complete Google bundles with zero stale bundles; home and REST returned HTTP 200. No key value was copied into this report or the repository.
