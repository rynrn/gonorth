import {runtime, ticket, error, DIAGNOSIS, REPRO, CHALLENGE, CHANGE, REFUTATION, assertTier} from './common.js';

export default async function bugLoop(args, api) {
  assertTier(args.tier);
  const {ask, run, runArgs, phase} = runtime(api, args.repoDir);
  phase('diagnose-prove-challenge');
  if (args.tier === 'lite' || !args.testCmd) return error('non-lite tier and testCmd required');
  const daRounds = [];
  let diagnosis, test, challenge, red;
  for (let r = 1; r <= (args.tier === 'small' ? 1 : 5); r++) {
    diagnosis = await ask('investigator', 'Diagnose at the root line with evidence. Do not fix. On later rounds execute the prior distinguishing observation using only read-only operations and use its actual result. A lead is a hypothesis, not truth.', {ticket: ticket([args.problem, args.ticketText].filter(Boolean).join('\n')), lead: args.lead, previousChallenge: challenge}, DIAGNOSIS, `diagnose-${r}`);
    if (!diagnosis) return error('diagnosis', {daRounds});
    let reproduced = false;
    for (let attempt = 1; attempt <= 2; attempt++) {
      test = await ask('implementer', 'Write ONE regression test, tests only; never fix production code. Run it. It must fail for exactly the diagnosed reason. Reuse or revise the earlier test rather than leaving unrelated failing experiments.', {diagnosis, priorTest: test, priorOutput: red}, REPRO, `repro-${r}-${attempt}`);
      if (!test || !test.command.trim()) return error('repro', {daRounds});
      red = await run(test.command, 'independent-repro');
      if (!red) return error('independent repro', {daRounds});
      if (!red.pass && test.fails && test.reasonMatches) {
        const reason = await ask('investigator', 'Independently verify that this failing test demonstrates exactly this root cause; refute setup, typo, dependency or unrelated failures. Do not edit.', {diagnosis, test, red}, REFUTATION, 'repro-reason');
        if (!reason) return error('repro reason', {daRounds});
        if (!reason.refuted) { reproduced = true; break; }
        red = {...red, reason: reason.why};
      }
    }
    if (!reproduced) return {status: 'couldNotReproduce', diagnosis, test, daRounds, red};
    const rootFile = diagnosis.rootLine.replace(/:\d+(?::\d+)?$/, '');
    let history = 'Root file is not a safe relative path; history unavailable.';
    if (/^[\w./-]+$/.test(rootFile) && !rootFile.startsWith('/') && !rootFile.split('/').includes('..')) {
      const result = await runArgs('git', ['log', '-n', '20', '--oneline', '--', rootFile], 'root-history');
      if (!result || !result.pass) return error('root history', {diagnosis, test, daRounds});
      history = result.output;
    }
    challenge = await ask('devils-advocate', 'Challenge independently. Default is not proven. Answer the nine questions in your role. No shell commands. When not proven give one distinguishing read-only observation. Evaluate root, simplest sufficient shape and least risk.', {symptom: diagnosis.symptom, rootCause: diagnosis.rootCause, rootLine: diagnosis.rootLine, evidence: diagnosis.evidence, repro: {test, actualOutput: red.output}, proposedFix: diagnosis.proposedFix, history, ticket: ticket(args.ticketText)}, CHALLENGE, `challenge-${r}`);
    if (!challenge) return error('challenge', {diagnosis, test, daRounds});
    daRounds.push({round: r, diagnosis, test, red, challenge});
    if (challenge.rootHolds) break;
  }
  if (!challenge?.rootHolds) return {status: 'notProven', diagnosis, test, daRounds};
  if ((!challenge.simplest || !challenge.riskless) && !challenge.alternative?.trim()) return {status: 'notProven', diagnosis, test, daRounds, reason: 'Rejected fix has no concrete safer/simpler alternative.'};
  const gradedApproach = challenge.simplest && challenge.riskless ? diagnosis.proposedFix : `Use this replacement approach from the challenge: ${challenge.alternative}`;
  let failures = [];
  for (let attempt = 1; attempt <= 2; attempt++) {
    const change = await ask('implementer', 'Apply the smallest sufficient change at the root line using the graded approach. Repro must pass; never weaken it. No existing tests may break. No adjacent fixes.', {diagnosis, gradedApproach, test, failures}, CHANGE, `fix-${attempt}`);
    if (!change) return error('fix', {diagnosis, test, daRounds});
    const repro = await run(test.command, 'repro-green');
    if (!repro) return error('repro green', {diagnosis, test, daRounds});
    const suite = repro.pass ? await run(args.testCmd, 'suite-green') : repro;
    if (!suite) return error('suite green', {diagnosis, test, daRounds});
    if (suite.pass) return {status: 'fixed', diagnosis, test, proofCommands: [test.command], gradedApproach, fixSummary: change.summary, changedFiles: change.changedFiles, daRounds};
    failures = suite.failures.length ? suite.failures : [suite.output];
  }
  return {status: 'testsFailing', diagnosis, test, daRounds, failures};
}
