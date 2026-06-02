#!/bin/bash
# Uso: source "$(dirname "$0")/common.sh" && legacy_cd

legacy_root() {
  local src="${BASH_SOURCE[1]:-${BASH_SOURCE[0]}}"
  local script_dir
  script_dir="$(cd "$(dirname "$src")" && pwd)"
  cd "$script_dir/.." && pwd
}

legacy_cd() {
  cd "$(legacy_root)"
}
