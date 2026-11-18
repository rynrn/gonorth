# Executable workflow contract

Read [runtime.md](runtime.md) for the adapter and runtime fallback. The JavaScript
files are the authoritative control flow; schemas are exported from
[`common.js`](../workflows/common.js). Call sites validate JSON, including replies
already parsed by the host. Exceptions, null, empty strings and malformed replies
take the null branch without an implicit retry.

| Module | Inputs | Outcomes / caps |
| --- | --- | --- |
| [plan-loop](../workflows/plan-loop.js) | repoDir, tier, requirements, assumptions?, ticketText?, config?, draft?, ownerNotes? | converged, plan, rounds, error/findings; small 1, full 4 review rounds |
| [build](../workflows/build.js) | repoDir, approved plan, testCmd | built, testsNotFailing, testsFailing, error; 2 test attempts, 2 implementation attempts |
| [bug-loop](../workflows/bug-loop.js) | repoDir, tier, problem, ticketText?, testCmd, lead? | fixed, couldNotReproduce, notProven, testsFailing, error; small 1/full 5 challenge rounds, 2 repro attempts per diagnosis, 2 fix attempts |
| [validate](../workflows/validate.js) | root, repoDir, tier, config, resolved base hash, gradedApproach? | pass, noChanges, escalate, fail, error; 3 rounds, full scope and content fingerprint checked each round |

All directories are absolute. Only the orchestrator can record actual human approval;
workflows cannot ask the user or manufacture approval. Lite skips plan/build/bug
loops and still validates and requests publication approval.

Role contracts: RUN=exact command outcome; PLAN=plan text; PLAN_REVIEW=SOUND/SECURE
and findings; TESTS=new tests/target command; REPRO=one failing regression test;
DIAGNOSIS=root-line evidence; CHALLENGE=root/simplest/risk verdict; CHANGE=changed
files, summary, deviations; REVIEW=findings; REFUTATION=refuted/why; TIER=tier/why.
Unknown properties are rejected. Findings have severity, file, optional positive
line, title, concrete scenario and fix. Critical/high/medium block; low is collected.

The independent investigator also verifies that red tests fail for missing behavior,
not missing dependencies or broken test setup. History lookup errors stop the bug
gate. A missing tier review raises one tier. Missing lens output becomes a high
finding; missing verifier output keeps the finding. Confirmed findings persist until
independently shown fixed, even when a later reviewer omits them.
Refutations expire after edits. Inconclusive verification remains blocking. A pass includes a fingerprint; changes during a round discard its green evidence and consume a new round. Feature build requires both targeted new tests and the full suite to pass.
