import {runtime, context, ticket, PLAN, PLAN_REVIEW, assertTier} from './common.js';

export default async function planLoop(args, api) {
  assertTier(args.tier);
  const {ask, phase} = runtime(api, args.repoDir);
  phase('plan');
  if (args.tier === 'lite') return {converged: false, error: 'lite has no plan gate', rounds: []};
  let plan = args.draft;
  const notes = args.ownerNotes === undefined ? [] : Array.isArray(args.ownerNotes) ? args.ownerNotes : [args.ownerNotes];
  let findings = notes.map(issue => ({kind: 'sound', issue, fix: 'Reconcile the owner note in the plan.'}));
  const rounds = notes.length && plan ? [{round: 0, findings, source: 'owner'}] : [];
  const max = args.tier === 'small' ? 1 : 4;
  for (let r = 1; r <= max; r++) {
    if (!plan || findings.length) {
      const draft = await ask('planner', plan ? 'Revise the plan; address every finding explicitly. Read real code. Every step needs files, a verification and tests for new behavior.' : 'Write the simplest sufficient implementation plan after reading real code. Include files, checks, tests, alternatives, risks and open questions.', {...context(args), ticket: ticket(args.ticketText), plan, findings}, PLAN);
      if (!draft || !draft.plan.trim()) return {converged: false, plan, rounds, error: 'planner returned no valid plan'};
      plan = draft.plan;
    }
    const review = await ask('plan-review', 'Independently check SOUND and SECURE against real code. No manufactured gaps.', {repoDir: args.repoDir, plan, requirements: args.requirements, securityRules: args.config?.securityRules || []}, PLAN_REVIEW);
    if (!review) return {converged: false, plan, rounds, error: 'plan-review returned no valid result'};
    rounds.push({round: r, ...review});
    if (review.sound && review.secure && review.findings.length === 0) return {converged: true, plan, rounds};
    findings = review.findings.length ? review.findings : [{kind: review.sound ? 'secure' : 'sound', issue: review.why, fix: 'Resolve the failed verdict.'}];
  }
  return {converged: false, plan, rounds, findings};
}
