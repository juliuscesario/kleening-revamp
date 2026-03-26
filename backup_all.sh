#!/bin/bash

# Configuration
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p "$BACKUP_DIR"

# Database Credentials (extracted from .env)
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_USER="user_kleening"
DB_PASS="password_Kl33n1ng"

# If you have postgres superuser access, you can uncomment this:
# pg_dumpall -h "$DB_HOST" -p "$DB_PORT" -U postgres > "$BACKUP_DIR/full_cluster_$TIMESTAMP.sql"

# List of databases to backup
# You can add more database names here
DATABASES=("kleening_revamp" "servisbos")

echo "Starting backup for all specified databases..."

export PGPASSWORD="$DB_PASS"

for DB in "${DATABASES[@]}"
do
    OUTPUT_FILE="${BACKUP_DIR}/${DB}_${TIMESTAMP}.sql"
    echo "Backing up: $DB..."
    
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DB" -f "$OUTPUT_FILE"
    
    if [ $? -eq 0 ]; then
        echo "Success: $DB -> $OUTPUT_FILE"
    else
        echo "Error: Failed to back up $DB. Skipping..."
    fi
done

echo "Backup process completed. Backups are stored in: $BACKUP_DIR"
chmod 600 "$BACKUP_DIR"/*.sql # Set secure permissions for the backup files
