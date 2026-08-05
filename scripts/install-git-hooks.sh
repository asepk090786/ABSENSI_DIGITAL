#!/bin/bash
set -e
# Installs git hooks by setting core.hooksPath to .githooks (recommended)
# Usage: bash scripts/install-git-hooks.sh

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

if [ ! -d .githooks ]; then
    echo "No .githooks directory found."
    exit 1
fi

if git rev-parse --git-dir > /dev/null 2>&1; then
    echo "Setting git core.hooksPath to .githooks"
    git config core.hooksPath .githooks
    echo "Making hooks executable"
    chmod +x .githooks/* || true
    echo "Hooks installed."
else
    echo "Not a git repository. To enable hooks manually, copy files from .githooks/ to .git/hooks/ and chmod +x them."
fi
