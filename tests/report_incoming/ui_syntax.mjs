import fs from 'node:fs';
const source = fs.readFileSync(new URL('../../application/modules/report_incoming/views/index.php', import.meta.url), 'utf8');
const scripts = [...source.matchAll(/<script>([\s\S]*?)<\/script>/g)];
if (scripts.length !== 1) throw new Error('Expected one inline report script');
new Function(scripts[0][1]);
console.log('PASS inline JavaScript syntax (no browser test)');

