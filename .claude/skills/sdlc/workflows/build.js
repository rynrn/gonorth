import {runtime, error, TESTS, CHANGE, REFUTATION} from './common.js';

export default async function build(args, api) {
  const {ask, run, phase} = runtime(api, args.repoDir);
  phase('test-first-build');
  if (!args.plan || !args.testCmd) return error('plan/testCmd required');
  let tests, confirmation;
  for (let attempt = 1; attempt <= 2; attempt++) {
    tests = await ask('implementer', 'Write ONLY tests for every new behavior in this approved plan. No production edits. Run the targeted command. Tests must fail for missing behavior, not broken setup. On rewrite retain all intended coverage.', {plan: args.plan, previousTests: tests, previousResult: confirmation}, TESTS, `tests-${attempt}`);
    if (!tests || !tests.command.trim() || !tests.tests.length) return error('tests first');
    confirmation = await run(tests.command, 'independent-red');
    if (!confirmation) return error('independent red');
    if (!confirmation.pass) {
      const reason = await ask('investigator', 'Read the new tests and independent failure output. Confirm they fail for the missing planned behavior. Set refuted=true for syntax, typo, dependency, setup or unrelated failure. Do not edit.', {plan: args.plan, tests, confirmation}, REFUTATION, 'red-reason');
      if (!reason) return error('red reason');
      if (!reason.refuted) break;
      confirmation = {...confirmation, reason: reason.why};
    }
    if (attempt === 2) return {status: 'testsNotFailing', tests, confirmation};
  }
  let failures = [];
  for (let attempt = 1; attempt <= 2; attempt++) {
    const change = await ask('implementer', 'Implement the approved plan. New tests must pass; never weaken or remove assertions. Do not widen scope. Report deviations.', {plan: args.plan, tests, failures}, CHANGE, `implement-${attempt}`);
    if (!change) return error('implementation');
    const targeted = await run(tests.command, 'new-tests-green');
    if (!targeted) return error('new tests green');
    const suite = targeted.pass ? await run(args.testCmd, 'full-suite') : targeted;
    if (!suite) return error('full suite');
    if (suite.pass) return {status: 'built', tests: tests.tests, proofCommands: [tests.command], red: confirmation, changedFiles: change.changedFiles, deviations: change.deviations};
    failures = suite.failures.length ? suite.failures : [suite.output];
  }
  return {status: 'testsFailing', failures};
}
