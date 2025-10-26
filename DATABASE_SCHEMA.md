# 📊 Database Schema - Accounting Module

## Tables Created

### 1. `banks`
Stores bank accounts and payment methods (cash, banks, mobile money).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Bank/account name (e.g., "BnB", "Cash on Hand") |
| type | enum | 'cash', 'bank', 'mobile_money' |
| account_id | bigint | FK to chart_of_accounts |
| account_number | varchar | Bank account number |
| swift_code | varchar | SWIFT/BIC code |
| branch_name | varchar | Bank branch name |
| address | text | Bank address |
| contact_person | varchar | Contact person at bank |
| phone | varchar | Contact phone |
| email | varchar | Contact email |
| current_balance | decimal(15,2) | Current balance |
| is_active | boolean | Active status |
| description | text | Notes |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Indexes:** account_id

---

### 2. `transfers`
Inter-account transfers and fund movements.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transfer_number | varchar | Unique number (TRF20250116001) |
| transaction_date | date | Transaction date |
| from_account_id | bigint | FK to chart_of_accounts (source) |
| to_account_id | bigint | FK to chart_of_accounts (destination) |
| from_bank_id | bigint | FK to banks (optional) |
| to_bank_id | bigint | FK to banks (optional) |
| amount | decimal(15,2) | Transfer amount |
| type | enum | 'deposit', 'withdrawal', 'disbursement', 'expense', 'transfer' |
| reference_number | varchar | External reference |
| description | text | Description |
| branch_id | bigint | FK to branches |
| user_id | bigint | FK to users (creator) |
| status | enum | 'pending', 'approved', 'rejected', 'posted' |
| approved_by | bigint | FK to users (approver) |
| approved_at | timestamp | Approval timestamp |
| posted_at | timestamp | Posted timestamp |
| rejection_reason | text | Rejection reason |
| metadata | json | Additional data |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Indexes:** transaction_date, status

---

### 3. `expenses`
Expense transactions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| expense_number | varchar | Unique number (EXP20250116001) |
| transaction_date | date | Transaction date |
| account_id | bigint | FK to chart_of_accounts (expense account) |
| description | text | Expense description |
| amount | decimal(15,2) | Expense amount |
| payment_method | enum | 'cash', 'cheque', 'bank_transfer', 'mobile_money' |
| bank_id | bigint | FK to banks (if not cash) |
| reference_number | varchar | Cheque number or transaction reference |
| payee_name | varchar | Who received payment |
| branch_id | bigint | FK to branches |
| user_id | bigint | FK to users (creator) |
| status | enum | 'pending', 'approved', 'rejected', 'posted' |
| approved_by | bigint | FK to users (approver) |
| approved_at | timestamp | Approval timestamp |
| posted_at | timestamp | Posted timestamp |
| rejection_reason | text | Rejection reason |
| receipt_file | varchar | Receipt file path |
| metadata | json | Additional data |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Indexes:** transaction_date, status

---

### 4. `revenue_entries`
Revenue/income transactions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| revenue_number | varchar | Unique number (REV20250116001) |
| transaction_date | date | Transaction date |
| account_id | bigint | FK to chart_of_accounts (revenue account) |
| revenue_type | enum | 'interest_received', 'default_charges', 'processing_fee', 'system_charge', 'other' |
| description | text | Revenue description |
| amount | decimal(15,2) | Revenue amount |
| bank_id | bigint | FK to banks (receiving account) |
| reference_number | varchar | External reference |
| loan_id | bigint | FK to loans (if from loan) |
| client_id | bigint | FK to clients (if from client) |
| branch_id | bigint | FK to branches |
| user_id | bigint | FK to users (creator) |
| status | enum | 'pending', 'approved', 'rejected', 'posted' |
| approved_by | bigint | FK to users (approver) |
| approved_at | timestamp | Approval timestamp |
| posted_at | timestamp | Posted timestamp |
| rejection_reason | text | Rejection reason |
| metadata | json | Additional data |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Indexes:** transaction_date, status, revenue_type

---

### 5. `chart_of_accounts` (Enhanced)
**New columns added:**

| Column | Type | Description |
|--------|------|-------------|
| current_balance | decimal(15,2) | Real-time balance (added) |
| last_transaction_date | date | Last transaction date (added) |

**Existing columns:**
- id, code, name, type, category, parent_id, is_active, description, normal_balance, opening_balance, currency, is_system_account

---

## Relationships

### Banks
```
banks.account_id → chart_of_accounts.id
banks → expenses (one-to-many)
banks → revenue_entries (one-to-many)
banks → transfers (one-to-many as from_bank)
banks → transfers (one-to-many as to_bank)
```

### Transfers
```
transfers.from_account_id → chart_of_accounts.id
transfers.to_account_id → chart_of_accounts.id
transfers.from_bank_id → banks.id
transfers.to_bank_id → banks.id
transfers.branch_id → branches.id
transfers.user_id → users.id
transfers.approved_by → users.id
```

### Expenses
```
expenses.account_id → chart_of_accounts.id
expenses.bank_id → banks.id
expenses.branch_id → branches.id
expenses.user_id → users.id
expenses.approved_by → users.id
```

### Revenue Entries
```
revenue_entries.account_id → chart_of_accounts.id
revenue_entries.bank_id → banks.id
revenue_entries.loan_id → loans.id
revenue_entries.client_id → clients.id
revenue_entries.branch_id → branches.id
revenue_entries.user_id → users.id
revenue_entries.approved_by → users.id
```

