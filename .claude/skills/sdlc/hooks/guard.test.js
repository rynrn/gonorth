import test from 'node:test';
import assert from 'node:assert/strict';
import {evaluate, hook} from './guard.js';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {execFileSync, spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
const cwd = process.cwd();
const query = (branch = 'dev/1-fix', defaultBranch = 'trunk') => (dir, globals, args) => {
  if (args.includes('HEAD')) return branch;
  if (args.includes('refs/remotes/origin/HEAD')) return `origin/${defaultBranch}`;
  return null;
};
for (const command of ['git commit -m fix', 'git push -u origin HEAD:refs/heads/dev/1-fix', 'git push origin dev/1-fix', 'git status', 'echo hello', 'echo "git push origin main"', 'env FOO=x command git push origin dev/1-fix']) {
  test(`allows dev branch operation: ${command}`, () => assert.equal(evaluate(command, cwd, query()), true));
}
for (const command of ['git push origin main', 'git push origin HEAD:master', 'git push origin +HEAD:refs/heads/trunk', 'git push --all origin', 'git push --mirror', 'git push origin :main', 'git push --delete origin master', 'git push origin :', 'git push origin "ma\"\"ster"', 'git push origin \\master', 'gh pr merge 1', 'gh -R org/repo pr merge 1', 'gh api repos/o/r/pulls/1/merge -X PUT', 'bash -c "git push origin main"', 'env A=x sudo -u root command git push origin trunk', 'git push origin $(cat branch)', 'git push origin "main', 'git push --repo=origin HEAD:main', 'git push origin refs/heads/*:refs/heads/*', 'GIT_WORK_TREE=/tmp git commit -m x']) {
  test(`blocks dangerous operation: ${command}`, () => assert.equal(evaluate(command, cwd, query()), false));
}
test('protected branches forbid commits and implicit pushes', () => {
  for (const branch of ['main', 'master', 'trunk']) {
    assert.equal(evaluate('git commit -m x', cwd, query(branch)), false);
    assert.equal(evaluate('git push origin', cwd, query(branch)), false);
    assert.equal(evaluate('git push origin HEAD', cwd, query(branch)), false);
  }
});
test('git -C and global options resolve the actual repository', () => {
  let actual;
  const q = (dir, globals, args) => { actual = {dir, globals}; return query('main')(dir, globals, args); };
  assert.equal(evaluate('git -C child --git-dir .git --work-tree . -c user.name=x commit -m x', cwd, q), false);
  assert.ok(actual.dir.endsWith('child'));
  assert.ok(actual.globals.includes('--git-dir'));
});
test('cd followed by git checks the new directory', () => {
  let actual;
  assert.equal(evaluate('cd child && git commit -m x', cwd, (dir, globals, args) => { actual = dir; return query('main')(dir, globals, args); }), false);
  assert.ok(actual.endsWith('child'));
});
test('detached or unreadable repository fails dangerous git commands', () => {
  assert.equal(evaluate('git commit -m x', cwd, () => null), false);
  assert.equal(evaluate('git push origin dev/x', cwd, () => null), false);
});
test('malformed harmless shell is permitted, malformed dangerous shell is denied', () => {
  assert.equal(evaluate('echo "unterminated', cwd, query()), true);
  assert.equal(evaluate('git push "unterminated', cwd, query()), false);
});
test('malformed hook input fails closed', () => {
  for (const value of [null, {}, {cwd}, {tool_input: {command: 'echo x'}}]) assert.equal(hook(value), false);
});
test('real hook CLI checks the actual branch and emits exit 2 with deny JSON', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-guard-'));
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const git = args => execFileSync('git', args, {cwd: dir, stdio: ['ignore', 'pipe', 'pipe']});
  git(['init', '-b', 'main']);
  git(['symbolic-ref', 'refs/remotes/origin/HEAD', 'refs/remotes/origin/main']);
  const guard = fileURLToPath(new URL('./guard.js', import.meta.url));
  const invoke = command => spawnSync(process.execPath, [guard], {input: JSON.stringify({cwd: dir, tool_input: {command}}), encoding: 'utf8'});
  const denied = invoke('git commit -m x');
  assert.equal(denied.status, 2);
  assert.equal(JSON.parse(denied.stdout).hookSpecificOutput.permissionDecision, 'deny');
  git(['symbolic-ref', 'HEAD', 'refs/heads/dev/1-test']);
  assert.equal(invoke('git commit -m x').status, 0);
  assert.equal(invoke('gh pr merge 1').status, 2);
});
