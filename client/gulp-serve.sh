#!/usr/bin/env bash

. $(brew --prefix nvm)/nvm.sh
nvm use v10.24.1

gulp serve
