#!/usr/bin/env python3
"""Inspect ERROR-severity messages in a GAE request_log dump."""
import json, collections, sys

path = sys.argv[1] if len(sys.argv) > 1 else '/tmp/rcq_backend_scans.json'
data = json.load(open(path))

err_msgs = collections.Counter()
err_samples = {}
for e in data:
    if e.get('severity') != 'ERROR':
        continue
    pp = e.get('protoPayload', {}) or {}
    for line in pp.get('line', []) or []:
        if line.get('severity') in ('ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'):
            msg = (line.get('logMessage') or '')[:300]
            sig = msg[:140]
            err_msgs[sig] += 1
            if sig not in err_samples:
                err_samples[sig] = {
                    'method': pp.get('method'),
                    'resource': pp.get('resource'),
                    'status': pp.get('status'),
                    'ip': pp.get('ip'),
                    'ua': (pp.get('userAgent') or '')[:80],
                    'full': msg,
                }

print(f"Unique error signatures: {len(err_msgs)}\n")
for sig, cnt in err_msgs.most_common(30):
    s = err_samples[sig]
    print(f"[{cnt:3}x] {s['method']} {s['resource']}  status={s['status']}  ip={s['ip']}")
    print(f"       ua={s['ua']}")
    print(f"       msg={sig[:200]}")
    print()

# Also list IP + UA + resource combos that produced 500
print("\n=== All status=500 entries (ip | ua | resource) ===")
combos = collections.Counter()
for e in data:
    pp = e.get('protoPayload', {}) or {}
    if pp.get('status') == 500:
        combos[(pp.get('ip'), (pp.get('userAgent') or '')[:50], pp.get('resource', '').split('?')[0])] += 1
for (ip, ua, r), c in combos.most_common(40):
    print(f"  {c:3}x  {ip:20}  {r}")
    print(f"         ua={ua}")
