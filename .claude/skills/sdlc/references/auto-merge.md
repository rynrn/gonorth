# Automatic merge

The user-authorized publication flow finishes with automatic squash merging after
validation and CI. `autoMerge:false` opts out. `autoMergeChecks` names every CI context
the project requires (for this plugin's CI: `sdlc-verify`). The host reads the policy
before publication; an existing explicit instruction to publish/merge is sufficient.

From the clean, validated `dev/*` checkout:

```sh
node ROOT/scripts/auto-merge.js --repo owner/repo --pr 123 --head COMMIT_SHA --check sdlc-verify --execute
```

Repeat `--check` for additional expected jobs. Omit `--execute` for a read-only
preflight. Use an argument array or appropriate host quoting. The helper verifies:

- Local origin identifies the requested github.com repository (HTTPS or standard SSH).
  A missing origin, another host, or a different repository stops the flow.
- Local clean branch and HEAD match the same-repository, non-draft PR and default base.
- All expected named checks exist and succeed; other reported checks cannot be pending
  or failed. At least one successful check is mandatory.
- No outstanding required review/change request, conflict or unknown mergeability.
- A fresh view still meets every condition immediately before the command.

It calls GitHub's PR merge endpoint with `sha=<validated-head>` and `merge_method=squash`
through `gh api`, after the host has waited for the named checks. This is automatic
merging by the active host, not persistent GitHub auto-merge enrollment. A second
read verifies the merged state. On waiting, poll with bounded
checks during the active task; a CI failure, changed head or API error stops the flow.
Do not retry a failed mutation blindly; inspect the PR first. Stop after 30 minutes
of unchanged waiting and report what requirement is outstanding. No automation job
or background agent is installed. Do not delete the branch/worktree automatically.

Do not change repository protections or enable global settings as a fallback. If
GitHub requires a merge queue or refuses the endpoint, report the error; do not weaken the request.
The helper pins the head atomically, but client-side checks do not atomically lock
base updates or CI reruns. Required-check rules on GitHub provide that server-side
enforcement. The host must report that limit on repositories without those rules.

The guard permits only this installed Node helper with the supported flags. Direct
`gh pr merge`, merge API calls and direct protected-branch writes remain blocked.
This is an accident guard, not an authorization/security boundary.

References: [PR merge endpoint](https://docs.github.com/en/rest/pulls/pulls#merge-a-pull-request),
[GitHub auto-merge](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/configuring-pull-request-merges/managing-auto-merge-for-pull-requests-in-your-repository).
