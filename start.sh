#!/bin/bash
PORT=${PORT:-8098}
DB_SOCKET=/tmp/mysql.sock
DB_PORT=3307
DB_NAME=ssd_boost
DB_USER=ssd_boost
DB_PASS=ssd_boost
SQL_DUMP="/home/runner/workspace/attached_assets/ssdboostsql_1778236721316.sql"
DATADIR=/tmp/mysql_data

echo "[ssd_panel] Starting SSD Boost SMM Panel on port $PORT..."

# ── 1. Start MariaDB if not already running ──────────────────────────────────
mysql_ready() {
    mysqladmin --socket="$DB_SOCKET" -u root ping --silent 2>/dev/null
}

if ! mysql_ready; then
    echo "[ssd_panel] Starting MariaDB (datadir=$DATADIR)..."
    mkdir -p "$DATADIR"
    rm -f "$DB_SOCKET"
    mysqld --no-defaults \
        --datadir="$DATADIR" \
        --socket="$DB_SOCKET" \
        --port="$DB_PORT" \
        --skip-grant-tables \
        --skip-name-resolve \
        --innodb-buffer-pool-size=64M \
        2>/tmp/mysqld.log &
    echo "[ssd_panel] MariaDB PID=$! — waiting up to 30s..."
    READY=0
    for i in $(seq 1 30); do
        sleep 1
        if mysql_ready; then
            echo "[ssd_panel] MariaDB ready after ${i}s"
            READY=1
            break
        fi
    done
    if [ "$READY" -eq 0 ]; then
        echo "[ssd_panel] ERROR: MariaDB did not start. Last log:" >&2
        tail -10 /tmp/mysqld.log >&2
    fi
else
    echo "[ssd_panel] MariaDB already running"
fi

# ── 2. Create DB + user ───────────────────────────────────────────────────────
if mysql_ready; then
    mysql --socket="$DB_SOCKET" -u root 2>/dev/null <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'  IDENTIFIED BY '$DB_PASS';
FLUSH PRIVILEGES;
SQL
    echo "[ssd_panel] DB + user ready"

    # ── 3. Import dump if DB is empty ─────────────────────────────────────────
    TABLE_COUNT=$(mysql --socket="$DB_SOCKET" -u root "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l)
    if [ "$TABLE_COUNT" -lt 5 ] && [ -f "$SQL_DUMP" ]; then
        echo "[ssd_panel] Importing database (~8k lines)..."
        mysql --socket="$DB_SOCKET" -u root "$DB_NAME" < "$SQL_DUMP" 2>&1 | tail -3
        echo "[ssd_panel] Import done"
    else
        echo "[ssd_panel] DB has $TABLE_COUNT tables — skipping import"
    fi
fi

# ── 4. Start PHP built-in server with router ─────────────────────────────────
echo "[ssd_panel] PHP server on 0.0.0.0:$PORT (dir=$(pwd))"
exec php -S "0.0.0.0:$PORT" router.php 2>&1
