# 🚀 Quick Start Guide - Accounting Module

## Installation (5 Minutes)

### Step 1: Run Migrations
```bash
cd microfinance-laravel
php artisan migrate
```

Expected output: 5 new tables created (banks, transfers, expenses, revenue_entries, chart_of_accounts updated)

### Step 2: Seed Data
```bash
php artisan db:seed --class=ChartOfAccountsSeeder
php artisan db:seed --class=BanksSeeder
php artisan db:seed --class=AccountingDataSeeder
```

This creates:
- ✅ Complete chart of accounts (30+ accounts)
- ✅ 9 banks/payment methods
- ✅ Sample transactions for testing

### Step 3: Create Permissions
```bash
php artisan tinker
```

Paste this:
```php
use Spatie\Permission\Models\Permission;

$permissions = [
    'manage_banks', 'manage_expenses', 'approve_expenses', 'post_expenses',
    'manage_revenues', 'approve_revenues', 'post_revenues',
    'manage_transfers', 'approve_transfers', 'post_transfers',
    'view_financial_reports'
];

foreach($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission]);
}

$admin = \Spatie\Permission\Models\Role::findByName('admin');
if($admin) $admin->givePermissionTo($permissions);

echo "Permissions created and assigned to admin!\n";
exit;
```

### Step 4: Test
```bash
php artisan serve
```

Visit: `http://localhost:8000/accounting`

## 🎯 Key URLs

| Feature | URL | Description |
|---------|-----|-------------|
| Dashboard | `/accounting` | Main accounting dashboard |
| Banks | `/accounting/banks` | Manage payment accounts |
| Expenses | `/accounting/expenses` | Record and manage expenses |
| Revenues | `/accounting/revenues` | Record income |
| Transfers | `/accounting/transfers` | Inter-account transfers |
| Chart of Accounts | `/accounting/chart-of-accounts` | Account structure |
| P&L Report | `/accounting/reports/profit-loss` | Income statement |
| Balance Sheet | `/accounting/reports/balance-sheet` | Financial position |
| Cash Flow | `/accounting/reports/cash-flow` | Cash movements |
| Revenue Board | `/accounting/reports/revenue-board` | Revenue analysis |

## 📝 Common Tasks

### Create an Expense
```
1. Go to /accounting/expenses/create
2. Fill form:
   - Date: Today
   - Account: "Rent Expense" (or any expense account)
   - Amount: 1000
   - Payment Method: Cash or Cheque
   - Description: "Office rent for January"
3. Submit → Status: Pending
4. As admin, approve the expense
5. Post to ledger → Creates automatic journal entries
6. Check P&L report → Expense appears
```

### Create a Revenue Entry
```
1. Go to /accounting/revenues/create
2. Fill form:
   - Date: Today
   - Revenue Type: Interest Received
   - Account: Auto-selected based on type
   - Amount: 500
   - Description: "Interest from loan #123"
3. Submit → Approve → Post
4. Check P&L → Revenue appears
```

### Create a Transfer
```
1. Go to /accounting/transfers/create
2. Fill form:
   - From Account: "Cash on Hand"
   - To Account: "BnB Bank"
   - Amount: 5000
   - Type: Deposit
   - Description: "Bank deposit"
3. Submit → Approve → Post
4. Check balances updated in real-time
```

### View Financial Reports
```
P&L Report:
1. Go to /accounting/reports/profit-loss
2. Select date range
3. View revenues, expenses, net income
4. Export to PDF/Excel/CSV

Balance Sheet:
1. Go to /accounting/reports/balance-sheet
2. Select "as of" date
3. View assets, liabilities, equity
4. Export if needed
```

## 🔄 Workflow

```
Create Entry → Pending → Approve → Post to Ledger → Update Balances → Reflect in Reports
```

### Expense Workflow
```mermaid
Expense Created (Pending)
    ↓
Approver Reviews → Approve/Reject
    ↓
If Approved → Post to Ledger
    ↓
Auto-creates Journal Entry:
    DR: Expense Account
    CR: Cash/Bank Account
    ↓
Updates Account Balances
    ↓
Broadcasts Real-Time Event
    ↓
Dashboard & Reports Update
```

## 🎨 UI Components Available

### Livewire Components (Real-Time)
- `<livewire:accounting-dashboard />` - Full dashboard with metrics
- `<livewire:expense-form-live />` - Expense form with validation
- `<livewire:transfer-form-live />` - Transfer form
- `<livewire:revenue-form-live />` - Revenue form

