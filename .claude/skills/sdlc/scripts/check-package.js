import fs from 'node:fs';
import path from 'node:path';
import {execFileSync} from 'node:child_process';
import {validateConfig} from '../workflows/common.js';

const root = path.resolve(import.meta.dirname, '..');
function walk(dir) { return fs.readdirSync(dir, {withFileTypes: true}).flatMap(e => e.isDirectory() ? walk(path.join(dir, e.name)) : [path.join(dir, e.name)]); }
const files = walk(root);
for (const file of files) {
  const text = fs.readFileSync(file, 'utf8');
  if (file.endsWith('.json')) JSON.parse(text);
  if (file.endsWith('.js')) execFileSync(process.execPath, ['--check', file], {stdio: 'pipe'});
  if (file.endsWith('SKILL.md')) {
    if (!/^---\r?\nname: [a-z0-9-]+\r?\ndescription: .+\r?\n---/.test(text)) throw Error(`Invalid frontmatter: ${file}`);
    if (/\[TODO|\[INSERT|PLACEHOLDER/.test(text)) throw Error(`Unfinished scaffold: ${file}`);
  }
  if (file.endsWith('.md')) {
    for (const [, ref] of text.matchAll(/\[[^\]]+\]\(([^)]+)\)/g)) {
      if (/^(https?:|#)/.test(ref)) continue;
      const target = path.resolve(path.dirname(file), ref.split('#')[0]);
      if (!fs.existsSync(target)) throw Error(`Broken reference in ${file}: ${ref}`);
    }
  }
}
for (const name of ['checker', 'investigator', 'planner', 'plan-review', 'devils-advocate', 'implementer', 'code-reviewer', 'security-reviewer', 'spec-reviewer']) {
  const text = fs.readFileSync(path.join(root, 'agents', name + '.md'), 'utf8').replaceAll('\r\n', '\n');
  if (!text.includes(`name: ${name}\n`) || !/^model: (opus|sonnet|haiku)$/m.test(text)) throw Error(`Invalid role ${name}`);
  if (['planner', 'plan-review', 'devils-advocate'].includes(name) && !text.includes('tools: Read, Grep, Glob\n')) throw Error(`Unrestricted role ${name}`);
}
validateConfig({test: 'node --test tests/*.test.js'});
console.log(`Validated ${files.length} plugin/skill files, JSON, JS syntax, role restrictions, links and project config.`);
