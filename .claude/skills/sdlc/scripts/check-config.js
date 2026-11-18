import fs from 'node:fs';
import {validateConfig} from '../workflows/common.js';
try {
  validateConfig(JSON.parse(fs.readFileSync(process.argv[2], 'utf8')));
  console.log('SDLC configuration is valid.');
} catch (error) { console.error(error.message); process.exitCode = 1; }
