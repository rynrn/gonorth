import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {execFileSync} from 'node:child_process';
import build from '../workflows/build.js';
import bugLoop from '../workflows/bug-loop.js';
import validate, {classify, checksFor, lensRouting} from '../workflows/validate.js';
import {validateConfig} from '../workflows/common.js';
import {scope} from '../scripts/scope.js';
import {cleanup} from '../scripts/cleanup.js';
import {evaluate} from '../hooks/guard.js';

const repoDir = process.cwd();
const args = {repoDir, root: path.join(repoDir, '.claude/skills/sdlc'), tier: 'full', config: {test: 'npm test'}, base: 'a'.repeat(40)};
const green = {pass: true, failures: [], output: 'passed'};
const red = {pass: false, failures: ['expected behavior missing'], output: 'assertion failed'};
const change = {changedFiles: ['src/a.js'], summary: 'Updated behavior', deviations: []};
const finding = {severity: 'medium', file: 'src/a.js', title: 'Zero fails', scenario: 'x=0 produces 1', fix: 'Handle zero'};
const inventory = (files = ['src/a.js'], fingerprint = 'a'.repeat(64)) => ({pass: true, failures: [], output: JSON.stringify({files, added: files.length, removed: 0, fingerprint})});
function runtime(handler) {
  const calls = [];
  return {calls, agent: async (prompt, options) => { calls.push(options); return handler(options.agentType.slice(5), options, prompt); }};
}
const isScope = label => ['changed-paths', 'scope-after-review'].includes(label);

test('new feature tests must turn green even when the full suite would omit them', async () => {
  const api = runtime((role, {label}) => {
    if (role === 'implementer') return label.startsWith('tests-') ? {tests: [{file: 'outside-suite/new.test.js', name: 'new behavior'}], command: 'node outside-suite/new.test.js', output: 'red'} : change;
    if (role === 'investigator') return {refuted: false, why: 'Fails for missing behavior'};
    return label === 'full-suite' ? green : red;
  });
  const result = await build({repoDir, plan: 'Add behavior', testCmd: 'npm test'}, api);
  assert.equal(result.status, 'testsFailing');
  assert.equal(api.calls.filter(c => c.label === 'new-tests-green').length, 2);
  assert.equal(api.calls.filter(c => c.label === 'full-suite').length, 0);
});

test('executable files and dependency inputs under docs retain review coverage', () => {
  for (const file of ['docs/build.js', 'docs/example.mdx', 'docs/requirements.txt']) {
    const result = classify([file], {});
    assert.equal(result.docsOnly, false, file);
    assert.ok(lensRouting('small', result).lensesRun.includes('code'), file);
  }
});

test('empty app command maps cannot silently remove root test coverage', () => {
  const config = {test: 'npm test', apps: [{name: 'app', path: 'app', commands: {}}]};
  assert.throws(() => validateConfig(config));
  assert.deepEqual(checksFor(['app/x.js'], classify(['app/x.js'], config), config, repoDir).map(c => c.command), ['npm test']);
});

test('no diff is a distinct noChanges outcome, not successful validation', async () => {
  const result = await validate(args, runtime(() => inventory([])));
  assert.equal(result.status, 'noChanges');
});

test('erasing a failing patch cannot turn failed validation into success', async () => {
  let round = 0;
  const api = runtime((role, {label}) => {
    if (label === 'changed-paths') return inventory(++round === 1 ? ['src/a.js'] : []);
    if (label === 'scope-after-review') return inventory();
    if (role === 'checker') return red;
    if (role === 'implementer') return change;
    return {findings: []};
  });
  const result = await validate(args, api);
  assert.equal(result.status, 'fail');
  assert.equal(result.rounds.length, 1);
});

test('a refuted finding is checked again after an intervening fix', async () => {
  let checks = 0, verifications = 0;
  const api = runtime((role, {label}) => {
    if (isScope(label)) return inventory();
    if (role === 'checker') return ++checks === 1 ? red : green;
    if (role === 'implementer') return change;
    if (role === 'security-reviewer') return {findings: []};
    if (label.startsWith('code-verify-')) return {refuted: ++verifications === 1, why: verifications === 1 ? 'Guard presently exists' : 'Fix removed the guard'};
    return {findings: [finding]};
  });
  const result = await validate(args, api);
  assert.equal(result.status, 'fail');
  assert.equal(verifications, 3);
  assert.equal(result.rounds[2].confirmedFindings[0].title, finding.title);
});

test('concurrent content edits invalidate green review and exhaust the bounded retry cap', async () => {
  let scopes = 0;
  const api = runtime((role, {label}) => {
    if (isScope(label)) return inventory(['src/a.js'], String(++scopes).repeat(64));
    if (role === 'checker') return green;
    return {findings: []};
  });
  const result = await validate(args, api);
  assert.equal(result.status, 'fail');
  assert.equal(result.rounds.length, 3);
  assert.ok(result.rounds.every(r => r.changedDuringReview));
  assert.equal(api.calls.filter(c => c.agentType === 'sdlc:implementer').length, 0);
});

