#!/usr/bin/env bash
# CERNIX — One-command reliable launch (Git Bash / WSL / bash on Windows)
# Usage:  bash start.sh   OR   ./start.sh

set -euo pipefail

PHP="/c/xampp/php/php"
ARTISAN="/c/Users/hp/cernix-exam-verify/cernix/artisan"
LOG_DIR="/c/Users/hp/cernix-exam-verify/cernix/storage/logs"
PORT=8000
NGROK_DOMAIN="refusal-deem-launch.ngrok-free.dev"

# ANSI colours (safe — falls back to plain text if terminal lacks support)
GRN=$'\033[0;32m'; YLW=$'\033[1;33m'; CYN=$'\033[0;36m'
RED=$'\033[0;31m'; DIM=$'\033[2m'; RST=$'\033[0m'

echo ""
echo "  ${CYN}============================================================${RST}"
echo "  ${CYN}  CERNIX  |  Adekunle Ajasin University  |  QR Verify${RST}"
echo "  ${CYN}============================================================${RST}"
echo ""

# ── Step 1: Release port 8000 ──────────────────────────────────────────────────
echo "  ${YLW}[1/4]${RST} Releasing port $PORT..."
# Use PowerShell to kill the owner of port $PORT (works in Git Bash on Windows)
powershell.exe -NoProfile -NonInteractive -Command "
    \$rows = netstat -ano | Select-String ':%PORT%\s'
    foreach (\$row in \$rows) {
        \$pid = (\$row.ToString().Trim().Split()[-1])
        if (\$pid -ne '0') {
            try { Stop-Process -Id \$pid -Force -ErrorAction SilentlyContinue } catch {}
        }
    }
" 2>/dev/null || true
sleep 0.5

# ── Step 2: Start Laravel server ──────────────────────────────────────────────
echo "  ${YLW}[2/4]${RST} Starting Laravel server on port $PORT..."
mkdir -p "$LOG_DIR"
# Background the server; capture PID for cleanup
"$PHP" "$ARTISAN" serve --host=0.0.0.0 --port="$PORT" \
    > "$LOG_DIR/server.log" 2>&1 &
SERVER_PID=$!

# ── Step 3: Poll until server responds ────────────────────────────────────────
echo "  ${YLW}[3/4]${RST} Waiting for server to be ready..."
READY=false
for i in $(seq 1 60); do
    sleep 0.5
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
        --connect-timeout 1 --max-time 2 \
        "http://localhost:$PORT/up" 2>/dev/null || echo "000")
    if [[ "$HTTP_STATUS" == "200" ]]; then
        READY=true
        break
    fi
done

if [[ "$READY" != "true" ]]; then
    echo ""
    echo "  ${RED}ERROR: Server did not start within 30 seconds.${RST}"
    echo "  ${DIM}Check: $LOG_DIR/server.log${RST}"
    echo ""
    kill "$SERVER_PID" 2>/dev/null || true
    exit 1
fi
echo "  ${GRN}[3/4]${RST} Server is ready at http://localhost:$PORT"

# ── Step 4: Start ngrok tunnel ────────────────────────────────────────────────
echo "  ${YLW}[4/4]${RST} Connecting ngrok tunnel ($NGROK_DOMAIN)..."
ngrok http --domain="$NGROK_DOMAIN" "$PORT" \
    > /tmp/ngrok-cernix.log 2>&1 &
NGROK_PID=$!

# Give ngrok time to establish the tunnel
sleep 3

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo "  ${GRN}============================================================${RST}"
echo "   LOCAL :  http://localhost:$PORT"
echo "   PUBLIC:  https://$NGROK_DOMAIN"
echo "  ${GRN}============================================================${RST}"
echo ""
echo "  Press ${YLW}Ctrl+C${RST} to stop all services."
echo "  ${DIM}Server log : $LOG_DIR/server.log${RST}"
echo "  ${DIM}ngrok  log : /tmp/ngrok-cernix.log${RST}"
echo ""

# ── Cleanup on exit ───────────────────────────────────────────────────────────
_cleanup() {
    echo ""
    echo "  Stopping services..."
    kill "$SERVER_PID" 2>/dev/null || true
    kill "$NGROK_PID"  2>/dev/null || true
    # Belt-and-suspenders: kill anything still on the port
    powershell.exe -NoProfile -NonInteractive -Command "
        \$rows = netstat -ano | Select-String ':$PORT\s'
        foreach (\$row in \$rows) {
            \$pid = (\$row.ToString().Trim().Split()[-1])
            if (\$pid -ne '0') {
                try { Stop-Process -Id \$pid -Force -ErrorAction SilentlyContinue } catch {}
            }
        }
    " 2>/dev/null || true
    echo "  Stopped. Goodbye."
}
trap _cleanup EXIT INT TERM

# Keep the script alive so the trap fires on Ctrl+C
wait "$SERVER_PID" 2>/dev/null || true
