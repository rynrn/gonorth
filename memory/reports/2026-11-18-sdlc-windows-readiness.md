# SDLC Windows readiness — 18.11.2026

Risk: L1. Canonical writer: local Git worktree only. No live-site change.

## Scope and authorization

The owner requested SDLC configuration, then completion of readiness tests and fixes. Issue #101 remains previously completed; this is a KEY=0 tooling repair, not a reopening or new implementation of #101. Existing unrelated work remains in the original checkout.

## Change and diagnosis

Added `.claude/sdlc.json` with real Python project tests, tracked-file secret scanning, and SDLC package verification. Commands require no dependency installation. The standard CI requirement is `tracked-secret-patterns`; for plugin changes the host must also explicitly require `sdlc-verify` before merging. Publication and merging remain subject to the skill's authorization gate.

Windows Git checkout uses CRLF. `check-package.js` compared raw role text against LF-terminated literals and rejected valid roles. The fix normalizes CRLF at the role read boundary, preserving existing name, model and tool restrictions.

One regression test executes the actual package checker using temporary LF and CRLF fixtures. Both encodings cover valid roles, invalid names, unsupported models and expanded restricted-tool declarations. Temporary cleanup checks containment before removal.

## Evidence

- Initial project configuration: semantic validator passed; 14 Python tests and tracked-file secret scan passed; package check failed with `Invalid role checker`.
- Baseline SDLC suite: 86 tests passed; it did not cover the package newline defect.
- Real shipped `runner.js bug-loop` used independent model agents for diagnosis, regression implementation, failing-test check, root-cause verification, challenge, fix and green checks. Host maintained NDJSON pipes and external journals, rather than simulating the workflow.
- Before production-code repair: four LF subcases passed; four CRLF subcases failed at the diagnosed predicate (nine Node tests including the failed parent).
- After repair: independent regression check passed 9/9; implementer package verification passed 50 files and 95 tests. Final independent full verification and code/security review are recorded in the local runner validation receipt.
- Audit receipts stay under the Git common directory `sdlc-runs/windows-checks-1795028639794/`; raw agent journals are not published.

## Limits and next step

This establishes Windows/Codex local workflow operation. GitHub publication, CI on the proposed commit and head-bound merge remain separate gates; no real remote merge is claimed. Bash/jq hook integration is not exercised by the Windows/Codex host; Codex uses explicit branch checks and does not automatically load the Claude Bash hook.

## Rollback

Restore `.claude/skills/sdlc/scripts/check-package.js` from the reviewed base with `git restore --source=origin/main -- .claude/skills/sdlc/scripts/check-package.js` only in this dedicated worktree. Remove only this run's new config, regression test and report if abandoning it; reverse only this report's appended status paragraph. Preserve unrelated files and avoid blanket reset/clean.

Sources checked 18.11.2026: plugin scripts/tests and role files, `.github/workflows/sdlc.yml`, `.github/workflows/secret-scan.yml`, project test directories, and local workflow receipts. Risk and evidence contract: `docs/agent-change-contract.md`.