test('staged rename includes sensitive source and destination, and fingerprints actual content', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-regression-scope-'));
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const git = argv => execFileSync('git', argv, {cwd: dir, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
  git(['init', '-b', 'dev/test']);
  git(['config', 'core.autocrlf', 'false']);
  git(['config', 'user.name', 'SDLC QA']); git(['config', 'user.email', 'qa@example.invalid']);
  fs.writeFileSync(path.join(dir, 'auth.js'), 'export const allowed = user => user.isAdmin;\n');
  git(['add', '.']); git(['commit', '-m', 'baseline']);
  const base = git(['rev-parse', 'HEAD']);
  git(['mv', 'auth.js', 'helpers.js']);
  const first = scope(dir, base);
  assert.deepEqual(first.files, ['auth.js', 'helpers.js']);
  assert.ok(classify(first.files, {}).sensitive.includes('auth.js'));
  assert.equal(scope(dir, base).fingerprint, first.fingerprint);
  fs.writeFileSync(path.join(dir, 'helpers.js'), 'export const allowed = () => true;\n');
  const edited = scope(dir, base);
  assert.notEqual(edited.fingerprint, first.fingerprint);
  fs.writeFileSync(path.join(dir, 'new.bin'), Buffer.from([0, 1, 2]));
  const untracked = scope(dir, base);
  fs.writeFileSync(path.join(dir, 'new.bin'), Buffer.from([0, 1, 3]));
  assert.notEqual(scope(dir, base).fingerprint, untracked.fingerprint);
});

test('guard rejects implicit matching pushes and branch mutations before commits', () => {
  const query = (_cwd, _globals, argv) => argv.includes('HEAD') ? 'dev/1-fix' : argv.includes('refs/remotes/origin/HEAD') ? 'origin/main' : null;
  for (const cmd of ['git -c push.default=matching push origin', 'git push origin', 'git checkout main && git commit -m fix', 'git switch main; git push origin HEAD']) assert.equal(evaluate(cmd, repoDir, query), false, cmd);
  assert.equal(evaluate('git push origin HEAD:refs/heads/dev/1-fix', repoDir, query), true);
  assert.equal(evaluate('git commit -m fix', repoDir, (_cwd, _globals, argv) => argv.includes('HEAD') ? 'dev/1-fix' : null), false);
});

test('cleanup refuses a pushed branch advanced beyond the merged PR head', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-regression-cleanup-'));
  const original = path.join(dir, 'original'), target = path.join(dir, 'target');
  fs.mkdirSync(original); fs.mkdirSync(target);
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const writes = [];
  const run = (exe, argv) => {
    if (exe === 'gh') return JSON.stringify({state: 'MERGED', headRefName: 'dev/1-fix', headRefOid: 'a'.repeat(40)});
    if (argv[0] === 'worktree' && argv[1] === 'list') return `worktree ${original}\nbranch refs/heads/main\n\nworktree ${target}\nbranch refs/heads/dev/1-fix\n`;
    if (argv[0] === 'status' || argv[0] === 'fetch') return '';
    if (argv[0] === 'rev-parse') return argv.includes('--abbrev-ref') ? 'origin/dev/1-fix' : 'b'.repeat(40);
    if (argv[0] === 'rev-list') return '0';
    writes.push(argv); return '';
  };
  assert.throws(() => cleanup({cwd: original, branch: 'dev/1-fix', execute: true}, run), /tip.*merged PR/);
  assert.equal(writes.length, 0);
});

test('nested shell branch mutations cannot evade the protected commit guard', () => {
  const query = (_cwd, _globals, argv) => argv.includes('HEAD') ? 'dev/1-fix' : argv.includes('refs/remotes/origin/HEAD') ? 'origin/main' : null;
  for (const command of [
    'bash -c "git switch main" && git commit -m fix',
    'bash -c "git symbolic-ref HEAD refs/heads/main"; git commit -m fix',
    `bash -c 'sh -c "git switch main"' && git commit -m fix`
  ]) assert.equal(evaluate(command, repoDir, query), false, command);
});

test('validation reruns proof commands after a repair even when verify omits them', async () => {
  let proofs = 0, fixes = 0;
  const api = runtime((role, {label}) => {
    if (isScope(label)) return inventory();
    if (label === 'verify') return green;
    if (label === 'proof-1') { proofs++; return red; }
    if (role === 'implementer') { fixes++; return change; }
    return {findings: []};
  });
  const result = await validate({...args, config: {test: 'npm test', verify: 'npm run verify'}, proofCommands: ['node outside-suite/new.test.js']}, api);
  assert.equal(result.status, 'fail');
  assert.equal(proofs, 3);
  assert.equal(fixes, 2);
  assert.ok(result.rounds.every(round => round.checks.some(check => check.name === 'proof-1' && !check.result.pass)));
});

test('a rejected fix without a concrete alternative never reaches implementation', async () => {
  const diagnosis = {symptom: 'Wrong boundary', rootCause: 'Incorrect comparison at src/a.js:1', rootLine: 'src/a.js:1', confidence: 'CONFIRMED', evidence: ['src/a.js:1 and failing repro'], proposedFix: 'Remove all guards', whyTestsMissed: 'Boundary not covered'};
  const repro = {file: 'test/a.js', name: 'zero boundary', command: 'node test/a.js', fails: true, reasonMatches: true, output: 'zero fails'};
  for (const alternative of [undefined, '', '   ']) {
    const api = runtime((role, {label}) => {
      if (role === 'investigator') return label === 'repro-reason' ? {refuted: false, why: 'Exact root cause'} : diagnosis;
      if (role === 'implementer') return label.startsWith('repro-') ? repro : change;
      if (role === 'devils-advocate') return {rootHolds: true, simplest: true, riskless: false, why: 'Removal breaks ownership checks', ...(alternative === undefined ? {} : {alternative})};
      return label === 'independent-repro' ? red : green;
    });
    const result = await bugLoop({repoDir, tier: 'small', problem: 'Wrong boundary', testCmd: 'npm test'}, api);
    assert.equal(result.status, 'notProven');
    assert.equal(api.calls.filter(call => call.label.startsWith('fix-')).length, 0);
  }
});
