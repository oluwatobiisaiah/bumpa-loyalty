#!/bin/sh

echo "Waiting for MySQL to be ready..."

# Wait for MySQL to accept connections
until php -r "
try {
    \$pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
} catch (Exception \$e) {
    exit(1);
}
";
do
  echo "MySQL not ready yet - sleeping..."
  sleep 3
done

echo "MySQL is up!"

# Run migrations only if not already run
MIGRATION_COUNT=$(php -r "
\$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);
try {
    \$stmt = \$pdo->query('SELECT COUNT(*) FROM migrations');
    echo \$stmt->fetchColumn();
} catch (Exception \$e) {
    echo '0';
}
")

if [ "$MIGRATION_COUNT" -eq 0 ]; then
  echo "Running migrations..."
  if php artisan migrate --force; then
    echo "Migrations completed."
  else
    echo "Migrations failed, but continuing..."
  fi
else
  echo "Migrations already run — skipping."
fi

# Run seeders only if the database is empty
TABLE_COUNT=$(php -r "
\$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);
\$stmt = \$pdo->query('SELECT COUNT(*) FROM users');  # pick a table that always exists after migrations
echo \$stmt->fetchColumn();
")

if [ "$TABLE_COUNT" -eq 0 ]; then
  echo "Database empty — running seeders..."
  php artisan db:seed --force
else
  echo "Database already seeded — skipping."
fi

# Wait for RabbitMQ to be ready
echo "Waiting for RabbitMQ to be ready..."
RABBIT_HOST="${RABBITMQ_HOST:-rabbitmq}"
RABBIT_PORT="${RABBITMQ_PORT:-5672}"

php -r "
while (!@fsockopen('$RABBIT_HOST', $RABBIT_PORT)) {
    echo 'RabbitMQ not ready yet - sleeping...'.PHP_EOL;
    sleep(3);
}
echo 'RabbitMQ is up!'.PHP_EOL;
"

echo "Starting PHP-FPM..."
exec php-fpm
