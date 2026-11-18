import {execFileSync} from 'node:child_process';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {mergeOptions} from '../scripts/auto-merge.js';

export const message = 'blocked — use a dev/* branch and a PR; automatic merging must use the SDLC auto-merge helper after validation and CI';
const dangerous = s => /\b(commit|push|merge)\b/.test(s.replace(/["'\\]/g, ''));
// A deliberately bounded shell lexer: parse common shell forms, refuse ambiguous
// dangerous constructs. It is not a general shell interpreter or security boundary.
export function lex(input) {
  const out = []; let word = '', started = false, quote = null;
  const flush = () => { if (started) out.push(word); word = ''; started = false; };
  for (let i = 0; i < input.length; i++) {
    const ch = input[i];
    if (ch === '\\' && quote !== "'") { if (++i >= input.length) throw Error('trailing escape'); word += input[i]; started = true; continue; }
    if (quote) { if (ch === quote) quote = null; else word += ch; started = true; continue; }
    if (ch === '"' || ch === "'") { quote = ch; started = true; continue; }
    if (/\s/.test(ch)) { flush(); if (ch === '\n') out.push(';'); continue; }
    if (';&|()'.includes(ch)) { flush(); out.push(ch); continue; }
    if (ch === '#' && !started) { while (i < input.length && input[i] !== '\n') i++; out.push(';'); continue; }
    word += ch; started = true;
  }
  if (quote) throw Error('unclosed quote');
  flush(); return out;
}
const branchName = value => value.replace(/^\+/, '').replace(/^refs\/heads\//, '');
function nativeGit(cwd, globals, args) {
  try { return execFileSync('git', [...globals, ...args], {cwd, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore']}).trim(); } catch { return null; }
}
export function evaluate(command, cwd, query = nativeGit, depth = 0, outerDanger = false) {
  if (typeof command !== 'string' || !cwd || depth > 8) return false;
  let words;
  try { words = lex(command); } catch { return !dangerous(command); }
  if (outerDanger && words.some(w => ['checkout', 'switch', 'symbolic-ref', 'update-ref', 'reset', 'branch'].includes(w))) return false;
  if (!dangerous(command) && !outerDanger) return true;
  if (/\$|`|<<|>/.test(command)) return false;
  const groups = []; let group = [];
  for (const word of words) {
    if ([';', '&', '|', '(', ')'].includes(word)) { if (group.length) groups.push(group); group = []; }
    else group.push(word);
  }
  if (group.length) groups.push(group);
  let currentDir = cwd;
  for (let tokens of groups) {
    tokens = [...tokens];
    while (tokens.length) {
      if (/^[A-Za-z_]\w*=/.test(tokens[0])) {
        if (/^GIT_(DIR|WORK_TREE|CONFIG|CONFIG_COUNT|CONFIG_PARAMETERS|COMMON_DIR)=/.test(tokens[0])) return false;
        tokens.shift(); continue;
      }
      if (['env', 'command', 'sudo', 'nohup', 'time', 'builtin', 'exec'].includes(tokens[0])) {
        tokens.shift();
        while (tokens[0]?.startsWith('-')) {
          const flag = tokens.shift();
          if (['-u', '-g', '--user', '--group', '--chdir'].includes(flag)) tokens.shift();
          else if (!['--', '-p', '-n', '-E', '-i', '--ignore-environment'].includes(flag)) return false;
        }
        continue;
      }
      break;
    }
    if (!tokens.length) continue;
    const exe = tokens.shift().replaceAll('\\', '/').split('/').at(-1).replace(/\.exe$/i, '');
    if (['bash', 'sh', 'zsh'].includes(exe)) {
      const at = tokens.findIndex(t => /^-[a-z]*c[a-z]*$/.test(t));
      if (at < 0 || !tokens[at + 1] || !evaluate(tokens[at + 1], currentDir, query, depth + 1, true)) return false;
      continue;
    }
    if (exe === 'cd') {
      if (tokens.length !== 1 || tokens[0].startsWith('-')) return false;
      currentDir = path.resolve(currentDir, tokens[0]); continue;
    }
    if (exe === 'gh') {
      // Global gh flags can precede the subcommand.
      while (tokens[0]?.startsWith('-')) {
        const flag = tokens.shift();
        if (['-R', '--repo', '--hostname'].includes(flag)) tokens.shift(); else return false;
      }
      if (tokens[0] === 'pr' && tokens[1] === 'merge') return false;
      if (tokens[0] === 'api' && tokens.some(t => /(^|\/)merge(?:[/?#]|$)/.test(t))) return false;
      continue;
    }
    if (exe !== 'git') {
      if (exe === 'node' && tokens[0] && path.resolve(currentDir, tokens[0]) === path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../scripts/auto-merge.js')) {
        try { mergeOptions(tokens.slice(1)); } catch { return false; }
        if (groups.length !== 1) return false;
        continue;
      }
      // Text-display commands are harmless; opaque interpreters containing a
      // dangerous command cannot be reliably interpreted by this accident guard.
      if (['echo', 'printf', 'cat', 'rg', 'grep'].includes(exe)) continue;
      if (dangerous(tokens.join(' '))) return false;
      continue;
    }
    const globals = []; let gitDir = currentDir;
    while (tokens[0]?.startsWith('-')) {
      const flag = tokens.shift();
      if (['-C', '--git-dir', '--work-tree', '-c'].includes(flag)) {
        const value = tokens.shift(); if (!value) return false;
        if (flag === '-C') { gitDir = path.resolve(gitDir, value); }
        else globals.push(flag, value);
      } else if (/^--(git-dir|work-tree)=/.test(flag) || /^-c.+/.test(flag)) globals.push(flag);
      else if (['--no-pager', '--literal-pathspecs', '--no-optional-locks'].includes(flag)) globals.push(flag);
      else return false;
    }
    const action = tokens.shift();
    // Pre-execution branch queries cannot model an earlier state mutation.
    if (['checkout', 'switch', 'symbolic-ref', 'update-ref', 'reset', 'branch'].includes(action) && groups.length > 1) return false;
    if (!['commit', 'push'].includes(action)) continue;
    const branch = query(gitDir, globals, ['symbolic-ref', '--quiet', '--short', 'HEAD']);
    const remoteHead = query(gitDir, globals, ['symbolic-ref', '--quiet', '--short', 'refs/remotes/origin/HEAD']);
    const protectedBranches = new Set(['main', 'master', remoteHead?.replace(/^origin\//, '')].filter(Boolean));
    if (!branch || !remoteHead) return false;
    if (action === 'commit') { if (protectedBranches.has(branch)) return false; continue; }
    if (tokens.some(t => ['--all', '--mirror'].includes(t))) return false;
    const positional = []; let endOptions = false, repoFlag = false;
    for (let i = 0; i < tokens.length; i++) {
      const token = tokens[i];
      if (token === '--') { endOptions = true; continue; }
      if (!endOptions && token.startsWith('-')) {
        if (['--repo', '--receive-pack', '--exec', '--push-option', '-o'].includes(token)) { if (!tokens[++i]) return false; if (token === '--repo') repoFlag = true; }
        else if (/^--(repo|receive-pack|exec|push-option|force-with-lease)=/.test(token)) { if (token.startsWith('--repo=')) repoFlag = true; }
        else if (!['-u', '--set-upstream', '-f', '--force', '--force-with-lease', '--force-if-includes', '--atomic', '--porcelain', '--dry-run', '-n', '--tags', '--follow-tags', '-d', '--delete', '--prune', '-v', '--verbose', '-q', '--quiet', '--no-verify', '--signed', '--no-signed', '--thin', '--no-thin'].includes(token)) return false;
      } else positional.push(token);
    }
    const refs = repoFlag ? positional : positional.slice(1);
    if (!refs.length) {
      // Includes push.default=matching and remote.push configuration.
      return false;
    }
    for (const ref of refs) {
      let destination = branchName(ref.includes(':') ? ref.slice(ref.lastIndexOf(':') + 1) : ref);
      if (destination === 'HEAD') destination = branch;
      if (!destination || /[*?$`]/.test(destination) || protectedBranches.has(destination)) return false;
    }
  }
  return true;
}
export function hook(input) {
  if (!input || !input.tool_input || typeof input.tool_input.command !== 'string' || !input.cwd) return false;
  return evaluate(input.tool_input.command, input.cwd);
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  let raw = ''; for await (const chunk of process.stdin) raw += chunk;
  let allowed = false; try { allowed = hook(JSON.parse(raw)); } catch { /* malformed hook input fails closed */ }
  if (!allowed) {
    console.error(message);
    console.log(JSON.stringify({permission: 'deny', hookSpecificOutput: {hookEventName: 'PreToolUse', permissionDecision: 'deny', permissionDecisionReason: message}}));
    process.exitCode = 2;
  }
}
