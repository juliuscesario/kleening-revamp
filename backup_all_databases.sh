#!/bin/bash

# Configuration
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p "$BACKUP_DIR"

# Database Credentials (extracted from your local .env)
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_USER="user_kleening"
DB_PASS="password_Kl33n1ng"

# Authenticate using the password
export PGPASSWORD="$DB_PASS"

# Automatic database discovery (listing all databases the user has access to)
echo "🔍 Discovering databases for user: $DB_USER..."
DATABASES=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -t -c "SELECT datname FROM pg_database WHERE datistemplate = false AND datallowconn = true" | awk '{$1=$1}1' | grep -v "^$")

if [ -z "$DATABASES" ]; then
    echo "❌ No databases found or connection error. Please check your credentials."
    exit 1
fi

echo "✅ Found: $DATABASES"
echo "----------------------------------------"

for DB in $DATABASES
do
    OUTPUT_FILE="${BACKUP_DIR}/${DB}_${TIMESTAMP}.sql"
    echo "📦 Backing up: $DB..."
    
    # Run pg_dump to create the SQL file
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DB" -f "$OUTPUT_FILE"
    
    if [ $? -eq 0 ]; then
        echo "   -> Success: $OUTPUT_FILE"
    else
        echo "   -> Error: Failed to dump $DB"
    fi
done

echo "----------------------------------------"
echo "🎉 Backup complete! Your SQL files are in: $BACKUP_DIR"

# Secure the generated files (read/write only for owner)
chmod 600 "$BACKUP_DIR"/*.sql
