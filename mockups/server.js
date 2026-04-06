const http = require('http');
const fs = require('fs');
const path = require('path');

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.svg':  'image/svg+xml',
  '.css':  'text/css',
  '.js':   'text/javascript',
  '.png':  'image/png',
  '.jpg':  'image/jpeg',
};

const server = http.createServer((req, res) => {
  const url = req.url === '/' ? '/index.html' : req.url;
  const filePath = path.join(__dirname, url.split('?')[0]);
  const ext = path.extname(filePath);
  fs.readFile(filePath, (err, data) => {
    if (err) { res.writeHead(404); res.end('Not found'); return; }
    res.writeHead(200, { 'Content-Type': MIME[ext] || 'application/octet-stream' });
    res.end(data);
  });
});

server.listen(3456, () => console.log('Mockup server running on http://localhost:3456'));
