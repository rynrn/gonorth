import test from 'node:test';
import assert from 'node:assert/strict';
import path from 'node:path';
import {autoMerge, mergeOptions} from '../scripts/auto-merge.js';
import {evaluate} from '../hooks/guard.js';
const head = 'a'.repeat(40), options = {repo: 'owner/repo', pr: '123', head, checks: ['sdlc-verify'], execute: true};
const success = {__typename: 'CheckRun', name: 'sdlc-verify', status: 'COMPLETED', conclusion: 'SUCCESS'};
function scenario(change = {}, local = {}) {
  let merged = false; const writes = [];
  const pr = {number: 123, url: 'https://github.com/owner/repo/pull/123', state: 'OPEN', isDraft: false, headRefName: 'dev/123-fix', headRefOid: head, baseRefName: 'main', isCrossRepository: false, mergeable: 'MERGEABLE', mergeStateStatus: 'CLEAN', reviewDecision: '', statusCheckRollup: [success], ...change};
  const run = (exe, args) => {
    if (exe === 'git') return args[0] === 'remote' ? local.origin ?? 'https://github.com/owner/repo.git' : args[0] === 'branch' ? local.branch || 'dev/123-fix' : args[0] === 'rev-parse' ? local.head || head : local.dirty || '';
    if (args[0] === 'repo') return JSON.stringify({nameWithOwner: 'owner/repo', defaultBranchRef: {name: 'main'}});
    if (args[0] === 'api') { writes.push(args); merged = true; return JSON.stringify({merged: true}); }
    return JSON.stringify(merged ? {...pr, state: 'MERGED'} : pr);
  }; return {run, writes};
}
test('auto merge pins the validated head and verifies the actual result', () => {
  const s = scenario(); assert.equal(autoMerge(options, s.run).status, 'merged');
  assert.deepEqual(s.writes[0], ['api', '--method', 'PUT', 'repos/owner/repo/pulls/123/merge', '-f', `sha=${head}`, '-f', 'merge_method=squash']);
});
test('pending, failed, empty, skipped-only and review-required checks do not merge', () => {
  for (const change of [
    {statusCheckRollup: []}, {statusCheckRollup: [{...success, status: 'IN_PROGRESS'}]},
    {statusCheckRollup: [{...success, conclusion: 'FAILURE'}]},
    {statusCheckRollup: [{...success, conclusion: 'SKIPPED'}]},
    {reviewDecision: 'CHANGES_REQUESTED'}, {reviewDecision: 'REVIEW_REQUIRED'},
    {mergeable: 'CONFLICTING'}, {mergeStateStatus: 'BLOCKED'}
  ]) { const s = scenario(change); assert.ok(['waiting', 'blocked'].includes(autoMerge(options, s.run).status)); assert.equal(s.writes.length, 0); }
});
test('changed head, draft, wrong PR/repository/base and dirty checkout stop merging', () => {
  for (const change of [{headRefOid: 'b'.repeat(40)}, {isDraft: true}, {number: 124}, {isCrossRepository: true}, {baseRefName: 'release'}]) {
    const s = scenario(change); assert.throws(() => autoMerge(options, s.run)); assert.equal(s.writes.length, 0);
  }
  for (const local of [{head: 'b'.repeat(40)}, {branch: 'main'}, {dirty: ' M file'}]) { const s = scenario({}, local); assert.throws(() => autoMerge(options, s.run)); assert.equal(s.writes.length, 0); }
});
test('preflight checks eligibility without writing; rejects unknown CLI flags', () => {
  const s = scenario(); assert.equal(autoMerge({...options, execute: false}, s.run).status, 'eligible'); assert.equal(s.writes.length, 0);
  assert.throws(() => mergeOptions(['--admin']));
});
test('guard permits only exact installed helper and keeps direct merges blocked', () => {
  const helper = path.resolve(import.meta.dirname, '../scripts/auto-merge.js').replaceAll('\\', '/');
  assert.equal(evaluate(`node "${helper}" --repo owner/repo --pr 123 --head ${head} --check sdlc-verify --execute`, process.cwd()), true);
  assert.equal(evaluate(`node "${helper}" --repo owner/repo --pr 123 --head ${head} --admin`, process.cwd()), false);
  assert.equal(evaluate('gh pr merge 123 --auto --squash', process.cwd()), false);
});
test('an unrelated successful check cannot replace the expected test job', () => {
  const s = scenario({statusCheckRollup: [{...success, name: 'unrelated'}]});
  assert.equal(autoMerge(options, s.run).status, 'waiting'); assert.equal(s.writes.length, 0);
});
test('terminal expected failures block immediately instead of polling', () => {
  for (const conclusion of ['FAILURE', 'CANCELLED', 'TIMED_OUT', 'SKIPPED', 'NEUTRAL']) {
    const s = scenario({statusCheckRollup: [{...success, conclusion}]});
    assert.equal(autoMerge(options, s.run).status, 'blocked'); assert.equal(s.writes.length, 0);
  }
});
test('a head changed during the final refresh never merges', () => {
  const s = scenario(); let views = 0;
  const run = (exe, args) => { const value = s.run(exe, args); if (exe === 'gh' && args[0] === 'pr' && args[1] === 'view' && ++views === 2) return JSON.stringify({...JSON.parse(value), headRefOid: 'b'.repeat(40)}); return value; };
  assert.throws(() => autoMerge(options, run)); assert.equal(s.writes.length, 0);
});

test('local origin must identify the requested repository even with an identical head', () => {
  for (const origin of ['https://github.com/another/repo.git', 'https://example.com/owner/repo.git', '', 'https://github.com/owner/repo.git/extra']) {
    const s = scenario({}, {origin}); assert.throws(() => autoMerge(options, s.run), /Local origin/); assert.equal(s.writes.length, 0);
  }
  for (const origin of ['https://github.com/Owner/Repo', 'git@github.com:owner/repo.git', 'ssh://git@github.com/owner/repo.git']) {
    const s = scenario({}, {origin}); assert.equal(autoMerge(options, s.run).status, 'merged');
  }
});
