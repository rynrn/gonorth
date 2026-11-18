Scannable; omit empty sections except **Gates**.
```markdown
Closes #<KEY> — <one-line summary>
## Summary
<what and why, 1–3 sentences>
## Changes
- <file or area>: <change>
## Gates
flow=<bug|feature> tier=<lite|small|full>
- Plan: plan-review SOUND=<y/n> SECURE=<y/n> after <n> round(s); approved by owner   (feature)
- Diagnosis: <root cause, file:line>; devils-advocate rootHolds after <n> round(s)   (bug)
- Repro / new tests: <file :: name> (failed before, passes now)
- Validate: <n> round(s); checks <names> green; lenses run <list>; skipped <lens: reason>
- Evidence: <runner run ID>; validated content fingerprint <hash>; published commit <sha>
- Auto-merge: <enabled/disabled>; expected CI contexts <names>; actual status <waiting/blocked/merged>
- Skipped by tier: <gates>
- Escalated: <from → to, reason>   (if any)
## Low findings (not blocking)
- <lens> <file:line> <title>
## Test plan
- <manual steps a reviewer can follow>
## Deviations and accepted risks
- <departure from the approved plan or individually accepted review finding; why and owner approval>
```
The issue comment is the PR link followed by the **Gates** block verbatim.
Populate gate results from runner results/receipts. Do not invent successful gates from descriptions or test counts. Keep raw journals local; redact secrets, local paths and ticket/log data before publishing a compact evidence summary. Staging changes the fingerprint, so compare before staging, then compare the approved tree to the resulting commit as specified by the orchestrator.
