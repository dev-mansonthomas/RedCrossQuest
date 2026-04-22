#!/usr/bin/env python3
"""Analyze GCP App Engine request_log dump for scan patterns.
Usage: python3 analyze_scans.py [dump.json]
"""
import json, collections, sys

path = sys.argv[1] if len(sys.argv) > 1 else '/tmp/rcq_scans.json'
data = json.load(open(path))
print(f'Total entries: {len(data)}  (source={path})')

uris, ips, uas = [], [], []
statuses = collections.Counter()
methods = collections.Counter()
severities = collections.Counter()
modules = collections.Counter()
log_messages = collections.Counter()
colon_hits = []
records = []

for e in data:
    pp = e.get('protoPayload', {}) or {}
    resource = pp.get('resource', '') or ''
    ip = pp.get('ip', '')
    ua = pp.get('userAgent', '') or ''
    st = pp.get('status', 0)
    mth = pp.get('method', '')
    sev = e.get('severity', '')
    module = pp.get('moduleId', '') or 'default'
    lines = pp.get('line', []) or []
    msg = (lines[0].get('logMessage', '') if lines else '')[:200]

    uris.append(resource.split('?')[0])
    ips.append(ip)
    uas.append(ua)
    statuses[st] += 1
    methods[mth] += 1
    severities[sev] += 1
    modules[module] += 1
    if msg:
        log_messages[msg] += 1
    records.append((ip, ua, resource, st, sev, module, msg))
    if ':' in resource.split('?')[0]:
        colon_hits.append((resource, ip, ua, module))

def section(title):
    print(f'\n=== {title} ===')

section('severity')
for k, v in severities.most_common():
    print(f'  {v:5}  {k}')

section('status')
for k, v in statuses.most_common(15):
    print(f'  {v:5}  {k}')

section('method')
for k, v in methods.most_common():
    print(f'  {v:5}  {k}')

section('top 40 URIs')
for k, v in collections.Counter(uris).most_common(40):
    print(f'  {v:5}  {k}')

section('top 20 IPs')
for k, v in collections.Counter(ips).most_common(20):
    print(f'  {v:5}  {k}')

section('top 15 User-Agents')
for k, v in collections.Counter(uas).most_common(15):
    sk = (k or '(empty)').replace('\n', ' ')[:140]
    print(f'  {v:5}  {sk}')

section(f'URIs containing colon in path (likely placeholder scans): {len(colon_hits)}')
seen = set()
for p, i, u, m in colon_hits:
    if p in seen:
        continue
    seen.add(p)
    print(f'  [{m}] {p}  (ip={i})')
    if len(seen) >= 25:
        break

section('module (GAE service)')
for k, v in modules.most_common():
    print(f'  {v:5}  {k}')

section('top 20 paths with status 404')
c404 = collections.Counter(r[2].split('?')[0] for r in records if r[3] == 404)
for k, v in c404.most_common(20):
    print(f'  {v:5}  {k}')

section('top 10 log messages (first line per request)')
for k, v in log_messages.most_common(10):
    print(f'  {v:5}  {k}')

section('severity breakdown by module')
bymod = collections.Counter()
for r in records:
    bymod[(r[5], r[4])] += 1
for (m, s), v in bymod.most_common():
    print(f'  {v:5}  module={m:10}  severity={s}')

section('scans hitting the PHP backend (module=default or moduleId empty)')
backend_scans = [r for r in records if (r[5] in ('default', '') or r[5] is None)]
print(f'  backend hits: {len(backend_scans)}')
top_backend_uris = collections.Counter(r[2].split('?')[0] for r in backend_scans)
for k, v in top_backend_uris.most_common(20):
    print(f'  {v:5}  {k}')

section('scan signatures (wordpress / .env / .git / php probes)')
scan_keys = ('.env', 'wp-', 'wordpress', '.git', 'phpmyadmin', 'phpMyAdmin',
             '.aws', 'xmlrpc', '.well-known/security', 'config.php', '/cgi-bin/',
             '.bak', '.old', '.backup', '.svn', '.htaccess', 'eval-stdin')
scan_hits = [r for r in records if any(k in r[2].lower() for k in [x.lower() for x in scan_keys])]
print(f'  total scan probe hits: {len(scan_hits)} / {len(records)}')
scan_uris = collections.Counter(r[2].split('?')[0] for r in scan_hits)
for k, v in scan_uris.most_common(15):
    print(f'  {v:5}  {k}')
