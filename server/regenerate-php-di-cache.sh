#!/usr/bin/env bash
#https://getcomposer.org/doc/articles/autoloader-optimization.md
rm -f  /tmp/php-di-compiled/CompiledContainer.php
composer dump-autoload -o
