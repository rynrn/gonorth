import {execFileSync} from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

export function cleanup({cwd, branch, execute = false}, run = (exe, args, dir) => execFileSync(exe, args, {cwd: dir, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']}).trim()) {
  if (!/^dev\/[a-z0-9][a-z0-9/-]*$/.test(branch || '')) throw Error('Target branch must match dev/*');
  const git = (args, dir = cwd) => run('git', args, dir);
  const blocks = git(['worktree', 'list', '--porcelain']).split(/\r?\n\r?\n/);
  const block = blocks.find(b => b.split(/\r?\n/).includes(`branch refs/heads/${branch}`));
  const target = block?.split(/\r?\n/).find(l => l.startsWith('worktree '))?.slice(9);
  if (!target || !path.isAbsolute(target) || !fs.existsSync(target)) throw Error('No target worktree');
  const realTarget = fs.realpathSync(target), realCwd = fs.realpathSync(cwd);
  if (realCwd === realTarget || realCwd.startsWith(realTarget + path.sep)) throw Error('Exit target worktree before cleanup');
  if (blocks[0]?.split(/\r?\n/)[0] === `worktree ${target}`) throw Error('Refusing original checkout');
  if (git(['status', '--porcelain', '--untracked-files=all'], target)) throw Error('Worktree has uncommitted work');
  // Refresh the upstream before trusting local tracking refs. This is read-only remotely.
  git(['fetch', '--all', '--prune']);
  const upstream = git(['rev-parse', '--abbrev-ref', `${branch}@{upstream}`]);
  if (!upstream) throw Error('Branch has no upstream');
  if (git(['rev-list', '--count', `${upstream}..${branch}`]) !== '0') throw Error('Branch has unpushed commits');
  const tip = git(['rev-parse', branch]);
  const pr = JSON.parse(run('gh', ['pr', 'view', branch, '--json', 'state,headRefName,headRefOid'], cwd));
  if (pr.state !== 'MERGED' || pr.headRefName !== branch) throw Error('PR is not MERGED for this branch');
  if (!pr.headRefOid || tip !== pr.headRefOid) throw Error('Current branch tip is not the head of the merged PR');
  if (execute) {
    git(['worktree', 'remove', target]);
    git(['branch', '-D', branch]);
    git(['worktree', 'prune']);
  }
  return {status: execute ? 'removed' : 'eligible', branch, path: target};
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const args = process.argv.slice(2), value = key => args[args.indexOf(key) + 1];
  try { console.log(JSON.stringify(cleanup({cwd: args.includes('--cwd') ? value('--cwd') : process.cwd(), branch: value('--branch'), execute: args.includes('--execute')}))); }
  catch (e) { console.error(e.message); process.exitCode = 1; }
}
