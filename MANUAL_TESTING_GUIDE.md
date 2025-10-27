# Manual Testing Guide - Complete Loan Workflow

## 🎯 How to Test the Complete Loan Application Workflow

### Prerequisites:
✅ Server running: `php artisan serve --port=8180`
✅ Database set up with users
✅ All migrations completed
✅ Users seeded

---

## 📋 COMPLETE WORKFLOW TEST (15-30 minutes)

### 🔹 STEP 1: BORROWER SUBMITS LOAN APPLICATION

**Time: ~5 minutes**

1. **Open your browser** and navigate to:
   ```
   http://127.0.0.1:8180/login
   ```

2. **Login as Borrower:**
   - Email: `borrower@microfinance.com`
   - Password: `borrower123`
   - Click "Sign In"

3. **Expected:** You should be redirected to Borrower Dashboard

4. **Verify Sidebar:**
   - ✓ Should NOT see "My Reports" section (removed)
   - ✓ Should see "Transaction History" under "History"
   - ✓ Should see "Apply for Loan" link

5. **Click "Apply for Loan"** in sidebar

6. **Fill the Real-Time Loan Application Form:**
   
   ```
   Loan Amount: 5000
   Interest Rate: 12 (should be pre-filled)
   Term: 12 months
   Purpose: "Business expansion to open new store"
   Employment Status: Self Employed
   Monthly Income: 2000
   Existing Loans: No
   Collateral: "2020 Toyota Camry, Good condition, approx $15,000 value"
   ```

7. **Watch the Live Calculation Panel (Right Side):**
   - As you type the amount, it should update instantly
   - **Expected values:**
     ```
     Principal: $5,000.00
     Interest: $600.00  (12% of $5,000)
     Total: $5,600.00
     Monthly: $466.67   ($5,600 ÷ 12)
     ```

8. **Click "Preview Calculation"** to verify

9. **Click "Submit Application"**

10. **Expected Results:**
    - ✅ Success message: "Loan application submitted successfully!"
    - ✅ Redirected to "My Loans" page
    - ✅ Application appears with **"Pending"** badge
    - ✅ Application number displayed (e.g., LN20241027XXXX)

11. **Take Screenshot:** Save as `test-1-borrower-submit.png`

12. **Leave browser tab open** (for checking notifications later)

---

### 🔹 STEP 2: LOAN OFFICER REVIEWS & ADDS DOCUMENTS

**Time: ~5 minutes**

1. **Open new browser tab** (or incognito window)
   - URL: `http://127.0.0.1:8180/login`

2. **Login as Loan Officer:**
   - Email: `lo@microfinance.com`
   - Password: `lo123`
   - Click "Sign In"

3. **Expected:** Redirect to Loan Officer Dashboard

4. **Verify Sidebar** is clean:
   - ✓ Should NOT see financial metrics (Portfolio Overview, PAR, etc.)
   - ✓ Should see "KYC Documents" link
   - ✓ Should see "Collaterals" link
   - ✓ Should see "Loan Repayments" link

5. **Check Notifications** (bell icon, top right):
   - Should have 1 new notification: "New loan application received"

6. **Click "Loan Applications"** in sidebar

7. **Find the borrower's application:**
   - Look for status "Pending"
   - Client: John Doe (borrower)
   - Amount: $5,000

8. **Click to open the application**

9. **Review Application Details:**
   - Verify amount: $5,000
   - Verify purpose: Business expansion
   - Verify client info

10. **Add KYC Documents:**
    - Click "KYC Documents" in sidebar
    - OR find "Upload Documents" button in application
    - Upload/create these documents:
      ```
      Document 1: National ID
      Document 2: Salary Slip / Proof of Income
      Document 3: Bank Statement (last 3 months)
      ```
    - If actual upload fails, just create records with dummy paths

11. **Add Collateral:**
    - Click "Collaterals" in sidebar
    - Click "Create Collateral"
    - Fill details:
      ```
      Client: Select the borrower
      Loan: Select the application
      Type: Vehicle
      Description: 2020 Toyota Camry
      Value: 15000
      Condition: Good
      Location: Client's residence
      Status: Pending (will be verified by BM)
      ```
    - Save collateral

12. **Return to the Loan Application**

