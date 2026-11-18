---
name: sdlc
description: Run the repository SDLC pipeline from a GitHub issue or request to a ready pull request.
---

# SDLC

Read and execute [the canonical orchestrator](skills/sdlc/SKILL.md). Resolve its
relative links from its own directory. This file is the `/sdlc` entry point for
the repository skills-directory plugin; supporting commands use `/sdlc:init`,
`/sdlc:validate`, `/sdlc:cleanup`, `/sdlc:grilling`, `/sdlc:plan-review` and
`/sdlc:devils-advocate`. The plugin root is this file's containing directory.
