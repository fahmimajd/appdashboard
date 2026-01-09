#!/bin/bash
set -e

echo "Starting OCI8 installation (Fixed Version for PHP 8.1)..."

# Check requirements
if [ ! -d "/opt/oracle/instantclient_23_26/sdk" ]; then
    echo "ERROR: Oracle Instant Client SDK not found in /opt/oracle/instantclient_23_26/sdk"
    echo "Please verify installation."
    exit 1
fi

echo "Updating PECL channel..."
sudo pecl channel-update pecl.php.net

echo "Installing system packages..."
sudo apt update
sudo apt install -y php8.1-xml php8.1-curl php8.1-mbstring php8.1-gd php8.1-zip php8.1-dev php-pear build-essential libaio1

export LD_LIBRARY_PATH=/opt/oracle/instantclient_23_26
echo "LD_LIBRARY_PATH set to $LD_LIBRARY_PATH"

echo "Running pecl install oci8-3.2.1..."
# Specify exact version 3.2.1 for PHP 8.1 compatibility
printf "instantclient,/opt/oracle/instantclient_23_26\n" | sudo -E pecl install oci8-3.2.1

echo "Verifying oci8.so exists..."
if [ -f "/usr/lib/php/20210902/oci8.so" ]; then
    echo "oci8.so found!"
else
    # Try finding it in typical locations
    FOUND=$(find /usr/lib/php -name "oci8.so" | head -n 1)
    if [ -z "$FOUND" ]; then
        echo "ERROR: oci8.so not found after installation!"
        echo "Pecl installation likely failed."
        exit 1
    else
        echo "oci8.so found at $FOUND"
    fi
fi

# Enable extension
echo "Enabling extension..."
echo "extension=oci8.so" | sudo tee /etc/php/8.1/cli/conf.d/20-oci8.ini
if [ -d "/etc/php/8.1/fpm/conf.d" ]; then
    echo "extension=oci8.so" | sudo tee /etc/php/8.1/fpm/conf.d/20-oci8.ini
fi

echo "Checking PHP modules..."
php -m | grep oci8

echo "Installation SUCCESS!"
