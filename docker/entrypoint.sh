#!/bin/bash
set -e

# Wait for MySQL database connection to be ready if DB_HOST is set
if [ -n "$DB_HOST" ]; then
  echo "Waiting for database ($DB_HOST:$DB_PORT) to be ready..."
  # Wait up to 30 seconds
  for i in {1..30}; do
    if php -r "
      try {
        \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
      } catch (Exception \$e) {
        exit(1);
      }
    "; then
      echo "Database is ready!"
      break
    fi
    echo "Database not ready yet, waiting..."
    sleep 2
  done
fi

# Run migrations if migrations/run.php exists
if [ -f "migrations/run.php" ]; then
  echo "Running migrations..."
  php migrations/run.php || true
fi

# Execute main CMD
exec "$@"
