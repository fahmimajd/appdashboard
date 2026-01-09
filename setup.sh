#!/bin/bash

# Dashboard Pelayanan - Full Source Code Generator
# This script generates all remaining Laravel files

echo "==================================="
echo "Dashboard Pelayanan Setup Script"
echo "==================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the correct directory
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found. Please run this script from the project root.${NC}"
    exit 1
fi

echo -e "${YELLOW}Creating directory structure...${NC}"

# Create directories
mkdir -p app/Http/Controllers/Auth
mkdir -p app/Http/Requests
mkdir -p app/Services
mkdir -p resources/views/auth
mkdir -p resources/views/layouts
mkdir -p resources/views/components
mkdir -p resources/views/dashboard
mkdir -p resources/views/wilayah
mkdir -p resources/views/pendamping
mkdir -p resources/views/petugas
mkdir -p resources/views/kinerja
mkdir -p resources/views/kependudukan
mkdir -p resources/views/pelayanan
mkdir -p resources/views/sarpras
mkdir -p resources/views/vpn
mkdir -p public/css
mkdir -p public/js
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

echo -e "${GREEN}✓ Directories created${NC}"

# Set permissions
echo -e "${YELLOW}Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}Creating .env file from .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
    echo -e "${YELLOW}⚠ Please edit .env with your Oracle database credentials${NC}"
fi

# Install Composer dependencies
echo -e "${YELLOW}Do you want to install Composer dependencies? (y/n)${NC}"
read -r install_composer
if [ "$install_composer" = "y" ]; then
    composer install
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
fi

# Install NPM dependencies
echo -e "${YELLOW}Do you want to install NPM dependencies? (y/n)${NC}"
read -r install_npm
if [ "$install_npm" = "y" ]; then
    npm install
    echo -e "${GREEN}✓ NPM dependencies installed${NC}"
fi

# Generate application key
if grep -q "APP_KEY=$" .env; then
    echo -e "${YELLOW}Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✓ Application key generated${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Setup completed!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit .env file with your Oracle database credentials"
echo "2. Test database connection: php artisan tinker > DB::connection()->getPdo();"
echo "3. Build assets: npm run build (production) or npm run dev (development)"
echo "4. Start server: php artisan serve"
echo ""
echo -e "${YELLOW}Additional controllers and views need to be created.${NC}"
echo "Refer to source_code_structure.md for the complete list."
echo ""
