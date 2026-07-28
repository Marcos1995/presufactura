#!/usr/bin/env bash
# Copia CSS/JS a public_html (padre). Ejecutar desde laravel/ en el servidor.
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -n "${1:-}" ]; then
    WEB_ROOT="$1"
elif [ -f "../index.php" ] && [ ! -f "../artisan" ]; then
    WEB_ROOT=".."
else
    echo "ERROR: no se detectó public_html."
    echo "Uso: ./scripts/sync-public-assets.sh [ruta/a/public_html]"
    exit 1
fi

echo "→ Sync assets → $WEB_ROOT"
mkdir -p "$WEB_ROOT/css" "$WEB_ROOT/js" "$WEB_ROOT/images"
cp -r public/css/. "$WEB_ROOT/css/"
cp -r public/js/. "$WEB_ROOT/js/"
[ -d public/images ] && cp -r public/images/. "$WEB_ROOT/images/" || true
STORAGE_SRC="$(pwd)/public/storage"
ln -sfn "$STORAGE_SRC" "$WEB_ROOT/storage" 2>/dev/null || true
echo "✓ $WEB_ROOT/css/app.css ($(wc -c < public/css/app.css) bytes)"
