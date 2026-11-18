import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {spawnSync} from 'node:child_process';

const plugin = path.resolve(import.meta.dirname, '..');
const roles = ['checker', 'investigator', 'planner', 'plan-review', 'devils-advocate', 'implementer', 'code-reviewer', 'security-reviewer', 'spec-reviewer'];

function fixture(t, newline, mutation) {
  const tempRoot = fs.realpathSync(os.tmpdir());
  const dir = fs.mkdtempSync(path.join(tempRoot, 'sdlc-package-check-'));
  t.after(() => {
    const resolved = fs.realpathSync(dir);
    assert.equal(path.dirname(resolved), tempRoot);
    assert.ok(path.basename(resolved).startsWith('sdlc-package-check-'));
    fs.rmSync(resolved, {recursive: true, force: true});
  });
  fs.mkdirSync(path.join(dir, 'scripts'));
  fs.mkdirSync(path.join(dir, 'workflows'));
  fs.mkdirSync(path.join(dir, 'agents'));
  fs.writeFileSync(path.join(dir, 'package.json'), '{"type":"module"}');
  // Run the real checker and its real config validator, not duplicated predicates.
  fs.copyFileSync(path.join(plugin, 'scripts/check-package.js'), path.join(dir, 'scripts/check-package.js'));
  fs.copyFileSync(path.join(plugin, 'workflows/common.js'), path.join(dir, 'workflows/common.js'));
  for (const role of roles) {
    let text = fs.readFileSync(path.join(plugin, 'agents', role + '.md'), 'utf8').replaceAll('\r\n', '\n');
    if (mutation?.role === role) text = mutation.apply(text);
    fs.writeFileSync(path.join(dir, 'agents', role + '.md'), text.replaceAll('\n', newline));
  }
  return dir;
}

test('package checker accepts LF and CRLF roles without weakening metadata restrictions', async t => {
  const cases = [
    {name: 'valid roles', expectedStatus: 0, expected: /Validated \d+ plugin\/skill files/},
    {name: 'wrong role name', expectedStatus: 1, expected: /Invalid role planner/, mutation: {role: 'planner', apply: text => text.replace('name: planner\n', 'name: wrong\n')}},
    {name: 'invalid model', expectedStatus: 1, expected: /Invalid role planner/, mutation: {role: 'planner', apply: text => text.replace(/^model: .+$/m, 'model: unknown')}},
    {name: 'unrestricted tools', expectedStatus: 1, expected: /Unrestricted role planner/, mutation: {role: 'planner', apply: text => text.replace('tools: Read, Grep, Glob\n', 'tools: Read, Grep, Glob, Bash\n')}}
  ];
  for (const [encoding, newline] of [['LF', '\n'], ['CRLF', '\r\n']]) {
    for (const scenario of cases) {
      await t.test(`${encoding}: ${scenario.name}`, sub => {
        const dir = fixture(sub, newline, scenario.mutation);
        const result = spawnSync(process.execPath, [path.join(dir, 'scripts/check-package.js')], {cwd: dir, encoding: 'utf8'});
        assert.ifError(result.error);
        const output = result.stdout + result.stderr;
        assert.equal(result.status, scenario.expectedStatus, output);
        assert.match(output, scenario.expected);
      });
    }
  }
});
