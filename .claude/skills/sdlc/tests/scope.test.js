import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {execFileSync} from 'node:child_process';
import {scope} from '../scripts/scope.js';
import {cleanup} from '../scripts/cleanup.js';

test('scope includes renamed, deleted and untracked paths including spaced names', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-scope-'));
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const git = args => execFileSync('git', args, {cwd: dir, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
  git(['init', '-b', 'dev/test']); git(['config', 'user.name', 'SDLC Test']); git(['config', 'user.email', 'test@example.invalid']);
  fs.writeFileSync(path.join(dir, 'before.txt'), 'old\n'); git(['add', '.']); git(['commit', '-m', 'baseline']);
  const base = git(['rev-parse', 'HEAD']);
  fs.renameSync(path.join(dir, 'before.txt'), path.join(dir, 'after file.txt'));
  fs.writeFileSync(path.join(dir, 'new file.txt'), 'one\ntwo\n');
  const inventory = scope(dir, base);
  assert.deepEqual(inventory.files, ['after file.txt', 'before.txt', 'new file.txt']);
  assert.ok(inventory.added >= 3); assert.ok(inventory.removed >= 1);
  assert.throws(() => scope(dir, '--bad-option'));
});
test('scope fingerprints raw Git changes even when a textconv driver hides differences', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-textconv-'));
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const git = args => execFileSync('git', args, {cwd: dir, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim();
  git(['init', '-b', 'dev/test']);
  git(['config', 'user.name', 'SDLC Test']);
  git(['config', 'user.email', 'test@example.invalid']);
  git(['config', 'core.autocrlf', 'false']);
  fs.writeFileSync(path.join(dir, 'a.js'), 'before\n');
  fs.writeFileSync(path.join(dir, '.gitattributes'), 'a.js diff=hidden\n');
  fs.writeFileSync(path.join(dir, 'converter.cjs'), 'process.stdout.write("same\\n")');
  git(['config', 'diff.hidden.textconv', 'node converter.cjs']);
  git(['add', '.']); git(['commit', '-m', 'baseline']);
  const base = git(['rev-parse', 'HEAD']);
  fs.writeFileSync(path.join(dir, 'a.js'), 'after 1\n');
  const first = scope(dir, base);
  assert.equal(git(['diff', '--textconv', base, '--', 'a.js']), '', 'driver really hides the content change');
  assert.equal(scope(dir, base).fingerprint, first.fingerprint, 'unchanged content has a stable fingerprint');
  fs.writeFileSync(path.join(dir, 'a.js'), 'after 2\n');
  assert.notEqual(scope(dir, base).fingerprint, first.fingerprint, 'textconv cannot hide edited content from the validation receipt');
});
test('cleanup refuses branches outside dev before any command executes', () => {
  assert.throws(() => cleanup({cwd: process.cwd(), branch: 'main'}, () => {throw Error('must not run');}), /dev/);
});
test('cleanup checks dirty work, upstream, unpushed commits and merged PR before deletion', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-cleanup-'));
  const original = path.join(dir, 'original'), target = path.join(dir, 'target');
  fs.mkdirSync(original); fs.mkdirSync(target);
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const blocks = `worktree ${original}\nbranch refs/heads/main\n\nworktree ${target}\nbranch refs/heads/dev/1-fix\n`;
  const opts = {cwd: original, branch: 'dev/1-fix', execute: true};
  for (const failure of ['dirty', 'upstream', 'unpushed', 'pr', 'reused', 'none']) {
    const mutations = [];
    const run = (exe, args) => {
      if (exe === 'gh') return JSON.stringify({state: failure === 'pr' ? 'OPEN' : 'MERGED', headRefName: 'dev/1-fix', headRefOid: failure === 'reused' ? 'old' : 'tip'});
      if (args[0] === 'worktree' && args[1] === 'list') return blocks;
      if (args[0] === 'status') return failure === 'dirty' ? '?? unsaved.txt' : '';
      if (args[0] === 'rev-parse') { if (failure === 'upstream') throw Error('No upstream'); return args[1] === '--abbrev-ref' ? 'origin/dev/1-fix' : 'tip'; }
      if (args[0] === 'rev-list') return failure === 'unpushed' ? '1' : '0';
      if (args[0] !== 'fetch') mutations.push(args.slice(0, 2).join(' '));
      return '';
    };
    if (failure === 'none') {
      assert.equal(cleanup(opts, run).status, 'removed');
      assert.deepEqual(mutations, ['worktree remove', 'branch -D', 'worktree prune']);
    } else { assert.throws(() => cleanup(opts, run)); assert.equal(mutations.length, 0); }
  }
});