### Usage Example
```blade
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <livewire:accounting-dashboard />
</div>
@endsection
```

## 📊 Sample Data Included

After seeding, you'll have:
- **10 Sample Expenses** (various dates, amounts, accounts)
- **10 Sample Revenues** (different revenue types)
- **5 Sample Transfers** (between accounts)
- **3 Sample Journal Entries** (balanced entries)

## 🔍 Testing Checklist

- [ ] Can log in as admin
- [ ] Can access `/accounting`
- [ ] Dashboard shows metrics
- [ ] Can create expense
- [ ] Can approve expense
- [ ] Can post expense
- [ ] Expense appears in P&L
- [ ] Account balance updated
- [ ] Can create revenue
- [ ] Revenue appears in P&L
- [ ] Net income calculates correctly
- [ ] Can create transfer
- [ ] Both accounts update
- [ ] Can view all reports
- [ ] Can export to PDF
- [ ] Real-time updates work (open 2 browsers, post expense in one, see update in other)

## 🐛 Troubleshooting

### "Permission denied"
**Fix:** Run the permissions creation script (Step 3)

### "Table doesn't exist"
**Fix:** Run migrations (Step 1)

### "No accounts available"
**Fix:** Run seeders (Step 2)

### "Expense won't post"
**Fix:** 
1. Ensure expense is approved first
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify chart of accounts seeded correctly

### "Real-time not working"
**Optional:** Broadcasting requires additional setup:
```env
BROADCAST_DRIVER=pusher
# Or use Laravel Reverb (built-in)
```
But polling works without it (updates every 10s).

## 📱 Mobile Responsive

All views are responsive:
- Cards stack vertically on mobile
- Tables become scrollable
- Forms adapt to screen size
- Metrics displayed in grid

## 🎯 Pro Tips

1. **Auto-Number Generation**: Expense/Transfer/Revenue numbers auto-generate. Don't manually enter.

2. **Date Filters**: All reports support date ranges. Use them!

3. **Real-Time Updates**: Livewire components poll every 10 seconds. For instant updates, set up broadcasting.

4. **Approval Workflow**: Expenses/Revenues/Transfers require approval before posting. Separate concerns for security.

5. **Activity Logs**: Every action is logged. View in `/accounting/audit-trail`.

6. **Export**: All reports export to PDF, Excel, CSV. Click export button.

7. **Chart of Accounts**: System accounts (is_system_account=true) cannot be deleted.

8. **Double-Entry**: Always maintained. Every transaction creates balanced journal entries.

## 📚 Advanced Features

### Link Loan Repayment to Revenue
When a loan is repaid, automatically create revenue entry:
```php
$revenue = RevenueEntry::create([
    'revenue_number' => RevenueEntry::generateRevenueNumber(),
    'transaction_date' => now(),
    'account_id' => ChartOfAccount::where('code', '4000')->first()->id,
    'revenue_type' => 'interest_received',
    'description' => "Interest from loan #{$loan->loan_number}",
    'amount' => $interestAmount,
    'loan_id' => $loan->id,
    'client_id' => $loan->client_id,
    'branch_id' => auth()->user()->branch_id,
    'user_id' => auth()->id(),
    'status' => 'pending',
]);
```

### Automatic Transfers on Loan Disbursement
```php
$transfer = Transfer::create([
    'transfer_number' => Transfer::generateTransferNumber(),
    'transaction_date' => now(),
    'from_account_id' => ChartOfAccount::where('code', '1100')->first()->id, // Bank
    'to_account_id' => ChartOfAccount::where('code', '1200')->first()->id, // Loan Portfolio
    'amount' => $loan->amount,
    'type' => 'disbursement',
    'description' => "Loan disbursement #{$loan->loan_number}",
    'branch_id' => $loan->branch_id,
    'user_id' => auth()->id(),
    'status' => 'posted', // Auto-post disbursements
]);
$transfer->post();
```

## 🎓 Learning Resources

- **Double-Entry Accounting**: https://en.wikipedia.org/wiki/Double-entry_bookkeeping
- **Laravel Livewire**: https://livewire.laravel.com/docs/quickstart
- **Chart.js**: https://www.chartjs.org/docs/latest/
- **DataTables**: https://datatables.net/

## ✅ You're Ready!

Your accounting module is fully set up and ready to use. Start by:
1. Creating a few test expenses
2. Approving and posting them
3. Viewing the P&L report
4. Exploring the dashboard

Have fun! 🎉

