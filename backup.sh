#!/bin/bash

# Configuration
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DATABASE_NAME="kleening_revamp" # Or change to --all to dump everything
OUTPUT_FILE="${BACKUP_DIR}/${DATABASE_NAME}_${TIMESTAMP}.sql"

# Credentials (extracted from .env)
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_USER="user_kleening"
DB_PASS="password_Kl33n1ng"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "Starting backup for database: $DATABASE_NAME..."

# Execute pg_dump
PGPASSWORD="$DB_PASS" pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" "$DATABASE_NAME" > "$OUTPUT_FILE"

# Check if successful
if [ $? -eq 0 ]; then
    echo "Backup successful! Saved to: $OUTPUT_FILE"
else
    echo "Backup failed. Please check your credentials or database status."
    exit 1
fi
