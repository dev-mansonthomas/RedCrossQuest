#!/usr/bin/env bash
# Apply GAE firewall DENY rules on RCQ environment(s).
#
# Context: dev/test/prod each receive automated vulnerability scans that
# (1) probe non-existent third-party products (Jira/XWiki/Magento) and
# (2) flood Slack with 500 error alerts via the generic error handler.
#
# Identified scan sources (analysed from GCP logs on 2026-04-21):
#   - SCALEWAY-AMS (NL): 51.15.0.0/17 + 51.158.0.0/15     -> cluster "research.hadrian.io"
#   - 3.74.48.121/32    (AWS Frankfurt)                   -> route-template scanner
#   - 185.177.72.67/32  (Tele2 RU)                        -> generic probe
#
# Usage:
#   GCP/gae_firewall_rules.sh                       # apply to all 3 projects (initial setup)
#   GCP/gae_firewall_rules.sh rcq-fr-dev            # apply to a single project (deploy hook)
#
# Idempotent: compares existing rule (sourceRange + action) with the desired
# state and skips the gcloud call when already in sync, so running on every
# deploy adds negligible overhead (~1 describe call per rule).

set -euo pipefail

if [[ $# -ge 1 ]]; then
  PROJECTS=("$1")
else
  PROJECTS=(rcq-fr-dev rcq-fr-test rcq-fr-prod)
fi

declare -a RULES=(
  "1000|51.15.0.0/17|block scaleway NL /17 (hadrian scans)"
  "1001|51.158.0.0/15|block scaleway NL /15 (hadrian scans)"
  "1010|3.74.48.121/32|block route-template scanner (aws-eu-central)"
  "1020|185.177.72.67/32|block tele2 RU probe"
)

apply_rule() {
  local project="$1" priority="$2" src="$3" desc="$4"
  local existing
  existing=$(gcloud app firewall-rules describe "$priority" --project="$project" \
    --format="value(action,sourceRange)" 2>/dev/null || true)
  if [[ "$existing" == "DENY	$src" ]]; then
    echo "  [$priority] up-to-date (DENY $src)"
    return 0
  fi
  if [[ -n "$existing" ]]; then
    gcloud app firewall-rules delete "$priority" --project="$project" --quiet >/dev/null
  fi
  gcloud app firewall-rules create "$priority" \
    --project="$project" --action=DENY \
    --source-range="$src" --description="$desc" --quiet >/dev/null
  echo "  [$priority] applied (DENY $src - $desc)"
}

for project in "${PROJECTS[@]}"; do
  echo "=== ${project} : firewall rules ==="
  for rule in "${RULES[@]}"; do
    IFS='|' read -r priority src desc <<< "$rule"
    apply_rule "$project" "$priority" "$src" "$desc"
  done
done
