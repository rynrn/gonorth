---
name: cleanup
description: Remove a clean, fully pushed dev worktree after its GitHub PR has merged.
---

# Cleanup merged SDLC work

Target = argument or current branch; require dev/* and identify its exact path from git worktree list --porcelain. Run ../../scripts/cleanup.js in dry-run mode first (node <script> --branch <branch> --cwd <repo>). It refuses dirty work, missing upstream, unpushed commits and a PR whose state is not MERGED. Never force. If the session is inside the target, ExitWorktree or change to the original checkout first, then rerun with --execute. The helper rechecks conditions before git worktree remove <path>, git branch -D <branch> and git worktree prune. Report exactly what was removed. Never remove the original/main checkout.