13. **Change Status:**
    - Find status dropdown or "Review" button
    - Change status to: **"Under Review"**
    - Add notes: "All required documents collected. Ready for BM review."
    - Save

14. **Expected Results:**
    - ✅ Status changed to "Under Review"
    - ✅ Success message appears
    - ✅ Check notifications - Borrower/BM/Admin should be notified

15. **Take Screenshot:** Save as `test-2-loan-officer-review.png`

16. **Go back to Borrower tab:**
    - Refresh or check notifications
    - Should see: "Documents added to your application"

---

### 🔹 STEP 3: BRANCH MANAGER VERIFIES KYC & APPROVES

**Time: ~5 minutes**

1. **Open new browser tab**
   - URL: `http://127.0.0.1:8180/login`

2. **Login as Branch Manager:**
   - Email: `bm@microfinance.com`
   - Password: `bm123`
   - Click "Sign In"

3. **Expected:** Redirect to Branch Manager Dashboard

4. **Verify Dashboard:**
   - ✓ Should see "Collections & Payments" button
   - ✓ Should see branch metrics
   - ✓ Should see "Today's Collections" section

5. **Check Notifications:**
   - Should have 1 new notification: "Application ready for your review"

6. **Navigate to "Loan Applications"**

7. **Find application** with status "Under Review"
   - Client: John Doe
   - Amount: $5,000
   - Loan Officer: Loan Officer (who reviewed)

8. **Open the application**

9. **Verify KYC Documents:**
   - Check National ID is uploaded
   - Check Proof of Income is uploaded
   - Check Bank Statements uploaded
   - Verify all documents are complete

10. **Verify Collateral:**
    - Check collateral details
    - Type: Vehicle
    - Value: $15,000
    - Approve collateral (change status to "Approved")

11. **Review Client Credit:**
    - Check if client has good payment history
    - Verify monthly income supports loan repayment
    - Monthly payment: $466.67
    - Client income: $2,000 (sufficient)

12. **Approve Application:**
    - Click "Approve" button
    - OR change status to: **"Approved"**
    - Add approval notes: "KYC documents verified. Collateral approved. Client creditworthy. Forwarding to admin for disbursement."
    - Submit approval

13. **Expected Results:**
    - ✅ Status changed to "Approved"
    - ✅ Success message appears
    - ✅ All parties receive notifications:
      - Borrower: "KYC documents verified"
      - Loan Officer: "Application approved by Branch Manager"
      - Admin: "Ready for final approval and disbursement"

14. **Take Screenshot:** Save as `test-3-branch-manager-approve.png`

15. **Check other tabs:**
    - Borrower tab: Should see KYC verified notification
    - Loan Officer tab: Should see approval notification

---

### 🔹 STEP 4: ADMIN APPROVES & DISBURSES

**Time: ~5 minutes**

1. **Open new browser tab**
   - URL: `http://127.0.0.1:8180/login`

2. **Login as Admin:**
   - Email: `admin@microfinance.com`
   - Password: `admin123`
   - Click "Sign In"

3. **Expected:** Redirect to Admin Dashboard

4. **Verify Sidebar:**
   - ✓ Should see complete "Microbook-G5 Accounting" section
   - ✓ Should see all 9 accounting modules:
     - Accounting Dashboard (with "Live" badge)
     - Chart of Accounts
     - General Ledger
     - Journal Entries
     - Revenue Entries
     - Expense Entries
     - Expenses
     - Banks
     - Transfers
     - Reconciliations
     - Financial Reports
     - Audit Trail

5. **Check Notifications:**
   - Should have notification: "Application ready for final approval"

6. **Navigate to "Loans"** or **"Loan Applications"**

7. **Find application** with status "Approved"
   - Client: John Doe
   - Amount: $5,000
   - All approvals complete

8. **Open the application for final review**

9. **Verify All Details:**
   - ✓ Application amount: $5,000
   - ✓ Interest: $600 (12% simple interest)
   - ✓ Total: $5,600
   - ✓ Monthly: $466.67
   - ✓ Client information complete
   - ✓ KYC documents (3) uploaded
   - ✓ Collateral approved
   - ✓ Branch manager approval present
   - ✓ Loan officer review complete

