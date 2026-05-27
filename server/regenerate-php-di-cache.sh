#!/usr/bin/env bash
# Purge the compiled PHP-DI container and regenerate the optimized Composer
# autoloader. Intended to be run *inside* the php-fpm container (where the
# /tmp/php-di-compiled volume lives) — either via `make refresh-di`, by
# run_local.sh during bootstrap, or by GCP/deploy_back.sh before deploy.
#
# https://php-di.org/doc/performances.html
# https://getcomposer.org/doc/articles/autoloader-optimization.md
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"
rm -f /tmp/php-di-compiled/CompiledContainer.php
composer dump-autoload -o
