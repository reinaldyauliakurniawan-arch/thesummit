#!/bin/bash
export PATH="/tmp/php-8.3.20/sapi/cli:$PATH"
cd /home/z/my-project/thesummit

echo "=== E2E VERIFICATION ==="

# Clean DB
rm -f database/database.sqlite && touch database/database.sqlite

# Fresh migrate+seed
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make('Illuminate\Contracts\Console\Kernel');
\$status = \$kernel->handle(
    new Symfony\Component\Console\Input\ArgvInput(['artisan', 'migrate:fresh', '--seed', '--force']),
    new Symfony\Component\Console\Output\ConsoleOutput()
);
echo 'Migrate+Seed: status=' . \$status . PHP_EOL;
\$kernel->terminate(null, \$status);
"

# Start server on port 8123
php artisan serve --port=8123 &
SERVER_PID=$!
sleep 3

echo ""
echo "=== Registering 3 users ==="

# Register user 1
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt -L -X POST http://127.0.0.1:8123/register \
  -d "name=PlayerOne&email=p1@test.com&password=password123&password_confirmation=password123")
echo "Register P1: HTTP $RESP"

# Register user 2
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies2.txt -b /tmp/cookies2.txt -L -X POST http://127.0.0.1:8123/register \
  -d "name=PlayerTwo&email=p2@test.com&password=password123&password_confirmation=password123")
echo "Register P2: HTTP $RESP"

# Register user 3
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies3.txt -b /tmp/cookies3.txt -L -X POST http://127.0.0.1:8123/register \
  -d "name=PlayerThree&email=p3@test.com&password=password123&password_confirmation=password123")
echo "Register P3: HTTP $RESP"

echo ""
echo "=== Login as P1 and create room ==="
# Login P1
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt -L -X POST http://127.0.0.1:8123/login \
  -d "email=p1@test.com&password=password123")
echo "Login P1: HTTP $RESP"

# Create room
RESP=$(curl -s -D /tmp/create_headers.txt -c /tmp/cookies1.txt -b /tmp/cookies1.txt -L -X POST http://127.0.0.1:8123/rooms 2>/dev/null)
CREATE_CODE=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt -L -X POST http://127.0.0.1:8123/rooms)
echo "Create Room: HTTP $CREATE_CODE"

# Get room code from DB
ROOM_CODE=$(php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$room = \$app->make('Illuminate\Database\DatabaseManager')->table('game_rooms')->first();
echo \$room->code;
" 2>/dev/null)
echo "Room Code: $ROOM_CODE"

echo ""
echo "=== P2 joins room ==="
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies2.txt -b /tmp/cookies2.txt -L "http://127.0.0.1:8123/rooms/join/$ROOM_CODE")
echo "P2 Join: HTTP $RESP"

echo ""
echo "=== P3 joins room ==="
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies3.txt -b /tmp/cookies3.txt -L "http://127.0.0.1:8123/rooms/join/$ROOM_CODE")
echo "P3 Join: HTTP $RESP"

echo ""
echo "=== P1 starts game ==="
ROOM_ID=$(php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$room = \$app->make('Illuminate\Database\DatabaseManager')->table('game_rooms')->first();
echo \$room->id;
" 2>/dev/null)
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt -L -X POST "http://127.0.0.1:8123/rooms/$ROOM_ID/start")
echo "Start Game: HTTP $RESP"

echo ""
echo "=== Check game board access ==="
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt "http://127.0.0.1:8123/game/$ROOM_ID")
echo "Game Board P1: HTTP $RESP"
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies2.txt -b /tmp/cookies2.txt "http://127.0.0.1:8123/game/$ROOM_ID")
echo "Game Board P2: HTTP $RESP"
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies3.txt -b /tmp/cookies3.txt "http://127.0.0.1:8123/game/$ROOM_ID")
echo "Game Board P3: HTTP $RESP"

echo ""
echo "=== Check summary page ==="
RESP=$(curl -s -o /dev/null -w "%{http_code}" -c /tmp/cookies1.txt -b /tmp/cookies1.txt "http://127.0.0.1:8123/game/$ROOM_ID/summary")
echo "Summary P1: HTTP $RESP"

# Check for errors in log
echo ""
echo "=== Check laravel.log for errors ==="
if [ -f storage/logs/laravel.log ]; then
  ERROR_COUNT=$(grep -c "ERROR\|CRITICAL\|Exception" storage/logs/laravel.log 2>/dev/null || echo 0)
  echo "Error count in log: $ERROR_COUNT"
  if [ "$ERROR_COUNT" -gt "0" ]; then
    echo "Last error:"
    grep -m 1 "ERROR\|Exception" storage/logs/laravel.log | tail -1
  fi
else
  echo "No log file (good)"
fi

# Kill server
kill $SERVER_PID 2>/dev/null
echo ""
echo "=== E2E COMPLETE ==="
