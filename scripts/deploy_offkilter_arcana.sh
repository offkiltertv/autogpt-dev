#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/plugins/offkilter-arcana"
BUILD_DIR="$ROOT_DIR/build"
ZIP_PATH="$BUILD_DIR/offkilter-arcana.zip"

mkdir -p "$BUILD_DIR"
rm -f "$ZIP_PATH"

(
  cd "$ROOT_DIR/plugins"
  zip -r "$ZIP_PATH" offkilter-arcana >/dev/null
)

echo "Built plugin artifact: $ZIP_PATH"
