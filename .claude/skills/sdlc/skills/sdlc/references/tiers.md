Pick the lowest tier that fits. Unsure between two → pick the higher. Validate can escalate; nothing
de-escalates.
| Tier | When | Example |
|---|---|---|
| **lite** | No behaviour change: user-facing text, docs, comments, styling. No sensitive path | UI label, README |
| **small** | One behaviour in one area; approach obvious | add a field, off-by-one |
| **full** | Crosses modules/apps, touches auth/data/security, approach unclear, or the issue says so | new endpoint with permissions, a migration |
Gates per tier:
| Gate | lite | small | full |
|---|---|---|---|
| Worktree, checks, PR | ✓ | ✓ | ✓ |
| Feature: requirements | — | targeted questions | grilling |
| Feature: plan-loop rounds | — | 1 | ≤4 |
| Feature: approval + test-first build | — | ✓ | ✓ |
| Bug: investigate + repro + devil's advocate | — | 1 round | ≤5 rounds |
| Validate: code lens | — | unless docs-only | unless docs-only |
| Validate: security lens | sensitive paths | sensitive paths | always (non-docs) |
| Validate: spec lens | if touched | if touched | if touched |
| Validate: code lens checks graded approach | — | bug only | bug only |
Deliberate departures worth keeping:
- Bug flow proves a **failing repro test before** the devil's advocate runs, so the challenge grades a
  demonstrated failure, not a story.
- Bug **lite** skips the repro test (a string/docs fix has nothing to unit-test). Any non-string
  config-value change is **small**, not lite.
- Review lenses are agents (not skills) so a workflow can pin their model and restrict their tools.
### Escalation (validate returns `escalate` with `to`)
Validate re-judges the tier from the **actual diff** (fresh agent, reads the change, not the ticket). A
lite change on a sensitive path (auth, secrets, crypto, CI, Dockerfile, dependency manifests,
hooks/middleware, or `sensitivePaths`) always goes to at least small. Size alone never escalates.
- **small → full:** if only review depth increased, retain proven gates and validate at full. If the diff reveals new behavior, security boundaries or cross-module scope, reconcile requirements (full feature interview), revise/review the plan and obtain approval for the changed plan. Preserve the patch and reproduce any newly required red tests against baseline using runtime.md. Re-run affected build/diagnosis gates before full validation; existing evidence covers only unchanged behavior.
- **lite → `to`:** the edit is in the worktree, uncommitted, with no gates behind it. Set it aside so
  must-fail-first steps don't see it:
  Use the lossless escalation procedure in [runtime.md](../../../references/runtime.md): save a binary diff including untracked files outside the worktree, verify the saved patch, and restore only this run's known edits. Never run a blanket reset/clean. Then run the gates for `to`:
  - Feature: requirements for `to`, then plan-loop at `to` with the patch appended to `requirements`
    (not as `draft`), then approval.
  - Bug: bug-loop at `to` with the patch as `lead` (it still needs its own failing repro test).
  Validate again afterwards.
