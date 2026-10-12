# GoNorth agent change contract

This contract applies to every agent that changes listings, content, SEO data, theme files, plugins, or operational records.

## Risk level
- L0: read-only inspection.
- L1: reversible local changes.
- L2: scoped live change with a verified rollback.
- L3: broad, sensitive, or hard-to-reverse change.

## Minimum evidence by risk
- L0: scope, dated sources, findings, and an explicit no-change statement.
- L1: L0 plus local diff, relevant tests, and a file-level rollback. No live snapshot is required.
- L2: L1 plus a pre-change export, executable rollback, canonical-record verification, public verification, and a dated report.
- L3: L2 plus explicit authorization, independent review, dry run or safe rehearsal, staged execution with a stop condition, and full affected-inventory regression.

Use the highest applicable level. Publication, deletion, redirects, credentials, bulk writes, or changes affecting many URLs cannot be below L2.

## Required workflow
1. Read AGENTS.md, memory/README.md, memory/current-status.md, the latest relevant report, and the GitHub issue.
2. State the risk level and the canonical writer before changing anything.
3. Record every source URL and access date. Expose source conflicts instead of silently choosing one.
4. Create a pre-change snapshot and a concrete rollback path for L2 and L3 work.
5. Apply the smallest scoped change through the canonical interface.
6. Verify both the canonical record and the public result.
7. Run regression checks proportional to the affected scope.
8. Write a dated report, update memory/current-status.md, and add evidence to the issue.
9. Close an issue only after every acceptance criterion has evidence.

## Canonical writers
- Editorial content: authenticated WordPress REST, MCP, or the approved content workflow.
- Status, hours, phone, website, and address: place-facts submission, review, and WordPress sync.
- GeoDirectory country, region, city, and coordinates: authenticated GeoDirectory REST interface.
- SEO and schema: derived from reviewed canonical values and verified on the public page.
- Never write WordPress or place-facts SQLite tables directly.

## Safety rules
- New listings remain pending until an administrator approves them.
- A closed business keeps its URL live by default, with a verified closure notice and useful alternatives.
- An official non-Google source is valid when its URL and access date are recorded.
- Run tools/validate-place-records.py before creating or updating place records.
- Keep secrets out of source, reports, command output, and agent handoffs.
- A permission blocker must name the blocked action, the missing permission, completed independent work, and the exact next step.

## Transferable agent output
Every handoff must stand alone without conversation history and include these fields in order:

```yaml
status: complete | partial | blocked
scope: "inspected or changed scope"
risk_level: L0 | L1 | L2 | L3
canonical_writer: "interface used, or none"
findings: []
sources:
  - url: "URL or repository-relative path"
    checked_at: "YYYY-MM-DD"
changes_made: []
unchanged:
  - item: "deliberately unchanged item"
    reason: "reason"
validation:
  - check: "command or observable check"
    result: pass | fail | not-run
    evidence: "result or artifact path"
snapshot: "artifact path or not-required"
rollback: "exact recovery method"
blockers: []
remaining_risks: []
issue_recommendation: close | keep-open
```

A blocker must identify the missing permission or action, completed independent work, the exact user step, and the post-action verification. One blocker does not stop unrelated safe work.
