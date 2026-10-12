# Issue 101 implementation map — 2026-09-30

Issue: https://github.com/rynrn/gonorth/issues/101

## Scope
- Shared contract: docs/agent-change-contract.md
- Preventive validator: tools/validate-place-records.py
- Regression tests: tools/tests/test_validate_place_records.py
- Skills: gn-place, gn-scout, wp-listing, wp-seo
- Agents: listings-agent, seo-agent, content-quality-agent, orchestrator
- Evidence: issue 79 geography work, issue 80 listing and SEO work, and a local rollback exercise

## Conflicts found
- listings-agent still described direct metadata writes for existing dynamic facts and obsolete category names.
- orchestrator contained machine-specific macOS paths and required delegation for every task.
- SEO instructions did not require a pre-change snapshot and public JSON-LD comparison.
- The geography audit script was tied to one issue instead of being a reusable guard.

## Safe pilot plan
1. Listing update: use issue 80 listing 173 evidence, including source review, snapshot, public verification, and retained URL.
2. SEO and JSON-LD: use issue 80 live QA evidence for canonical, robots, JSON-LD parsing, and visible/schema consistency.
3. Broad multi-record work: use issue 79 evidence for eight corrected records and a 109-page public regression scan.
4. Rollback: mutate a local fixture, restore its backup, and compare hashes. No live record is changed for the exercise.
