# Issue #101 — local specification and conflict map

Date: 2026-09-30

## Ownership

- `AGENTS.md` is the project entrypoint.
- `docs/agent-change-contract.md` owns the cross-cutting workflow and handoff schema.
- Domain skills own only decisions unique to their domain and link to the shared contract.
- `memory/README.md` owns report retention and current-state conventions.
- Agent TOML files add role-specific independence requirements without copying the full contract.

## Information classes and canonical writers

| Information | Canonical writer | Derived projections |
|---|---|---|
| Editorial body and descriptions | Authenticated WordPress editorial workflow | Rendered HTML and excerpts |
| Status, hours, phone, website and address | place-facts submit → review → approved WordPress sync | Listing UI and schema |
| Country, region, city and coordinates | Authenticated GeoDirectory REST | Map, rendered address and JSON-LD |
| SEO metadata and JSON-LD | SEO workflow derived from approved values | Public head markup |
| Discovery candidate | `places-to-add.md`, pending only | Scout report |

No workflow writes SQLite or WordPress tables directly for dynamic place facts.

## Conflicts and duplication decisions

| Location | Finding | Decision |
|---|---|---|
| `AGENTS.md` and `CLAUDE.md` | Parallel references drifted in paths, SSH identity, credentials and hard rules. | `AGENTS.md` is authoritative; compatibility cleanup must not reintroduce credentials. |
| Listing examples vs. shared contract | Direct post-meta examples can appear canonical for existing facts. | place-facts supersedes them for existing dynamic facts. |
| `gn-scout` vs. closed-listing policy | A closed candidate should be rejected, but an existing closed URL should remain live. | Apply the rule according to lifecycle stage. |
| Google-first discovery | Older wording could make Google appear mandatory. | Official non-Google sources are accepted with URL, source type and checked date; Google ratings are never invented. |
| Agent report formats | Role reports omit rollback, blockers or close recommendation. | The shared handoff fields are mandatory; domain details may follow. |
| Orchestrator paths and roster | Some examples use absolute macOS paths or obsolete `.md` agent names. | Use repository-relative paths and the active TOML definitions. |

## Initial rollout

- Shared authority: `AGENTS.md` and `docs/agent-change-contract.md`.
- Skills: `gn-place`, `wp-listing`, `wp-seo` and `gn-scout`.
- Agents: listings, SEO, content quality and orchestrator.
- Prevention: `tools/validate-place-records.py` and local tests.
- Evidence: Issues #79 and #80 plus the local rollback rehearsal.

## Acceptance decision

The three pilot categories are covered by dated real-work evidence: listing update and SEO/JSON-LD verification in #80, and a broad eight-record geography correction with 109-record regression in #79. The rollback requirement is covered by a safe local restore whose final SHA-256 matches the backup. No new live mutation is required solely to close #101.
