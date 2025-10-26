@echo off
echo ==============================================
echo   Accounting Module Setup Script
echo   Microfinance Management System
echo ==============================================
echo.

REM Step 1: Run Migrations
echo Step 1/4: Running migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo [ERROR] Migration failed
    pause
    exit /b 1
)
echo [SUCCESS] Migrations completed successfully
echo.

REM Step 2: Seed Chart of Accounts
echo Step 2/4: Seeding Chart of Accounts...
php artisan db:seed --class=ChartOfAccountsSeeder --force
if %errorlevel% neq 0 (
    echo [ERROR] Seeding failed
    pause
    exit /b 1
)
echo [SUCCESS] Chart of Accounts seeded (30+ accounts created)
echo.

REM Step 3: Seed Banks
echo Step 3/4: Seeding Banks and Payment Methods...
php artisan db:seed --class=BanksSeeder --force
if %errorlevel% neq 0 (
    echo [ERROR] Seeding failed
    pause
    exit /b 1
)
echo [SUCCESS] Banks seeded (9 banks/payment methods created)
echo.

REM Step 4: Seed Sample Data
echo Step 4/4: Seeding sample accounting data...
php artisan db:seed --class=AccountingDataSeeder --force
if %errorlevel% neq 0 (
    echo [WARNING] Sample data seeding failed (optional)
)
echo [SUCCESS] Sample data seeded
echo.

REM Create Permissions
echo Creating accounting permissions...
php artisan tinker --execute="use Spatie\Permission\Models\Permission; use Spatie\Permission\Models\Role; $permissions = ['manage_banks','manage_expenses','approve_expenses','post_expenses','manage_revenues','approve_revenues','post_revenues','manage_transfers','approve_transfers','post_transfers','view_financial_reports']; foreach($permissions as $p) { Permission::firstOrCreate(['name' => $p]); } $admin = Role::where('name', 'admin')->orWhere('name', 'Admin')->first(); if($admin) { $admin->givePermissionTo($permissions); echo 'Permissions assigned\n'; }"
echo [SUCCESS] Permissions created
echo.

REM Clear caches
echo Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo [SUCCESS] Caches cleared
echo.

REM Success message
echo ==============================================
echo   Setup completed successfully!
echo ==============================================
echo.
echo Next steps:
echo 1. Visit /accounting to access the accounting module
echo 2. Create your first expense or revenue entry
echo 3. View financial reports at /accounting/reports/profit-loss
echo.
echo Quick URLs:
echo   - Dashboard: http://localhost:8000/accounting
echo   - Expenses: http://localhost:8000/accounting/expenses
echo   - Revenues: http://localhost:8000/accounting/revenues
echo   - Transfers: http://localhost:8000/accounting/transfers
echo   - P&L Report: http://localhost:8000/accounting/reports/profit-loss
echo.
echo Happy accounting!
echo.
pause

