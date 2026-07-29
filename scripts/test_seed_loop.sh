#!/bin/bash
export PATH="/tmp/php-8.3.20/sapi/cli:$PATH"
cd /home/z/my-project/thesummit
for i in 1 2 3; do
  rm -f database/database.sqlite && touch database/database.sqlite
  php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$kernel = \$app->make('Illuminate\Contracts\Console\Kernel');
    \$status = \$kernel->handle(
        new Symfony\Component\Console\Input\ArgvInput(['artisan', 'migrate:fresh', '--seed', '--force']),
        new Symfony\Component\Console\Output\ConsoleOutput()
    );
    echo \"Run $i: status=\" . \$status . PHP_EOL;
    \$kernel->terminate(null, \$status);
  " 2>&1
done
