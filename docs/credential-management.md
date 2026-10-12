# Credential management for GoNorth

Live credentials belong in the operator credential store or untracked machine configuration.

## Rules

1. Never put passwords, application passwords, API keys, private keys, OAuth tokens, or recovery codes in tracked files, issues, logs, shell history, reports, or agent instructions.
2. Keep machine configuration ignored and maintain sanitized example files.
3. Use a separate least-privilege credential for each integration and environment.
4. Test replacements before revoking old credentials; revoke exposed credentials immediately after a successful test.
5. Record only owner, purpose, scope, dates, and revocation status.
6. Run `python tools/scan-secrets.py` before commits and in CI. For a local read-only history audit, run `python tools/scan-git-history-secrets.py`; it prints aggregate rule counts only and returns nonzero on matches. It scans blobs reachable from local refs, not untracked files, remote-only refs, backups, or credential validity.
7. Rewrite Git history only after credential rotation and collaborator coordination.

## Integration scope

- SSH: dedicated Unix automation account and key authentication.
- WordPress: dedicated automation user with Editor capabilities by default.
- Google Places: dedicated key restricted to Places API and the production server IP.
- GitHub: named identity stored by GitHub CLI with repository-scoped access.

## Rotation checklist

- Create a narrower replacement.
- Test the exact integration path.
- Update the approved secret store.
- Revoke and test failure of the previous credential.
- Scan the current tree and agreed history scope.
- Record non-secret evidence in a dated private report.
