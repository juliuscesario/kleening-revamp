#!/bin/bash

# Configuration
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p "$BACKUP_DIR"

# Database Credentials (extracted from .env)
# These are pulled from your project's .env file
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_USER="user_kleening"
DB_PASS="password_Kl33n1ng"

# Authenticate with pgpass or environment variable
export PGPASSWORD="$DB_PASS"

# Automatic database discovery (get a list of all accessible databases)
echo "Discovering databases for user: $DB_USER..."
DATABASES=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -t -c "SELECT datname FROM pg_database WHERE datistemplate = false AND datallowconn = true" | awk '{$1=$1}1' | grep -v "^$")

if [ -z "$DATABASES" ]; then
    echo "No databases found or connection error. Please check your credentials."
    exit 1
fi

echo "The following databases will be backed up:"
echo "$DATABASES"
echo "----------------------------------------"

for DB in $DATABASES
do
    OUTPUT_FILE="${BACKUP_DIR}/${DB}_${TIMESTAMP}.sql"
    echo "Processing: $DB..."
    
    # Backup using pg_dump
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DB" -f "$OUTPUT_FILE"
    
    if [ $? -eq 0 ]; then
        echo "Success: $DB -> $OUTPUT_FILE"
        # Optional: compress to save space
        # gzip "$OUTPUT_FILE"
    else
        echo "Error: Failed to back up $DB. Skipping..."
    fi
done

echo "----------------------------------------"
echo "Backup process completed. All SQL files are in: $BACKUP_DIR"

# Secure the files
chmod 600 "$BACKUP_DIR"/*.sql
