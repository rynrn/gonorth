---
name: sdlc
description: Take a GitHub issue or implementation request through the repository SDLC plugin to a ready pull request.
---

# SDLC

Read the canonical orchestrator at ../../../.claude/skills/sdlc/skills/sdlc/SKILL.md, then follow it. Its supporting files are relative to that canonical file, not this wrapper. The plugin root is ../../../.claude/skills/sdlc. Read its references/runtime.md for Codex and other runtimes without Workflow. This invocation explicitly requests the separate agent roles required by the pipeline. Preserve human gates, role/tool restrictions, null-result failures and loop caps. The Claude Bash hook does not automatically execute in Codex; apply branch checks before each Git mutation. Never merge or publish without the specified gate.
