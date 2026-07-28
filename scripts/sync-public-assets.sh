#!/usr/bin/env bash
# Copia assets estáticos a public_html (Hostinger). Ejecutar desde laravel/
set -euo pipefail

cd "$(dirname "$0")/.."
PUBLIC_HTML="${1:-../public_html}"

if [ ! -d "$PUBLIC_HTML" ]; then
    echo "ERROR: no existe $PUBLIC_HTML"
    echo "Uso: ./scripts/sync-public-assets.sh [ruta/public_html]"
    exit 1
fi

echo "→ Sync a $PUBLIC_HTML"
cp index.php .htaccess "$PUBLIC_HTML/"
mkdir -p "$PUBLIC_HTML/css" "$PUBLIC_HTML/js" "$PUBLIC_HTML/images"
cp -r public/css/. "$PUBLIC_HTML/css/"
cp -r public/js/. "$PUBLIC_HTML/js/"
[ -d public/images ] && cp -r public/images/. "$PUBLIC_HTML/images/" || true
ln -sfn ../laravel/public/storage "$PUBLIC_HTML/storage" 2>/dev/null || true
echo "✓ CSS: $(wc -c < public/css/app.css) bytes → $PUBLIC_HTML/css/app.css"
