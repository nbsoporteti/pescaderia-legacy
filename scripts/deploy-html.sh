#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
ZIP="${1:-public_html.zip}"

[ -f "$ZIP" ] || { echo "Falta $ZIP"; exit 1; }

rm -rf html_tmp
mkdir -p html_tmp
python3 -c "import zipfile; zipfile.ZipFile('$ZIP').extractall('html_tmp')"

if [ -d html_tmp/public_html ]; then
  SRC=html_tmp/public_html
elif [ -f html_tmp/login.php ]; then
  SRC=html_tmp
else
  SRC=$(find html_tmp -mindepth 1 -maxdepth 1 -type d | head -1)
  SRC=${SRC:-html_tmp}
fi

rm -rf html.bak
[ -d html ] && mv html html.bak
mkdir -p html
rsync -a "$SRC"/ html/
rm -rf html_tmp

echo "Desplegados $(find html -type f | wc -l) archivos desde $SRC"
ls html | head -20
