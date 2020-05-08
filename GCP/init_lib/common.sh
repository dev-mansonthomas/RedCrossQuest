#!/usr/bin/env bash

props()
{
  # Read with:
  # IFS (Field Separator) =
  # -d (Record separator) newline
  # first field before separator as k (key)
  # second field after separator and reminder of record as v (value)
  while IFS='=' read -d $'\n' -r k v; do
    # Skip lines starting with sharp
    # or lines containing only space or empty lines
    [[ "$k" =~ ^([[:space:]]*|[[:space:]]*#.*)$ ]] && continue
    # Store key value into assoc array
    KEY+=($k)
    PROPERTIES[$k]="$v"
    # stdin the properties file
  done < "$1" ;
}