10. **Approve & Disburse:**
    - Click "Approve & Disburse" button
    - OR:
      - Change status to: **"Active"**
      - Set disbursement date: **Today's date**
      - Add disbursement notes: "Loan approved and funds disbursed"
    - Confirm disbursement
    - Submit

11. **Expected Results:**
    - ✅ Status changed to "Active"
    - ✅ Disbursement date set
    - ✅ Loan number confirmed
    - ✅ Success message: "Loan disbursed successfully!"
    - ✅ **ALL PARTIES RECEIVE NOTIFICATIONS:**
      - Borrower: "🎉 Your loan has been disbursed!"
      - Loan Officer: "Loan successfully disbursed"
      - Branch Manager: "Loan disbursed successfully"
      - Admin: "Disbursement confirmed"

12. **Verify Loan Details:**
    - Click on the loan to view full details
    - Check:
      ```
      Status: Active
      Disbursement Date: Today
      Outstanding Balance: $5,600
      Total Paid: $0
      Next Payment Date: 1 month from today
      Next Payment Amount: $466.67
      Repayment Schedule: 12 payments listed
      ```

13. **Take Screenshot:** Save as `test-4-admin-disburse.png`

---

### 🔹 STEP 5: VERIFY COMPLETE WORKFLOW

**Time: ~5 minutes**

**Go Back to Borrower Tab:**
1. Switch to borrower browser tab
2. Refresh page or click "My Loans"
3. **Verify:**
   - ✅ Loan status is now **"Active"** (not Pending)
   - ✅ Loan details show:
     - Amount: $5,000
     - Interest: $600
     - Total: $5,600
     - Monthly Payment: $466.67
     - Status: Active
     - Disbursement Date: Today
   - ✅ Repayment schedule visible (12 monthly payments)
   - ✅ First payment due date shown (1 month from today)

4. **Check Notifications** (click bell icon):
   - Should have 4 notifications:
     1. "Application submitted" (green)
     2. "Documents added by loan officer" (blue)
     3. "KYC documents verified" (yellow)
     4. "🎉 Loan disbursed!" (green)

5. **Take Screenshot:** Save as `test-5-borrower-final-verify.png`

**Check Loan Officer Tab:**
1. Switch to loan officer tab
2. Refresh page
3. Go to "My Loans"
4. **Verify:**
   - ✅ Loan appears in active loans
   - ✅ Shows in loan officer's portfolio

5. **Check Notifications:**
   - Should have received 3 notifications
   - Last one: "Loan successfully disbursed"

**Check Branch Manager Tab:**
1. Switch to branch manager tab
2. Refresh dashboard
3. **Verify:**
   - ✅ Active loans count increased by 1
   - ✅ Branch portfolio value increased by $5,600
   - ✅ Dashboard metrics updated

4. **Check Notifications:**
   - Should have received disbursement notification

**Check Admin Tab:**
1. Switch to admin tab
2. Refresh dashboard
3. **Verify:**
   - ✅ System-wide metrics updated
   - ✅ Loan in active loans list
   - ✅ Accounting entries may be created (if configured)

---

## 📊 DATABASE VERIFICATION

### After completing workflow, verify database:

```bash
php artisan tinker
```

**Check Loan Created:**
```php
$loan = Loan::latest()->first();
echo "Loan Number: " . $loan->loan_number . "\n";
echo "Status: " . $loan->status . "\n";  // Should be 'active'
echo "Amount: $" . number_format($loan->amount, 2) . "\n";
echo "Interest: $" . number_format($loan->total_interest, 2) . "\n";
echo "Total: $" . number_format($loan->total_amount, 2) . "\n";
echo "Monthly: $" . number_format($loan->monthly_payment, 2) . "\n";
echo "Outstanding: $" . number_format($loan->outstanding_balance, 2) . "\n";
echo "Disbursement Date: " . ($loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : 'Not set') . "\n";
```

**Expected Output:**
```
Loan Number: LN20241027XXXX
Status: active
Amount: $5,000.00
Interest: $600.00
Total: $5,600.00
Monthly: $466.67
Outstanding: $5,600.00
Disbursement Date: 2024-10-27
```

**Check Notifications:**
```php
$notifications = DB::table('notifications')->latest()->take(15)->get();
echo "Total notifications: " . $notifications->count() . "\n";
foreach($notifications as $n) {
    $data = json_decode($n->data, true);
    echo "- " . ($data['action'] ?? 'unknown') . ": " . ($data['message'] ?? '') . "\n";
}
```

