# The Summit — Leadership Pipeline Board Game

A multiplayer board game simulation built with Laravel 11 + Livewire 3 that assesses leadership behavior through evidence-based gameplay. 3–6 players make decisions on expedition cards, and the system generates a Reflection Report and Leadership Role Assessment (LRA) based on observable evidence.

## Requirements

- PHP 8.3+ with extensions: mbstring, openssl, pdo_sqlite (or pdo_mysql), xml, curl, bcmath, fileinfo
- Composer 2.x
- Node.js 18+ / npm

## Quick Start (SQLite — zero config)

```bash
git clone https://github.com/reinaldyauliakurniawan-arch/thesummit.git
cd thesummit
composer install
cp .env.example .env
php artisan key:generate
```

Configure for SQLite:

```bash
# In .env, set:
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Remove or blank out MySQL variables:
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Create the database file and run migrations:

```bash
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Open `http://localhost:8000` in your browser.

## Quick Start (MySQL)

```bash
git clone https://github.com/reinaldyauliakurniawan-arch/thesummit.git
cd thesummit
composer install
cp .env.example .env
php artisan key:generate
```

In `.env`, configure your MySQL connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=the_summit
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then:

```bash
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Running Tests

```bash
php artisan test
```

## How to Play

1. Register an account and log in.
2. Create a game room (3–6 players needed).
3. Share the room code with other players.
4. Once all players have joined, the host starts the game.
5. Each turn, draw an expedition card and choose Option A or B.
6. Cards affect your stats: **MP** (Mindset Points), **SP** (Skillset Points), **TT** (Trust Tokens).
7. After all rounds, view your Reflection Report and Leadership Assessment.

## Troubleshooting

### `unable to open database file` (SQLite)

Make sure `DB_DATABASE` in `.env` points to a writable path: `database/database.sqlite`, and the file (or its parent directory) exists.

### `table "notifications" already exists`

If you have a stale database, run `php artisan migrate:fresh --seed --force` to start clean.

### Artisan commands crash with `TypeError: Collision\Handler::setOutput()`

This can occur in non-interactive terminal environments. Ensure `nunomaduro/collision` is at `^8.1` (not pinned to `8.1`) in `composer.json`. Run `composer update nunomaduro/collision` if needed.
