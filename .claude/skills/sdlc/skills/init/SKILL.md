---
name: init
description: Detect and approve repository SDLC commands and project security rules.
---

# Initialize SDLC

### `/sdlc:init`
1. If the config exists, show it; ask re-detect or keep.
2. Detect commands yourself; ask only for project security rules (everything else comes from files).
   Set autoMerge=true and detect exact expected CI job/context names as autoMergeChecks from the repository workflows. Show both in the proposal. If CI names cannot be established, ask for them or set autoMerge=false explicitly; never use an empty list or silently guess a green-check name.
3. Validate the proposal through `../../scripts/check-config.js` (shared semantic validator), then show proposed JSON; ask for approval. JSON Schema validates shape; the shared validator also checks paths and regexes.
4. On approval write it, then run each command once through the `checker` agent and report which pass.
   A failing command stays in the config, flagged — the owner decides.
5. Tell the user to commit the file.

Read [detection rules](references/detect.md) and validate the proposed JSON against [schema](sdlc.schema.json). Use repository files to select package-manager-correct commands; npm ci requires a lockfile, otherwise npm install. Required test must be a real non-watch test command. If none can be detected, ask for it rather than inventing a passing command. Reject absolute or escaping apps paths and invalid sensitivePaths regexes. Never run install or write config before approval. Run approved commands in their actual app directory; missing dependencies fail. Runtime-only helpers live at ../../scripts/.
