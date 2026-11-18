---
name: plan-review
description: Run a single independent SOUND and SECURE gate on a proposed plan.
---

# Manual plan review

Gather the actual plan, agreed requirements and config securityRules from the conversation and repository; ask for missing inputs, never invent them. Dispatch sdlc:plan-review using the matching agent file and PLAN_REVIEW schema in ../../workflows/common.js. Supply only plan, requirements, securityRules and repoDir; no dispatcher reasoning. Fence ticket data using ticket() from common.js. Relay the verdict and findings without adding approval. Null or malformed output is a failed gate.
