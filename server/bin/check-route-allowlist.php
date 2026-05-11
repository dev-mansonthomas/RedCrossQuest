#!/usr/bin/env php
<?php
/**
 * Asserts that the first-segment allowlist hardcoded in
 *   server/public/rest/index.php (the FAIL-FAST block)
 * covers every first segment registered in
 *   server/src/routes/*.php
 *
 * Drift => exit 1. CI-ready. Zero dependencies.
 *
 * Usage: php server/bin/check-route-allowlist.php
 */

declare(strict_types=1);

$root        = dirname(__DIR__);
$indexPath   = $root . '/public/rest/index.php';
$routesGlob  = $root . '/src/routes/*.php';

/* ---------- 1. Extract allowlist from index.php ---------- */
$indexSrc = @file_get_contents($indexPath);
if ($indexSrc === false) {
    fwrite(STDERR, "Cannot read {$indexPath}\n");
    exit(2);
}
if (!preg_match('/static\s+\$allowed\s*=\s*\[(.*?)\];/s', $indexSrc, $m)) {
    fwrite(STDERR, "Could not locate \$allowed array in index.php\n");
    exit(2);
}
preg_match_all("/'([^']+)'/", $m[1], $am);
$allowed = array_values(array_unique($am[1]));
sort($allowed);

/* ---------- 2. Extract first segments from routes/*.php ---------- */
$routeFiles = glob($routesGlob);
if (!$routeFiles) {
    fwrite(STDERR, "No route files matched {$routesGlob}\n");
    exit(2);
}

$declared = [];
// Capture the first path segment after the leading '/' until the next '/' or
// closing quote. The segment may itself be a Slim placeholder like
// '{role-id:[1-9]}' (which contains '[' and ']'), so the only delimiters we
// must exclude are '/' and the closing single-quote.
$pattern  = '/\$app\s*->\s*(?:get|post|put|delete|patch|options|map|any)\s*\(\s*'
          . "'\\/([^'\\/]+)/";

foreach ($routeFiles as $file) {
    $src = file_get_contents($file);
    if ($src === false) { continue; }
    if (preg_match_all($pattern, $src, $hits)) {
        foreach ($hits[1] as $seg) {
            foreach (expandSegment($seg) as $expanded) {
                $declared[$expanded] = $file;
            }
        }
    }
}

if (!$declared) {
    fwrite(STDERR, "No \$app->{verb} declarations found under {$routesGlob}\n");
    exit(2);
}

/* ---------- 3. Compare ---------- */
$declaredKeys = array_keys($declared);
sort($declaredKeys);

$missing = array_diff($declaredKeys, $allowed);
$extra   = array_diff($allowed,      $declaredKeys);

echo "Allowed in index.php  : ", implode(', ', $allowed),       PHP_EOL;
echo "Declared in routes/   : ", implode(', ', $declaredKeys),  PHP_EOL;

if ($missing) {
    fwrite(STDERR, PHP_EOL . "FAIL: route segments NOT in fail-fast allowlist:" . PHP_EOL);
    foreach ($missing as $seg) {
        fwrite(STDERR, "  - '{$seg}'  (declared in " . basename($declared[$seg]) . ")" . PHP_EOL);
    }
    fwrite(STDERR, "Update \$allowed in {$indexPath}." . PHP_EOL);
    exit(1);
}
if ($extra) {
    fwrite(STDERR, PHP_EOL . "WARN: allowlist entries with no matching route (dead?):" . PHP_EOL);
    foreach ($extra as $seg) { fwrite(STDERR, "  - '{$seg}'" . PHP_EOL); }
    // soft warning, do not fail
}

echo PHP_EOL . "OK: fail-fast allowlist covers all declared route roots." . PHP_EOL;
exit(0);

/* ---------- helpers ---------- */

/**
 * Expand a Slim path token into the set of literal first-segments it can match.
 * Supports:
 *   - literal:                 'authenticate'        -> ['authenticate']
 *   - bracketed range placeholder:
 *       '{role-id:[1-9]}'      -> ['1','2','3','4','5','6','7','8','9']
 *       '{role-id:[2-9]}'      -> ['2'..'9']
 *       '{role-id:[9]}'        -> ['9']
 *   - placeholder without constraint => fail (we cannot allowlist 'anything')
 */
function expandSegment(string $seg): array
{
    if ($seg[0] !== '{') {
        return [$seg]; // plain literal
    }
    if (preg_match('/^\{[^:}]+:\[(\d)(?:-(\d))?\]\}$/', $seg, $m)) {
        $from = (int)$m[1];
        $to   = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : $from;
        return array_map('strval', range($from, $to));
    }
    fwrite(STDERR, "Unsupported placeholder pattern: {$seg}\n");
    fwrite(STDERR, "Add support in expandSegment() or use a literal first segment.\n");
    exit(2);
}
