#!/usr/bin/env bash
# Forwarding shortcut to scripts/backup.sh
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
exec "${SCRIPT_DIR}/scripts/backup.sh" "$@"

