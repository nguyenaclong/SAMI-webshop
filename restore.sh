#!/usr/bin/env bash
# Forwarding shortcut to scripts/restore.sh
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
exec "${SCRIPT_DIR}/scripts/restore.sh" "$@"

