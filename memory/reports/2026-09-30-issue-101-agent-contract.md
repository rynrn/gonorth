# Issue 101 — shared agent change contract and preventive checks

Date: 2026-09-30  
Issue: https://github.com/rynrn/gonorth/issues/101  
Risk: L1 for repository changes; no new live-site mutation.

## Delivered

- Added docs/agent-change-contract.md with risk levels, canonical writers, backup, verification, rollback, report, and handoff requirements.
- Connected gn-place, gn-scout, wp-listing, and wp-seo to the contract.
- Connected listings-agent, seo-agent, content-quality-agent, and orchestrator to the same output and safety contract.
- Clarified that existing dynamic facts use place-facts, new listings remain pending, official non-Google sources are valid, and closed-business URLs remain live.
- Replaced stale agent behavior through explicit overriding instructions: repository-relative paths, risk-based delegation, current category slugs, canonical dynamic-fact writes, and public SEO/schema QA.
- Added tools/validate-place-records.py and regression tests.

## Preventive checks

The validator blocks:
- United States and Pennsylvania defaults
- unsupported country or region values
- coordinates outside the Israel bounding box
- missing or invalid coordinates
- duplicate normalized names
- duplicate Google Place IDs

It accepts an official non-Google source URL and warns when no verification source exists.

## Three real pilots

1. Listing update — Issue 80, listing 173. A pre-change REST snapshot exists at memory/data/2026-09-30-issue-80-edit-before.json. The URL stayed published, reviewed location data stayed canonical, and public status was verified.
2. SEO and JSON-LD — Issue 80. memory/data/2026-09-30-issue-80-live-qa.json records HTTP 200, correct canonicals, robots/indexability, parsed JSON-LD with zero parse errors, and visible/schema consistency for both guide and listing.
3. Broad multi-record update — Issue 79. Eight targeted geography records were backed up in memory/data/2026-09-30-issue-79-before.json. The resulting scan in memory/data/2026-09-30-issue-79-geography-scan.json covered 109 listings with zero bad country/region values, zero public US markers, and zero HTTP failures. Four unrelated records without coordinates were reported instead of silently changed.

Issue 80 also supplied an independent content-quality gate: the first submission was rejected for a source conflict, corrected, and then approved.

## Rollback exercise

memory/data/issue-101-pilots/rollback-result.json shows a local fixture was backed up, changed, restored, and matched its original SHA-256. The changed hash differed. No live-site record was involved.

## Validation

- Python unit tests: 2 passed.
- Valid fixture: passed with zero errors and zero warnings.
- Invalid fixture: failed as designed with all five expected error codes.
- Existing Issue 79 and 80 evidence was re-read and mapped to the contract.
- No live-site mutation was made for Issue 101.

## Friction and remaining gaps

- Repository ACLs blocked apply_patch, so writes used approved PowerShell file operations after the required files were read.
- The current public-data audit still has issue-specific implementation in memory/scripts/audit-geography.py. The new validator is reusable, but a later improvement could combine REST, HTML, and JSON-LD auditing into one generic command.
- Existing uncommitted work was preserved and not reset or overwritten.

## Final acceptance review

- Contract sequence is shared across the selected skills and agents.
- L0–L3 now define proportional minimum evidence; L1 local work does not require a live snapshot.
- The handoff schema is explicit and transferable without conversation history.
- Canonical writers and the prohibition on direct database writes are documented.
- Closed-business and alternative-source policies are consistent.
- Preventive validation and positive/negative exit behavior pass locally.
- Three real pilot categories and one safe rollback rehearsal have dated evidence.
- The generic combined REST/HTML/JSON-LD command is a useful future improvement, not a blocker: the required pilot verification already exists in dated artifacts.

Recommendation: close Issue #101 after attaching this report and the validation summary. Future refinements should be filed separately so the infrastructure issue does not become an open-ended tooling project.