---

## Double-Entry Mechanics

### When Expense is Posted:
```sql
-- Debit: Expense Account
-- Credit: Cash/Bank Account

INSERT INTO general_ledger_entries (
    entry_number, account_id, debit, credit, 
    reference_type, reference_id
)
VALUES
    ('GLE20250116001', expense.account_id, expense.amount, 0, 'expense', expense.id),
    ('GLE20250116001', payment_account_id, 0, expense.amount, 'expense', expense.id);
```

### When Revenue is Posted:
```sql
-- Debit: Cash/Bank Account
-- Credit: Revenue Account

INSERT INTO general_ledger_entries ...
VALUES
    ('GLE20250116002', receiving_account_id, revenue.amount, 0, 'revenue', revenue.id),
    ('GLE20250116002', revenue.account_id, 0, revenue.amount, 'revenue', revenue.id);
```

### When Transfer is Posted:
```sql
-- Debit: To Account
-- Credit: From Account

INSERT INTO general_ledger_entries ...
VALUES
    ('GLE20250116003', transfer.to_account_id, transfer.amount, 0, 'transfer', transfer.id),
    ('GLE20250116003', transfer.from_account_id, 0, transfer.amount, 'transfer', transfer.id);
```

---

## Sample Chart of Accounts

### Assets (1000-1999)
```
1000 - Cash on Hand (debit normal)
1001 - Petty Cash (debit normal)
1100 - Bank Accounts (debit normal)
1200 - Loan Portfolio (debit normal)
1201 - Allowance for Loan Losses (credit normal)
1300 - Accounts Receivable (debit normal)
1400 - Office Equipment (debit normal)
1401 - Accumulated Depreciation - Equipment (credit normal)
```

### Liabilities (2000-2999)
```
2000 - Client Savings (credit normal)
2100 - Interest Payable (credit normal)
2200 - Accounts Payable (credit normal)
2300 - Bank Loans Payable (credit normal)
```

### Equity (3000-3999)
```
3000 - Owner's Capital (credit normal)
3100 - Retained Earnings (credit normal)
```

### Revenue (4000-4999)
```
4000 - Loan Interest Income (credit normal)
4100 - Penalty Income (credit normal)
4200 - Processing Fee Income (credit normal)
4300 - System Charge Income (credit normal)
4400 - Other Income (credit normal)
```

### Expenses (5000-5999)
```
5000 - Salaries and Wages (debit normal)
5100 - Rent Expense (debit normal)
5200 - Utilities Expense (debit normal)
5300 - Office Supplies (debit normal)
5400 - Transportation Expense (debit normal)
5500 - Marketing Expense (debit normal)
5600 - Depreciation Expense (debit normal)
5700 - Loan Loss Expense (debit normal)
5800 - Interest Expense (debit normal)
5900 - Miscellaneous Expense (debit normal)
```

---

## ERD (Entity Relationship Diagram)

```
┌──────────────────┐
│  chart_of_       │
│  accounts        │◄────┐
└──────────────────┘     │
         ▲               │
         │               │
         │ account_id    │ from/to_account_id
         │               │
┌──────────────────┐     │
│     banks        │     │
└──────────────────┘     │
         ▲               │
         │               │
         │ bank_id       │
         │               │
┌──────────────────┐     │
│    expenses      ├─────┘
└──────────────────┘
         │
         │ reference
         ▼
┌──────────────────┐
│ general_ledger_  │
│    entries       │
└──────────────────┘
```

---

## Balance Calculation

### Asset/Expense Accounts (Normal Debit):
```
current_balance = opening_balance + SUM(debits) - SUM(credits)
```

### Liability/Equity/Revenue Accounts (Normal Credit):
```
current_balance = opening_balance + SUM(credits) - SUM(debits)
```

### Example:
```php
// Cash on Hand (Asset, Normal Debit)
// Opening: $10,000
// Debits: $5,000 (deposits)
// Credits: $3,000 (withdrawals/expenses)
// Current Balance: $10,000 + $5,000 - $3,000 = $12,000
```

---

## Migration Order

1. `create_banks_table` (depends on chart_of_accounts)
2. `create_transfers_table` (depends on chart_of_accounts, banks)
3. `create_expenses_table` (depends on chart_of_accounts, banks)
4. `create_revenue_entries_table` (depends on chart_of_accounts, banks, loans, clients)
5. `add_balance_to_chart_of_accounts` (alters chart_of_accounts)

---

## Sample Queries

### Get Current Cash Position
```sql
SELECT 
    c.name,
    c.current_balance
FROM chart_of_accounts c
WHERE c.category IN ('cash_on_hand', 'cash_in_bank')
    AND c.is_active = 1
ORDER BY c.code;
```

### Get Monthly Expenses
```sql
SELECT 
    c.name AS account,
    SUM(e.amount) AS total
FROM expenses e
JOIN chart_of_accounts c ON e.account_id = c.id
WHERE e.status = 'posted'
    AND e.transaction_date BETWEEN '2025-01-01' AND '2025-01-31'
GROUP BY c.id, c.name
ORDER BY total DESC;
```

### Get Net Income for Period
```sql
SELECT 
    (SELECT SUM(amount) FROM revenue_entries WHERE status='posted' AND transaction_date BETWEEN ? AND ?) -
    (SELECT SUM(amount) FROM expenses WHERE status='posted' AND transaction_date BETWEEN ? AND ?)
AS net_income;
```

---

This completes the database schema documentation for the Accounting Module.

