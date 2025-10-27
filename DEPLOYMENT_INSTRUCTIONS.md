# 🚀 DEPLOY TO FLY.IO - Step-by-Step Instructions

## Ready to Deploy? Follow These Exact Steps!

### Time Required: 20-30 minutes
### Prerequisites: PowerShell/Terminal access

---

## 🎯 QUICK START (Copy & Paste Commands)

### STEP 1: Install Fly CLI (If not installed)

**Open PowerShell as Administrator:**
```powershell
iwr https://fly.io/install.ps1 -useb | iex
```

**Verify Installation:**
```bash
fly version
```

**Expected Output:** `flyctl v0.x.x ...`

---

### STEP 2: Login to Fly.io

```bash
# If you have an account
fly auth login

# If you need to create an account
fly auth signup
```

**This will open a browser - complete the signup/login process**

---

### STEP 3: Navigate to Project

```bash
cd C:\Users\DELL\LoanManagementSystem\microfinance-laravel
```

---

### STEP 4: Generate Application Key

```bash
# Generate key
php artisan key:generate --show
```

**Copy the entire output** (it will look like: `base64:xxxxxxxxxxxxx`)

---

### STEP 5: Deploy to Fly.io

```bash
# Launch application (follow prompts)
fly launch --no-deploy

# When prompted:
# - App name: Press Enter (use auto-generated) or type: microfinance-app
# - Region: Select closest (e.g., iad for US East)
# - Database: No (we're using SQLite)
# - Deploy now: No
```

---

### STEP 6: Set Environment Secrets

**Replace `YOUR_APP_KEY_HERE` with the key you generated in Step 4:**

```bash
fly secrets set APP_KEY="YOUR_APP_KEY_HERE"
fly secrets set APP_ENV=production
fly secrets set APP_DEBUG=false
fly secrets set DB_CONNECTION=sqlite
fly secrets set SESSION_DRIVER=database
fly secrets set QUEUE_CONNECTION=database
```

**Example:**
```bash
fly secrets set APP_KEY="base64:abc123xyz456=="
```

---

### STEP 7: Create Persistent Volume for Database

```bash
fly volumes create microfinance_data --size 1
```

**Add volume mount to fly.toml:**
```toml
[[mounts]]
  source = "microfinance_data"
  destination = "/var/www/html/database"
```

---

### STEP 8: Deploy!

```bash
# Deploy application
fly deploy

# This will take 3-5 minutes
# Watch the progress in terminal
```

---

### STEP 9: Verify Deployment

```bash
# Check status
fly status

# View logs
fly logs

# Open in browser
fly open
```

---

## ✅ VERIFICATION CHECKLIST

### After Deployment, Verify:

**1. Application is Running:**
```bash
fly status
# Should show: Status = running
```

**2. Health Check Passes:**
```bash
curl https://your-app-name.fly.dev/health
# Should return: {"status":"healthy",...}
```

**3. Login Page Loads:**
```
Visit: https://your-app-name.fly.dev/login
# Should see login page
```

**4. Can Login:**
- Email: `admin@microfinance.com`
- Password: `admin123`
- Should redirect to admin dashboard

**5. All Features Work:**
- [ ] Dashboards load
- [ ] Sidebar navigation works
- [ ] Accounting modules accessible
- [ ] Loan application works
- [ ] Livewire components function
- [ ] Real-time features active

---

## 🐛 TROUBLESHOOTING

### Problem: fly command not found
**Solution:**
```bash
# Restart PowerShell/Terminal
# Or manually add to PATH:
# C:\Users\<Username>\.fly\bin
```

### Problem: Deployment fails
**Solution:**
```bash
# Check logs
fly logs

# Common fixes:
fly secrets set APP_KEY="$(php artisan key:generate --show)"
fly apps restart your-app-name
```

### Problem: Database not persisting
**Solution:**
```bash
# Ensure volume is created and mounted
fly volumes list
fly volumes create microfinance_data --size 1
```

### Problem: 500 errors after deployment
**Solution:**
```bash
# Check logs
fly logs --level error

# Clear cache
fly ssh console
php artisan config:clear
php artisan cache:clear
exit
```

---

## 🎯 QUICK DEPLOYMENT (All Commands)

**Copy and paste these in order:**

```bash
# 1. Navigate to project
cd C:\Users\DELL\LoanManagementSystem\microfinance-laravel

# 2. Generate and copy APP_KEY
php artisan key:generate --show

# 3. Launch app
fly launch --no-deploy

# 4. Set secrets (replace with your actual key)
fly secrets set APP_KEY="base64:YOUR_KEY_HERE"
fly secrets set APP_ENV=production
fly secrets set APP_DEBUG=false

# 5. Create volume
fly volumes create microfinance_data --size 1

# 6. Deploy
fly deploy

# 7. Open app
fly open
```

**That's it! Your app is live!** 🎉

---

## 🌐 AFTER DEPLOYMENT

### Your Application Will Be Available At:
```
https://[your-app-name].fly.dev
```

### Share With Users:
✅ Admin: https://[your-app-name].fly.dev/admin/dashboard
✅ Branch Manager: https://[your-app-name].fly.dev/branch-manager/dashboard
✅ Loan Officer: https://[your-app-name].fly.dev/loan-officer/dashboard
✅ Borrower: https://[your-app-name].fly.dev/borrower/dashboard

---

## 💡 PRO TIPS

1. **Monitor your app:**
   ```bash
   fly logs  # Real-time logs
   fly dashboard  # Web dashboard
   ```

2. **Update your app:**
   ```bash
   git add .
   git commit -m "Updates"
   git push origin main
   fly deploy  # Deploy changes
   ```

3. **Scale if needed:**
   ```bash
   fly scale memory 2048  # Increase to 2GB RAM
   fly scale count 2  # Run 2 instances
   ```

4. **Add custom domain:**
   ```bash
   fly certs add your-domain.com
   # Then add CNAME record in your DNS
   ```

---

## 🎊 YOU'RE READY TO DEPLOY!

**Everything is configured and ready.**

**Just run these 3 commands to go live:**

```bash
1. fly auth login
2. fly launch --no-deploy
3. fly deploy
```

**Your Microfinance Application will be live on the internet!** 🚀

---

**Deployment Ready:** October 27, 2024  
**Configuration:** Complete  
**Documentation:** Comprehensive  
**Status:** ✅ READY TO DEPLOY NOW

