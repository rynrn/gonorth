import {execFileSync} from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';

export function scope(repoDir, base) {
  const git = args => execFileSync('git', args, {cwd: repoDir, encoding: 'utf8', maxBuffer: 32 * 1024 * 1024});
  if (!/^[a-f0-9]{40,64}$/i.test(base)) throw Error('base must be a resolved commit hash');
  const changed = git(['diff', '--no-textconv', '--no-renames', '--name-only', '-z', base, '--']).split('\0').filter(Boolean);
  const untracked = git(['ls-files', '--others', '--exclude-standard', '-z']).split('\0').filter(Boolean);
  const hash = createHash('sha256');
  hash.update(git(['rev-parse', 'HEAD']));
  hash.update(git(['diff', '--binary', '--no-textconv', '--no-ext-diff', base, '--']));
  hash.update(git(['diff', '--cached', '--binary', '--no-textconv', '--no-ext-diff', base, '--']));
  let added = 0, removed = 0;
  // numstat -z rename paths need special handling: use --no-renames for counts.
  for (const record of git(['diff', '--no-renames', '--numstat', '-z', base, '--']).split('\0').filter(Boolean)) {
    const [a, d] = record.split('\t'); added += Number(a) || 0; removed += Number(d) || 0;
  }
  for (const name of untracked) {
    const full = path.join(repoDir, name);
    const stat = fs.lstatSync(full);
    hash.update(JSON.stringify([name, stat.mode]));
    if (stat.isSymbolicLink()) { hash.update(fs.readlinkSync(full)); added++; continue; }
    const data = fs.readFileSync(full);
    hash.update(data);
    if (!data.includes(0)) added += data.length ? data.toString('utf8').split('\n').length - (data.at(-1) === 10 ? 1 : 0) : 0;
  }
  return {files: [...new Set([...changed, ...untracked])].sort(), added, removed, fingerprint: hash.digest('hex')};
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try { console.log(JSON.stringify(scope(process.argv[2], process.argv[3]))); }
  catch (e) { console.error(e.message); process.exitCode = 1; }
}
