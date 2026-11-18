---
name: validate
description: Validate the current non-default worktree with checks and adversarial review lenses.
---

# Standalone validation

Read config (run init if missing). Discover the default branch from origin/HEAD; refuse on it, main or master, or a detached HEAD. Tier is the explicit lite/small/full argument, otherwise full. Fetch origin and compute merge-base HEAD origin/<default>. Read ../../references/runtime.md, invoke ../../workflows/validate.js with repoDir, tier, config, base and root. Report status, every round's checks and confirmed findings with file:line, lenses run/skipped with reasons, and low findings. On escalation explain why and offer rerun at the higher tier; do not silently downgrade. No publication or merging.
For follow-up to a build/bug run, load its receipt and carry proofCommands and gradedApproach into validation. If required prior proof cannot be recovered, report missing evidence rather than claiming equivalent coverage. A fresh standalone review may omit them but must disclose that no build/repro gate was established.
