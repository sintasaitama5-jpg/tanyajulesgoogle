#!/bin/bash
# ============================================================
# update.sh — Deploy perubahan views ke container yang berjalan
# Tidak perlu rebuild Docker, tidak perlu restart
# Jalankan: bash update.sh
# ============================================================

CONTAINER="invoice_app"
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
VIEWS_SRC="$APP_DIR/app/views"
VIEWS_DST="/var/www/html/views"
SRC_SRC="$APP_DIR/app/src"
SRC_DST="/var/www/html/src"

# Warna output
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

echo ""
echo "=========================================="
echo "  InvoiceApp — Deploy Update"
echo "  Container : $CONTAINER"
echo "=========================================="
echo ""

# Cek apakah container running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER}$"; then
  echo -e "${RED}[ERROR]${NC} Container '$CONTAINER' tidak ditemukan atau tidak running."
  echo "Jalankan: docker compose up -d"
  exit 1
fi

echo -e "${YELLOW}[1/3]${NC} Copy views..."
if docker cp "$VIEWS_SRC/." "$CONTAINER:$VIEWS_DST/"; then
  echo -e "${GREEN}      OK${NC} — views ter-update"
else
  echo -e "${RED}      GAGAL${NC} — coba dengan sudo: sudo bash update.sh"
  exit 1
fi

echo -e "${YELLOW}[2/3]${NC} Copy src (controllers, helpers, DB schema)..."
if docker cp "$SRC_SRC/." "$CONTAINER:$SRC_DST/"; then
  echo -e "${GREEN}      OK${NC} — src ter-update"
else
  echo -e "${RED}      GAGAL${NC}"
  exit 1
fi

echo -e "${YELLOW}[3/3]${NC} Fix permissions..."
docker exec "$CONTAINER" chown -R www-data:www-data \
  "$VIEWS_DST" "$SRC_DST" \
  /var/www/html/data \
  /var/www/html/uploads \
  /var/www/html/pdfs 2>/dev/null || true
echo -e "${GREEN}      OK${NC}"

echo ""
echo -e "${GREEN}=========================================="
echo -e "  Selesai! Refresh browser untuk melihat"
echo -e "  perubahan."
echo -e "==========================================${NC}"
echo ""
