import path from 'node:path';
import {runtime, error, matches, SCOPE, TIER, REVIEW, REFUTATION, CHANGE, validateConfig, assertTier} from './common.js';

const tiers = ['lite', 'small', 'full'];
const sensitive = /auth|login|session|token|secret|crypt|password|permission|acl|tenant|oauth|saml|jwt|(^|\/)(Dockerfile|Jenkinsfile)(\.|$)|(^|\/)\.github\/|(^|\/)(package(?:-lock)?\.json|requirements[^/]*\.txt|pyproject\.toml|poetry\.lock|uv\.lock|yarn\.lock|pnpm-lock\.yaml)$|(^|\/)(hooks?|middleware)\//i;
export function classify(files, config) {
  return {
    docsOnly: files.every(f => /\.(md|txt|rst)$/i.test(f) && !/requirements[^/]*\.txt$/i.test(f)),
    spec: files.some(f => /(^|\/)(SKILL|AGENTS)\.md$/.test(f) || /(^|\/)(skills|agents|commands)\/.*\.md$/.test(f)),
    sensitive: files.filter(f => sensitive.test(f) || (config.sensitivePaths || []).some(re => new RegExp(re).test(f))),
    apps: (config.apps || []).filter(app => files.some(f => under(f, app.path)))
  };
}
const under = (file, dir) => dir === '.' || file === dir || file.startsWith(dir.replace(/\/$/, '') + '/');
export function checksFor(files, scope, config, repoDir) {
  if (config.verify) return [{name: 'verify', command: config.verify, cwd: repoDir}];
  if (!scope.apps.length) return config.test ? [{name: 'test', command: config.test, cwd: repoDir}] : [];
  const checks = scope.apps.flatMap(app => Object.entries(app.commands).map(([name, command]) => ({name: `${app.name}:${name}`, command, cwd: path.resolve(repoDir, app.path)})));
  if (config.test && (!checks.length || files.some(f => !scope.apps.some(app => under(f, app.path))))) checks.push({name: 'test', command: config.test, cwd: repoDir});
  return checks;
}
export function lensRouting(tier, scope) {
  const lensesRun = [], lensesSkipped = [];
  for (const [lens, enabled, why] of [
    ['code', tier !== 'lite' && !scope.docsOnly, scope.docsOnly ? 'docs-only' : 'lite tier'],
    ['security', scope.sensitive.length > 0 || tier === 'full' && !scope.docsOnly, 'no sensitive paths; not full non-docs'],
    ['spec', scope.spec, 'no executable prose touched']
  ]) enabled ? lensesRun.push(lens) : lensesSkipped.push({lens, reason: why});
  return {lensesRun, lensesSkipped};
}

