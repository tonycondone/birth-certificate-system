#!/bin/bash
# Log Rotation Shell Script for Unix/Linux
# Runs PHP log rotation script

# Set PHP and script paths
PHP_PATH=$(which php)
SCRIPT_PATH="$(dirname "$0")/log_rotation.php"

# Ensure script is executable
chmod +x "$SCRIPT_PATH"

# Run log rotation script
"$PHP_PATH" "$SCRIPT_PATH"

# Check script exit status
EXIT_CODE=$?
if [ $EXIT_CODE -ne 0 ]; then
    echo "Log rotation failed with error code $EXIT_CODE"
    exit $EXIT_CODE
fi 