**Expected:** 10-15 notifications for the workflow

**Check Activity Log:**
```php
$activities = DB::table('activity_log')->latest()->take(10)->get();
echo "Recent activities:\n";
foreach($activities as $a) {
    echo "- " . $a->description . " by user ID: " . $a->causer_id . "\n";
}
```

**Expected:** Multiple entries for application submission, review, approval, disbursement

---

## ✅ SUCCESS CHECKLIST

### After Testing, You Should Have:

#### Borrower Results:
- [x] Successfully submitted loan application using Livewire form
- [x] Saw live interest calculation (Principal × 12% = Interest)
- [x] Application submitted without page reload
- [x] Received 4 notifications during workflow
- [x] Final loan status: Active
- [x] Can see repayment schedule

#### Loan Officer Results:
- [x] Received instant notification when borrower submitted
- [x] Could access KYC Documents page
- [x] Could add/upload documents
- [x] Could access Collaterals page
- [x] Could add collateral information
- [x] Changed status to Under Review
- [x] All parties notified

#### Branch Manager Results:
- [x] Received notification when LO completed review
- [x] Could view KYC documents
- [x] Could verify collateral
- [x] Approved application
- [x] All parties notified
- [x] Dashboard metrics updated

#### Admin Results:
- [x] Received notification when BM approved
- [x] Could perform final review
- [x] Approved and disbursed loan
- [x] All parties notified
- [x] Loan set to Active status
- [x] Disbursement date recorded

#### System-Wide Results:
- [x] Real-time notifications worked at each step
- [x] No page reloads needed (Livewire)
- [x] Simple interest calculation correct ($5,000 × 12% = $600)
- [x] Workflow progressed: Pending → Under Review → Approved → Active
- [x] Complete audit trail created
- [x] All database records updated correctly

---

## 🐛 TROUBLESHOOTING

### Issue 1: Login Fails
**Symptom:** Invalid credentials error

**Solution:**
```bash
# Re-seed users
php artisan db:seed

# Or create user manually
php artisan tinker
User::create([
    'name' => 'Test Borrower',
    'email' => 'borrower@microfinance.com',
    'password' => Hash::make('borrower123'),
    'role' => 'borrower',
    'branch_id' => 1,
    'email_verified_at' => now(),
    'is_active' => true,
]);
```

### Issue 2: Live Calculation Not Updating
**Symptom:** Calculation panel doesn't update when typing

**Check:**
- Livewire component loaded? (Check browser console)
- JavaScript errors? (F12 developer tools)
- Livewire scripts included in layout?

**Solution:**
```bash
php artisan livewire:publish --assets
php artisan view:clear
```

### Issue 3: Application Submission Fails
**Symptom:** Error when clicking Submit

**Check Console:**
- F12 → Console tab
- Look for errors

**Common Causes:**
- Client record not found for borrower
- Validation errors
- Database constraint violations

**Solution:**
```bash
# Ensure borrower has client record
php artisan tinker
$user = User::where('email', 'borrower@microfinance.com')->first();
Client::firstOrCreate([
    'user_id' => $user->id
], [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'borrower@microfinance.com',
    'phone' => '555-0100',
    'branch_id' => 1,
    'status' => 'active',
]);
```

### Issue 4: Notifications Not Appearing
**Symptom:** Users don't receive notifications

**Check:**
- Queue worker running?
- Notifications table exists?
- Broadcasting configured?

**Solution:**
```bash
# Run queue worker
php artisan queue:work

# Check notifications table
php artisan tinker
DB::table('notifications')->count()
```

### Issue 5: KYC Documents Page Error
**Symptom:** Error accessing /kyc-documents

**Solution:**
```bash
# Verify table exists
php artisan migrate:status

# If table missing, run specific migration
php artisan migrate --path=database/migrations/2025_10_06_003034_create_kyc_documents_table.php
```

### Issue 6: Collaterals Page Error
**Symptom:** Error accessing /collaterals

**Solution:**
```bash
# Run specific migration
php artisan migrate --path=database/migrations/2025_10_06_003023_create_collaterals_table.php
```

---

## 📸 SCREENSHOTS TO COLLECT

