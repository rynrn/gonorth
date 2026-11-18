import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {spawn} from 'node:child_process';
import readline from 'node:readline';
import {fileURLToPath} from 'node:url';

const runner = fileURLToPath(new URL('../scripts/runner.js', import.meta.url));
const repoDir = process.cwd();
const root = fileURLToPath(new URL('../', import.meta.url));
const sound = {sound: true, secure: true, findings: [], why: 'Sound and secure'};
function host(t, workflow, args, handle, flags = []) {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'sdlc-runner-'));
  t.after(() => fs.rmSync(dir, {recursive: true, force: true}));
  const argsFile = path.join(dir, 'args.json'), journal = path.join(dir, 'events.ndjson');
  fs.writeFileSync(argsFile, JSON.stringify({repoDir, ...args}));
  const child = spawn(process.execPath, [runner, workflow, '--args', argsFile, '--journal', journal, ...flags], {stdio: ['pipe', 'pipe', 'pipe']});
  t.after(() => child.kill());
  const events = [], messages = readline.createInterface({input: child.stdout});
  let stderr = '';
  child.stderr.on('data', chunk => { stderr += chunk; });
  messages.on('line', line => {
    const event = JSON.parse(line); events.push(event);
    handle?.(event, result => child.stdin.write(JSON.stringify({id: event.id, result}) + '\n'), child);
  });
  return new Promise((resolve, reject) => {
    child.on('error', reject);
    child.on('close', code => resolve({code, events, stderr, journal, result: events.findLast(e => e.type === 'result')?.result}));
  });
}

test('runner executes a real plan workflow with fresh-role requests and durable receipts', async t => {
  const run = await host(t, 'plan-loop', {tier: 'small', requirements: 'Add the field'}, (event, reply) => {
    if (event.type !== 'agent') return;
    assert.equal(event.requiresFreshAgent, true);
    assert.match(event.role.instructions, /Return only JSON/);
    assert.deepEqual(event.role.tools, ['Read', 'Grep', 'Glob']);
    assert.equal(event.options.schema.type, 'object');
    reply(event.role.name === 'planner' ? {plan: '1. Add a field -> verify: targeted test'} : sound);
  });
  assert.equal(run.code, 0, run.stderr);
  assert.equal(run.result.converged, true);
  const requests = run.events.filter(e => e.type === 'agent');
  assert.equal(new Set(requests.map(e => e.id)).size, 2);
  const journal = fs.readFileSync(run.journal, 'utf8').trim().split('\n').map(JSON.parse);
  assert.equal(journal.filter(e => e.type === 'reply').length, 2);
  assert.equal(journal.at(-1).type, 'result');
});

test('runner returns malformed and null agent results through the null branch', async t => {
  for (const reply of [null, 'not json', {plan: 7}]) {
    const run = await host(t, 'plan-loop', {tier: 'small', requirements: 'feature'}, (event, send) => { if (event.type === 'agent') send(reply); });
    assert.equal(run.code, 1);
    assert.equal(run.result.converged, false);
    assert.match(run.result.error, /planner/);
    assert.equal(run.events.filter(e => e.type === 'agent').length, 1);
  }
});

test('runner bounds concurrent check and lens dispatch while the real validate module executes', async t => {
  let active = 0, peak = 0;
  const run = await host(t, 'validate', {root, tier: 'full', base: 'a'.repeat(40), config: {test: 'npm test'}}, (event, reply) => {
    if (event.type !== 'agent') return;
    active++; peak = Math.max(peak, active);
    setTimeout(() => {
      active--;
      reply(event.options.label === 'changed-paths' || event.options.label === 'scope-after-review'
        ? {pass: true, failures: [], output: JSON.stringify({files: ['src/a.js'], added: 1, removed: 0, fingerprint: 'f'.repeat(64)})}
        : event.role.name === 'checker' ? {pass: true, failures: [], output: 'ok'} : {findings: []});
    }, 10);
  }, ['--concurrency', '1']);
  assert.equal(run.code, 0, JSON.stringify(run.result));
  assert.equal(run.result.status, 'pass');
  assert.equal(peak, 1);
  assert.ok(run.events.some(e => e.role?.name === 'security-reviewer'));
});

test('runner treats EOF as terminal error rather than starting subsequent agents', async t => {
  const run = await host(t, 'plan-loop', {tier: 'small', requirements: 'feature'}, (event, reply, child) => { if (event.type === 'agent') child.stdin.end(); });
  assert.equal(run.code, 1);
  assert.equal(run.result.status, 'error');
  assert.match(run.result.error, /input closed/);
  assert.equal(run.events.filter(e => e.type === 'agent').length, 1);
  assert.equal(run.events.filter(e => e.type === 'cancel').length, 1);
});

test('runner timeout cancels the complete run with no automatic retry', async t => {
  const run = await host(t, 'plan-loop', {tier: 'small', requirements: 'feature'}, null, ['--timeout-ms', '30']);
  assert.equal(run.code, 1);
  assert.equal(run.result.status, 'error');
  assert.match(run.result.error, /timed out/);
  assert.equal(run.events.filter(e => e.type === 'agent').length, 1);
  assert.equal(run.events.find(e => e.type === 'cancel').ids.length, 1);
});
