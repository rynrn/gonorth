import fs from 'node:fs';
import path from 'node:path';
import readline from 'node:readline';
import {execFileSync} from 'node:child_process';
import {randomUUID} from 'node:crypto';
import {fileURLToPath} from 'node:url';
import {matches} from '../workflows/common.js';

const pluginRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const workflows = new Set(['plan-loop', 'build', 'bug-loop', 'validate']);
const roles = new Set(['checker', 'investigator', 'planner', 'plan-review', 'devils-advocate', 'implementer', 'code-reviewer', 'security-reviewer', 'spec-reviewer']);
const inside = (target, directory) => target === directory || target.startsWith(directory + path.sep);

function openJournal(repoDir, requested, runId) {
  const realRepo = fs.realpathSync(repoDir);
  let gitCommon;
  try {
    gitCommon = fs.realpathSync(execFileSync('git', ['rev-parse', '--path-format=absolute', '--git-common-dir'], {cwd: realRepo, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore']}).trim());
  } catch { /* An explicit external journal also supports repositories before Git setup. */ }
  if (!requested && !gitCommon) throw Error('A Git repository or an explicit external --journal path is required');
  if (requested && !path.isAbsolute(requested)) throw Error('--journal must be absolute');
  const journalPath = requested || path.join(gitCommon, 'sdlc-runs', runId, 'events.ndjson');
  const plannedParent = path.resolve(path.dirname(journalPath));
  if (inside(plannedParent, realRepo) && !(gitCommon && inside(plannedParent, gitCommon))) throw Error('Journal must be outside the working tree (Git common-dir is allowed)');
  fs.mkdirSync(path.dirname(journalPath), {recursive: true, mode: 0o700});
  const realParent = fs.realpathSync(path.dirname(journalPath));
  if (inside(realParent, realRepo) && !(gitCommon && inside(realParent, gitCommon))) throw Error('Journal must be outside the working tree (Git common-dir is allowed)');
  const realPath = path.join(realParent, path.basename(journalPath));
  const fd = fs.openSync(realPath, 'wx', 0o600);
  return {path: realPath, append: event => fs.writeSync(fd, JSON.stringify(event) + '\n'), close: () => fs.closeSync(fd)};
}

/** Execute real workflow code; the host only dispatches requests and supplies replies.
 * NDJSON stdout: ready, agent, phase/log, cancel, result. stdin: {id,result}.
 * Each agent request requires a fresh agent. This runner does not launch tools,
 * execute the supplied check commands, resume runs, or publish Git changes.
 */
export async function runWorkflow({workflow, args, concurrency = 3, timeoutMs = 600000, journalPath, input = process.stdin, output = process.stdout}) {
  if (!workflows.has(workflow)) throw Error('Unknown workflow');
  if (!Number.isInteger(concurrency) || concurrency < 1 || concurrency > 32) throw Error('concurrency must be 1..32');
  if (!Number.isInteger(timeoutMs) || timeoutMs < 1 || timeoutMs > 3600000) throw Error('timeout-ms must be 1..3600000');
  if (!args || typeof args !== 'object' || !path.isAbsolute(args.repoDir || '')) throw Error('args.repoDir must be an absolute directory');
  const runId = randomUUID(), journal = openJournal(args.repoDir, journalPath, runId);
  const pending = new Map(), queue = [];
  let sequence = 0, stopped = null, finished = false;
  const lines = readline.createInterface({input, crlfDelay: Infinity});
  const record = event => journal.append({at: new Date().toISOString(), runId, ...event});
  const emit = event => { record(event); output.write(JSON.stringify(event) + '\n'); };
  const abort = reason => {
    if (stopped || finished) return;
    stopped = reason;
    const activeIds = [...pending.keys()];
    emit({type: 'cancel', runId, ids: activeIds, reason, instruction: 'Stop these agents and confirm they have stopped before restarting work. This run cannot resume.'});
    for (const request of pending.values()) { clearTimeout(request.timer); request.resolve(null); }
    pending.clear();
    for (const request of queue.splice(0)) request.resolve(null);
  };
  const pump = () => {
    while (!stopped && pending.size < concurrency && queue.length) {
      const request = queue.shift();
      pending.set(request.id, request);
      request.timer = setTimeout(() => abort(`Agent request timed out: ${request.id}`), timeoutMs);
      emit({type: 'agent', runId, id: request.id, prompt: request.prompt, options: request.options, role: request.role, requiresFreshAgent: true});
    }
  };
  lines.on('line', line => {
    if (finished || stopped) return;
    let reply;
    try { reply = JSON.parse(line); } catch { abort('Malformed NDJSON response'); return; }
    if (!reply || typeof reply.id !== 'string' || !Object.hasOwn(reply, 'result') || Object.keys(reply).some(k => !['id', 'result'].includes(k))) { abort('Invalid response envelope; expected {id,result}'); return; }
    const request = pending.get(reply.id);
    if (!request) { abort(`Unknown or duplicate response id: ${reply.id}`); return; }
    clearTimeout(request.timer); pending.delete(reply.id);
    let result = reply.result;
    if (typeof result === 'string') { try { result = JSON.parse(result); } catch { result = null; } }
    if (!matches(result, request.options.schema)) result = null;
    record({type: 'reply', id: reply.id, result});
    request.resolve(result);
    pump();
  });
  lines.on('close', () => { if (!finished) abort('Host input closed before workflow completion'); });
  input.on('error', error => abort(`Host input error: ${error.message}`));
  const api = {
    agent(prompt, options) {
      if (stopped) return Promise.resolve(null);
      const name = options.agentType?.replace(/^sdlc:/, '');
      if (!roles.has(name)) { abort('Unknown agent role'); return Promise.resolve(null); }
      const rolePrompt = fs.readFileSync(path.join(pluginRoot, 'agents', name + '.md'), 'utf8');
      const role = {
        name, instructions: rolePrompt,
        model: rolePrompt.match(/^model: (.+)$/m)?.[1],
        tools: rolePrompt.match(/^tools: (.+)$/m)?.[1].split(',').map(s => s.trim()) || null,
        readonly: /^readonly: true$/m.test(rolePrompt)
      };
      return new Promise(resolve => {
        queue.push({id: `${runId}:${++sequence}`, prompt, options, role, resolve});
        pump();
      });
    },
    phase: name => { if (!stopped) emit({type: 'phase', name}); },
    log: message => { if (!stopped) emit({type: 'log', message}); }
  };
  let result;
  try {
    emit({type: 'ready', runId, workflow, concurrency, timeoutMs, journal: journal.path});
    record({type: 'start', workflow, args});
    const module = await import(`../workflows/${workflow}.js`);
    result = stopped ? null : await module.default(args, api);
  } catch (error) {
    abort(`Workflow failed: ${error.message}`);
  }
  if (stopped) result = {status: 'error', error: stopped};
  if (!result) result = {status: 'error', error: 'Workflow returned no result'};
  finished = true;
  const receipt = {type: 'result', runId, workflow, result, journal: journal.path};
  emit(receipt);
  lines.close(); input.pause(); journal.close();
  return receipt;
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try {
    const [workflow, ...flags] = process.argv.slice(2), values = {};
    for (let i = 0; i < flags.length; i += 2) {
      if (!['--args', '--concurrency', '--timeout-ms', '--journal'].includes(flags[i]) || flags[i + 1] === undefined || Object.hasOwn(values, flags[i])) throw Error('Usage: runner.js <workflow> --args <json-file> [--concurrency 3] [--timeout-ms 600000] [--journal <absolute-file>]');
      values[flags[i]] = flags[i + 1];
    }
    if (!values['--args']) throw Error('--args JSON file is required');
    const receipt = await runWorkflow({workflow, args: JSON.parse(fs.readFileSync(values['--args'], 'utf8')), concurrency: values['--concurrency'] ? Number(values['--concurrency']) : 3, timeoutMs: values['--timeout-ms'] ? Number(values['--timeout-ms']) : 600000, journalPath: values['--journal']});
    const result = receipt.result;
    process.exitCode = result.converged === true || ['pass', 'built', 'fixed'].includes(result.status) ? 0 : 1;
  } catch (error) {
    process.stdout.write(JSON.stringify({type: 'result', result: {status: 'error', error: error.message}}) + '\n');
    process.exitCode = 1;
  }
}
