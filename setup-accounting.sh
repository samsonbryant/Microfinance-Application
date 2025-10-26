#!/bin/bash

echo "=============================================="
echo "  Accounting Module Setup Script"
echo "  Microfinance Management System"
echo "=============================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Run Migrations
echo -e "${BLUE}Step 1/4: Running migrations...${NC}"
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations completed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi
echo ""

# Step 2: Seed Chart of Accounts
echo -e "${BLUE}Step 2/4: Seeding Chart of Accounts...${NC}"
php artisan db:seed --class=ChartOfAccountsSeeder --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Chart of Accounts seeded (30+ accounts created)${NC}"
else
    echo -e "${RED}✗ Seeding failed${NC}"
    exit 1
fi
echo ""

# Step 3: Seed Banks
echo -e "${BLUE}Step 3/4: Seeding Banks and Payment Methods...${NC}"
php artisan db:seed --class=BanksSeeder --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Banks seeded (9 banks/payment methods created)${NC}"
else
    echo -e "${RED}✗ Seeding failed${NC}"
    exit 1
fi
echo ""

# Step 4: Seed Sample Data
echo -e "${BLUE}Step 4/4: Seeding sample accounting data...${NC}"
php artisan db:seed --class=AccountingDataSeeder --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Sample data seeded (expenses, revenues, transfers)${NC}"
else
    echo -e "${YELLOW}⚠ Sample data seeding failed (optional)${NC}"
fi
echo ""

# Create Permissions
echo -e "${BLUE}Creating accounting permissions...${NC}"
php artisan tinker --execute="
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

\$permissions = [
    'manage_banks',
    'manage_expenses',
    'approve_expenses',
    'post_expenses',
    'manage_revenues',
    'approve_revenues',
    'post_revenues',
    'manage_transfers',
    'approve_transfers',
    'post_transfers',
    'view_financial_reports'
];

foreach(\$permissions as \$permission) {
    Permission::firstOrCreate(['name' => \$permission]);
}

\$admin = Role::where('name', 'admin')->orWhere('name', 'Admin')->first();
if(\$admin) {
    \$admin->givePermissionTo(\$permissions);
    echo 'Permissions assigned to admin role\n';
} else {
    echo 'Admin role not found. Please assign permissions manually.\n';
}
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Permissions created and assigned${NC}"
else
    echo -e "${YELLOW}⚠ Permission creation completed with warnings${NC}"
fi
echo ""

# Clear caches
echo -e "${BLUE}Clearing application caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

# Success message
echo -e "${GREEN}=============================================="
echo -e "  ✓ Setup completed successfully!"
echo -e "==============================================${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Visit /accounting to access the accounting module"
echo "2. Create your first expense or revenue entry"
echo "3. View financial reports at /accounting/reports/profit-loss"
echo ""
echo -e "${YELLOW}Quick URLs:${NC}"
echo "  - Dashboard: http://localhost:8000/accounting"
echo "  - Expenses: http://localhost:8000/accounting/expenses"
echo "  - Revenues: http://localhost:8000/accounting/revenues"
echo "  - Transfers: http://localhost:8000/accounting/transfers"
echo "  - P&L Report: http://localhost:8000/accounting/reports/profit-loss"
echo ""
echo -e "${GREEN}Happy accounting! 🎉${NC}"