export default async function validate(args, api) {
  assertTier(args.tier);
  validateConfig(args.config);
  if (args.proofCommands !== undefined && (!Array.isArray(args.proofCommands) || args.proofCommands.some(cmd => typeof cmd !== 'string' || !cmd.trim()))) return error('Invalid proofCommands');
  const {ask, run, runArgs, phase, log} = runtime(api, args.repoDir);
  phase('validate');
  const rounds = [], lows = new Map(), refuted = new Set(), unresolved = new Map();
  let routing = {lensesRun: [], lensesSkipped: []};
  const finish = extra => ({...extra, rounds, ...routing, lowFindings: [...lows.values()]});
  for (let r = 1; r <= 3; r++) {
    refuted.clear(); // An intervening fix can invalidate any earlier refutation.
    const scopeRun = await runArgs('node', [path.join(args.root, 'scripts/scope.js'), args.repoDir, args.base], 'changed-paths');
    if (!scopeRun || !scopeRun.pass) return finish(error('scope'));
    let inventory;
    try { inventory = JSON.parse(scopeRun.output); } catch { return finish(error('scope JSON')); }
    if (!matches(inventory, SCOPE)) return finish(error('scope schema'));
    const files = inventory.files;
    if (!files.length) return finish({status: rounds.length ? 'fail' : 'noChanges', reason: 'No change remains; removing the patch does not prove the task is solved.'});
    const scope = classify(files, args.config);
    if (args.tier !== 'full') {
      const tier = await ask('code-reviewer', 'Judge the tier from the ACTUAL diff including untracked files. lite=no behavior change and no sensitive paths; non-string config is not lite. small=one obvious behavior in one area; full=cross-module, auth/data/security or unclear. Unsure -> higher. Size alone never escalates.', {base: args.base, files}, TIER, 'tier-recheck');
      let to = tier?.tier || tiers[tiers.indexOf(args.tier) + 1];
      if (args.tier === 'lite' && scope.sensitive.length && to === 'lite') to = 'small';
      if (tiers.indexOf(to) > tiers.indexOf(args.tier)) return finish({status: 'escalate', to, reason: tier?.why || 'tier reviewer returned no valid result', files, sensitive: scope.sensitive});
    }
    routing = lensRouting(args.tier, scope);
    if (args.gradedApproach && !routing.lensesRun.includes('code')) log('Graded approach check skipped: code lens is off.');
    const checks = checksFor(files, scope, args.config, args.repoDir);
    for (const [i, command] of (args.proofCommands || []).entries()) {
      if (!checks.some(c => c.command === command && c.cwd === args.repoDir)) checks.push({name: `proof-${i + 1}`, command, cwd: args.repoDir});
    }
    if (!checks.length) return finish(error('No selected checks; run /sdlc:init.'));
    const checkPromise = Promise.all(checks.map(async check => ({...check, result: await run(check.command, check.name, 'checks', check.cwd)})));
    const lensPromises = routing.lensesRun.map(async lens => {
      const instruction = `Review the full diff vs base plus all untracked files using your ${lens} lens. Concrete input/state -> wrong outcome required. No style findings. ${lens === 'code' && !routing.lensesRun.includes('security') ? 'security-reviewer is NOT running — cover injection/auth/secrets too.' : ''} ${lens === 'code' && args.gradedApproach ? 'Flag any departure from the approved graded approach as medium, with approach vs actual code at file:line.' : ''}`;
      const review = await ask(`${lens}-reviewer`, instruction, {base: args.base, files, securityRules: args.config.securityRules || [], gradedApproach: lens === 'code' ? args.gradedApproach : undefined}, REVIEW, `${lens}-review-${r}`);
      if (!review) return [{lens, severity: 'high', file: '(review)', title: 'reviewer returned nothing', scenario: 'Missing review output would otherwise be counted as clean.', fix: 'Obtain a valid independent review.', synthetic: true}];
      const findings = await Promise.all(review.findings.map(async f => {
        const finding = {...f, lens};
        const key = `${lens}|${f.file}|${f.title}`;
        if (f.severity === 'low') { if (!lows.has(key)) lows.set(key, finding); return null; }
        if (refuted.has(key)) return null;
        const verification = await ask(`${lens}-reviewer`, 'You are a SECOND independent agent. Try to REFUTE this finding by reading code, callers, mitigations and reachability. Set refuted=true ONLY with concrete evidence disproving the failure scenario. Uncertainty or missing access means refuted=false and explain what remains unknown. For a graded-approach departure, refute only if actual code follows the approved approach. Do not edit.', {finding, base: args.base, files, gradedApproach: args.gradedApproach}, REFUTATION, `${lens}-verify-${r}`);
        if (verification?.refuted) { refuted.add(key); unresolved.delete(key); return null; }
        return {...finding, verification: verification || {refuted: false, why: 'Verifier returned no valid output; kept.'}};
      }));
      return findings.filter(Boolean);
    });
    const [checkResults, lensResults] = await Promise.all([checkPromise, Promise.all(lensPromises)]);
    const current = new Map(lensResults.flat().map(f => [`${f.lens}|${f.file}|${f.title}`, f]));
    // A reviewer omitting an earlier confirmed finding does not prove that it was fixed.
    for (const [key, f] of unresolved) {
      if (current.has(key) || refuted.has(key) || f.synthetic) continue;
      const verification = await ask(`${f.lens}-reviewer`, 'Independently verify whether the previously confirmed failure is now fixed. Refute only with concrete evidence from the current code. Graded departure is refuted only if code follows the approach.', {finding: f, base: args.base, files, gradedApproach: args.gradedApproach}, REFUTATION, `recheck-${r}`);
      if (verification?.refuted) refuted.add(key); else current.set(key, {...f, verification});
    }
    unresolved.clear();
    for (const [key, finding] of current) unresolved.set(key, finding);
    const failedChecks = checkResults.filter(c => !c.result?.pass);
    const confirmedFindings = [...current.values()];
    const afterRun = await runArgs('node', [path.join(args.root, 'scripts/scope.js'), args.repoDir, args.base], 'scope-after-review');
    let after;
    try { after = afterRun?.pass ? JSON.parse(afterRun.output) : null; } catch { after = null; }
    if (!matches(after, SCOPE)) return finish(error('post-review scope'));
    const changedDuringReview = after.fingerprint !== inventory.fingerprint;
    rounds.push({round: r, files, fingerprint: inventory.fingerprint, changedDuringReview, checks: checkResults, confirmedFindings, ...routing});
    if (changedDuringReview) { log('Content changed during checks/review; discard green evidence and rerun.'); continue; }
    if (!failedChecks.length && !confirmedFindings.length) return finish({status: 'pass', fingerprint: inventory.fingerprint});
    if (r === 3) break;
    const change = await ask('implementer', 'Fix EXACTLY these failed checks and confirmed findings, nothing else. Never weaken tests or check configuration. If a finding is wrong explain why instead of making a cosmetic fix. Do not attempt to fix a synthetic missing-review finding by editing code.', {failedChecks, confirmedFindings, files}, CHANGE, `validate-fix-${r}`);
    if (!change) return finish(error('validate fix'));
  }
  return finish({status: 'fail'});
}