During testing, collect these screenshots:

1. **test-1-borrower-submit.png**
   - Borrower loan application form with live calculation
   - Shows $5,000 → $600 interest → $5,600 total

2. **test-2-loan-officer-review.png**
   - Loan officer dashboard with notification
   - Application with Under Review status

3. **test-3-branch-manager-approve.png**
   - Branch manager approval screen
   - KYC documents listed
   - Collateral information shown

4. **test-4-admin-disburse.png**
   - Admin final approval screen
   - Disbursement confirmation

5. **test-5-borrower-final-verify.png**
   - Borrower viewing active loan
   - Repayment schedule visible
   - All notifications received

---

## 📝 TEST REPORT TEMPLATE

### Document Your Results:

```
TEST EXECUTION REPORT
Date: [Date]
Tester: [Your Name]
Environment: Local Development

STEP 1: BORROWER APPLICATION
Status: [PASS/FAIL]
Time Taken: [X minutes]
Issues: [None / List issues]
Screenshot: test-1-borrower-submit.png

STEP 2: LOAN OFFICER REVIEW
Status: [PASS/FAIL]
Time Taken: [X minutes]
Issues: [None / List issues]
Screenshot: test-2-loan-officer-review.png

STEP 3: BRANCH MANAGER APPROVAL
Status: [PASS/FAIL]
Time Taken: [X minutes]
Issues: [None / List issues]
Screenshot: test-3-branch-manager-approve.png

STEP 4: ADMIN DISBURSEMENT
Status: [PASS/FAIL]
Time Taken: [X minutes]
Issues: [None / List issues]
Screenshot: test-4-admin-disburse.png

OVERALL RESULT: [PASS/FAIL]
Total Time: [X minutes]
Real-Time Features: [Working / Not Working]
Notifications: [All Received / Some Missing]
Interest Calculation: [Correct / Incorrect]

NOTES:
[Add any additional observations]
```

---

## 🎯 QUICK TEST (If Time Limited)

### Abbreviated Test (5 minutes):

1. **Login as borrower** → Submit $5,000 loan
2. **Check database:**
   ```bash
   php artisan tinker
   Loan::latest()->first() // Should show pending loan
   ```
3. **Manually update status:**
   ```bash
   $loan = Loan::latest()->first();
   $loan->update(['status' => 'active', 'disbursement_date' => now()]);
   ```
4. **Login as borrower** → Verify loan shows as Active

This skips the full workflow but tests core functionality.

---

## 🎉 WHAT A SUCCESSFUL TEST LOOKS LIKE

### Complete Success:
```
✅ Borrower can submit application with live calculation
✅ Form submission works without page reload  
✅ Loan Officer receives instant notification
✅ Loan Officer can add KYC & collateral
✅ Branch Manager receives instant notification
✅ Branch Manager can verify and approve
✅ Admin receives instant notification
✅ Admin can approve and disburse
✅ ALL parties receive disbursement notification
✅ Loan status: Pending → Under Review → Approved → Active
✅ Interest calculated correctly: $5,000 × 12% = $600
✅ Complete audit trail exists
✅ No errors in console
✅ Performance is acceptable (< 5s per action)
```

---

## 🚀 NEXT STEPS AFTER SUCCESSFUL TEST

1. **Document any issues** encountered
2. **Report bugs** for fixing
3. **Test edge cases:**
   - Large loan amounts
   - Different interest rates
   - Different terms
   - Multiple simultaneous applications
   - Rejection workflow
4. **User acceptance testing** with real users
5. **Performance testing** with larger datasets
6. **Security testing** for vulnerabilities
7. **Mobile testing** on phones/tablets

---

## ✨ CONGRATULATIONS!

If you've completed all steps successfully, you have a fully functional real-time loan management system with:

- ✅ Live loan applications
- ✅ Simple interest calculation
- ✅ Multi-level approval workflow
- ✅ Real-time notifications
- ✅ Complete audit trail
- ✅ Role-based access control
- ✅ Clean, professional UI

**The system is ready for production use!** 🎊

---

**Testing Guide Created:** October 27, 2024  
**Status:** Ready for Manual Testing  
**Estimated Test Time:** 15-30 minutes  
**Difficulty:** Easy (step-by-step guide provided)

