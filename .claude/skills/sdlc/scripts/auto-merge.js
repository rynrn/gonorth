import {execFileSync} from 'node:child_process';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

const fields = 'number,url,state,isDraft,headRefName,headRefOid,baseRefName,isCrossRepository,mergeable,mergeStateStatus,reviewDecision,statusCheckRollup';
export function mergeOptions(argv) {
  const options = {execute: false, checks: []};
  for (let i = 0; i < argv.length; i++) {
    const flag = argv[i];
    if (flag === '--check' && argv[i + 1] && !argv[i + 1].startsWith('--')) { options.checks.push(argv[++i]); continue; }
    if (flag === '--execute' && !options.execute) { options.execute = true; continue; }
    if (!['--repo', '--pr', '--head'].includes(flag) || options[flag.slice(2)] !== undefined || !argv[i + 1]) throw Error('Expected --repo owner/repo --pr number --head sha --check name [--check name] [--execute]');
    options[flag.slice(2)] = argv[++i];
  }
  if (!/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/.test(options.repo || '') || !/^[1-9]\d*$/.test(options.pr || '') || !/^[a-f0-9]{40}$/.test(options.head || '') || !options.checks.length || options.checks.some(c => !c.trim())) throw Error('Invalid repository, PR number, head SHA or missing expected --check');
  return options;
}
export function autoMerge(options, run = (exe, args) => execFileSync(exe, args, {encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim()) {
  const {repo, pr, head, execute, checks: expectedChecks} = mergeOptions(['--repo', options.repo, '--pr', String(options.pr), '--head', options.head, ...(options.checks || []).flatMap(c => ['--check', c]), ...(options.execute ? ['--execute'] : [])]);
  const git = args => run('git', args);
  const origin = git(['remote', 'get-url', 'origin']);
  const identity = origin.match(/^(?:https:\/\/github\.com\/|ssh:\/\/git@github\.com\/|git@github\.com:)([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+?)(?:\.git)?\/?$/i)?.[1];
  if (identity?.toLowerCase() !== repo.toLowerCase()) throw Error('Local origin does not match the requested GitHub repository');
  const branch = git(['branch', '--show-current']);
  if (!branch.startsWith('dev/')) throw Error('Automatic merge requires a dev/* work branch');
  if (git(['rev-parse', 'HEAD']) !== head) throw Error('Local HEAD changed; revalidate');
  if (git(['status', '--porcelain', '--untracked-files=all'])) throw Error('Working tree is dirty; revalidate');
  const meta = JSON.parse(run('gh', ['repo', 'view', repo, '--json', 'nameWithOwner,defaultBranchRef']));
  const view = () => JSON.parse(run('gh', ['pr', 'view', pr, '--repo', repo, '--json', fields]));
  const assess = current => {
  if (meta.nameWithOwner.toLowerCase() !== repo.toLowerCase() || current.number !== Number(pr) || current.isCrossRepository || current.headRefName !== branch || current.headRefOid !== head || current.baseRefName !== meta.defaultBranchRef?.name) throw Error('PR repository/branch/head/base does not match the validated change');
  if (current.state === 'MERGED') return {status: 'merged', url: current.url, head};
  if (current.state !== 'OPEN' || current.isDraft) throw Error('PR must be open and ready');
  const checks = current.statusCheckRollup;
  if (!Array.isArray(checks) || !checks.length) return {status: 'blocked', reason: 'No CI checks reported for this head', url: current.url};
  for (const name of expectedChecks) {
    const named = checks.filter(c => (c.name || c.context) === name);
    if (!named.length) return {status: 'waiting', reason: `Expected CI check not reported: ${name}`, url: current.url};
    const results = named.map(c => c.__typename === 'CheckRun' ? c.status !== 'COMPLETED' ? 'PENDING' : c.conclusion : c.state);
    if (results.some(s => !['PENDING', 'EXPECTED', 'SUCCESS'].includes(s))) return {status: 'blocked', reason: `Expected CI check failed: ${name} (${results.join(', ')})`, url: current.url};
    if (results.some(s => s !== 'SUCCESS')) return {status: 'waiting', reason: `Expected CI check pending: ${name}`, url: current.url};
  }
  const states = checks.map(check => check.__typename === 'CheckRun'
    ? check.status !== 'COMPLETED' ? 'PENDING' : check.conclusion
    : check.state);
  if (states.some(s => !['SUCCESS', 'NEUTRAL', 'SKIPPED', 'PENDING', 'EXPECTED'].includes(s))) return {status: 'blocked', reason: 'CI failed or returned an unknown result', url: current.url};
  if (states.some(s => ['PENDING', 'EXPECTED'].includes(s))) return {status: 'waiting', reason: 'CI pending', url: current.url};
  if (!states.includes('SUCCESS')) return {status: 'blocked', reason: 'No successful CI check', url: current.url};
  if (['CHANGES_REQUESTED', 'REVIEW_REQUIRED'].includes(current.reviewDecision)) return {status: 'waiting', reason: current.reviewDecision, url: current.url};
  if (current.mergeable !== 'MERGEABLE' || !['CLEAN', 'HAS_HOOKS'].includes(current.mergeStateStatus)) return {status: 'waiting', reason: `Merge policy: ${current.mergeStateStatus}/${current.mergeable}`, url: current.url};
  return {status: 'eligible', url: current.url, head};
  };
  const decision = assess(view());
  if (!execute || decision.status !== 'eligible') return decision;
  // Refresh mutable checks, reviews and PR identity immediately before mutation.
  const fresh = assess(view());
  if (fresh.status !== 'eligible') return fresh;
  // GitHub checks the head atomically and enforces its policies. No --admin and no
  // fallback that changes protection/settings or merges a different head.
  // Immediate head-bound API merge after polling; no persistent auto-merge
  // enrollment that could later merge a different pushed revision.
  const merged = JSON.parse(run('gh', ['api', '--method', 'PUT', `repos/${repo}/pulls/${pr}/merge`, '-f', `sha=${head}`, '-f', 'merge_method=squash']));
  if (merged.merged !== true) throw Error('GitHub refused the merge; inspect PR requirements');
  const after = view();
  if (after.headRefOid !== head) throw Error('PR head changed during merge; inspect GitHub state');
  if (after.state === 'MERGED') return {status: 'merged', url: after.url, head};
  throw Error('GitHub did not confirm the merged state');
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try { const result = autoMerge(mergeOptions(process.argv.slice(2))); console.log(JSON.stringify(result)); process.exitCode = ['merged', 'eligible'].includes(result.status) ? 0 : 1; }
  catch (error) { console.error(JSON.stringify({status: 'error', error: error.message})); process.exitCode = 1; }
}
