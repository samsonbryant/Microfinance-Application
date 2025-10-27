# Fly.io Deployment Guide - Microfinance Application

## Complete Guide to Deploy on Fly.io

### Date: October 27, 2024
### Application: Microbook-G5 Microfinance Management System

---

## 📋 PREREQUISITES

### 1. Install Fly CLI
**Windows (PowerShell as Administrator):**
```powershell
iwr https://fly.io/install.ps1 -useb | iex
```

**Verify Installation:**
```bash
fly version
```

### 2. Create Fly.io Account
```bash
fly auth signup
# OR if you have an account
fly auth login
```

### 3. Prepare Application
```bash
cd C:\Users\DELL\LoanManagementSystem\microfinance-laravel
```

---

## 🚀 DEPLOYMENT STEPS

### STEP 1: Initialize Fly.io App

```bash
# Create new Fly.io application
fly apps create microfinance-laravel

# OR use the existing fly.toml configuration
fly launch --no-deploy
```

**Follow prompts:**
- App name: `microfinance-laravel` (or your choice)
- Region: `iad` (US East) or closest to you
- Database: Skip (we're using SQLite)
- Deploy now: No (we'll do it manually)

---

### STEP 2: Set Environment Variables

**Set Application Key:**
```bash
# Generate new app key
php artisan key:generate --show

# Copy the key (starts with base64:...)
# Then set it in Fly.io:
fly secrets set APP_KEY="base64:your-generated-key-here"
```

**Set Other Secrets:**
```bash
# Required secrets
fly secrets set APP_ENV=production
fly secrets set APP_DEBUG=false
fly secrets set APP_URL=https://microfinance-laravel.fly.dev

# Database (SQLite - already configured in fly.toml)
fly secrets set DB_CONNECTION=sqlite
fly secrets set DB_DATABASE=/var/www/html/database/database.sqlite

# Session
fly secrets set SESSION_DRIVER=database

# Queue
fly secrets set QUEUE_CONNECTION=database

# Optional: If you want email notifications
fly secrets set MAIL_MAILER=smtp
fly secrets set MAIL_HOST=smtp.mailtrap.io
fly secrets set MAIL_PORT=2525
fly secrets set MAIL_USERNAME=your-username
fly secrets set MAIL_PASSWORD=your-password
```

---

### STEP 3: Create Volume for Database (SQLite)

**Create persistent volume:**
```bash
# Create volume for SQLite database
fly volumes create microfinance_data --region iad --size 1

# Update fly.toml to mount volume (already configured)
```

**Note:** The fly.toml is already configured, but if you need to modify:
```toml
[[mounts]]
  source = "microfinance_data"
  destination = "/var/www/html/database"
```

---

### STEP 4: Deploy Application

**First Deployment:**
```bash
# Deploy to Fly.io
fly deploy

# This will:
# 1. Build Docker image
# 2. Push to Fly.io registry
# 3. Create machine
# 4. Start application
# 5. Run migrations
# 6. Seed database
```

**Monitor Deployment:**
```bash
# Watch logs during deployment
fly logs

# Check deployment status
fly status
```

---

### STEP 5: Verify Deployment

**Check Application:**
```bash
# Open in browser
fly open

# Check health
fly checks list

# View logs
fly logs
```

**Test Endpoints:**
```
https://microfinance-laravel.fly.dev/health
https://microfinance-laravel.fly.dev/login
```

---

### STEP 6: Post-Deployment Setup

**Run Migrations (if needed):**
```bash
fly ssh console
cd /var/www/html
php artisan migrate --force
php artisan db:seed --force
exit
```

**Clear Cache:**
```bash
fly ssh console
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
exit
```

---

## 🔒 SECURITY CONFIGURATION

### Set Production Environment Variables:

```bash
# Application Security
fly secrets set APP_KEY="base64:$(php artisan key:generate --show | cut -d':' -f2)"
fly secrets set APP_DEBUG=false
fly secrets set APP_ENV=production

# Database Encryption (optional)
fly secrets set DB_ENCRYPT=true

# Session Security
fly secrets set SESSION_SECURE_COOKIE=true
fly secrets set SESSION_HTTP_ONLY=true
fly secrets set SESSION_SAME_SITE=lax

# Additional Security
fly secrets set SANCTUM_STATEFUL_DOMAINS=microfinance-laravel.fly.dev
```

---

## 📊 SCALING CONFIGURATION

### Adjust Resources (if needed):

**Increase Memory:**
```bash
# For better performance, increase RAM
fly scale memory 2048  # 2GB RAM
```

**Add More Instances:**
```bash
# Scale to multiple instances
fly scale count 2

# Scale in specific regions
fly scale count 2 --region iad,lhr
```

**Auto-scaling:**
```toml
# Add to fly.toml
[[services.auto_scaling]]
  min = 1
  max = 3
```

---

## 🗄️ DATABASE MANAGEMENT

### Backup Database:

**Create Backup:**
```bash
# SSH into machine
fly ssh console

# Create backup
cd /var/www/html/database
cp database.sqlite backup-$(date +%Y%m%d).sqlite

# Download backup
exit
fly ssh sftp get /var/www/html/database/backup-*.sqlite ./backups/
```

### Restore Database:

**Upload and Restore:**
```bash
# Upload backup
fly ssh sftp shell
put local-backup.sqlite /var/www/html/database/database.sqlite
exit

# Restart app
fly apps restart microfinance-laravel
```

---

## 📧 CONFIGURE NOTIFICATIONS

### Email Notifications:

**Using Mailtrap (Development):**
```bash
fly secrets set MAIL_MAILER=smtp
fly secrets set MAIL_HOST=sandbox.smtp.mailtrap.io
fly secrets set MAIL_PORT=2525
fly secrets set MAIL_USERNAME=your-mailtrap-username
fly secrets set MAIL_PASSWORD=your-mailtrap-password
```

**Using Gmail:**
```bash
fly secrets set MAIL_MAILER=smtp
fly secrets set MAIL_HOST=smtp.gmail.com
fly secrets set MAIL_PORT=587
fly secrets set MAIL_USERNAME=your-email@gmail.com
fly secrets set MAIL_PASSWORD=your-app-password
fly secrets set MAIL_ENCRYPTION=tls
```

**Using SendGrid:**
```bash
fly secrets set MAIL_MAILER=smtp
fly secrets set MAIL_HOST=smtp.sendgrid.net
fly secrets set MAIL_PORT=587
fly secrets set MAIL_USERNAME=apikey
fly secrets set MAIL_PASSWORD=your-sendgrid-api-key
```

---

## 🔍 MONITORING & DEBUGGING

### View Logs:

**Real-time logs:**
```bash
fly logs
```

**Filtered logs:**
```bash
# Errors only
fly logs --level error

# Specific service
fly logs --service app
```

### SSH into Application:

```bash
# Access server
fly ssh console

# Check processes
ps aux | grep php

# Check disk space
df -h

# Check database
sqlite3 /var/www/html/database/database.sqlite "SELECT COUNT(*) FROM users;"

# Exit
exit
```

### Performance Monitoring:

```bash
# Check resource usage
fly status

# View metrics
fly dashboard microfinance-laravel
```

---

## 🛠️ TROUBLESHOOTING

### Issue 1: Deployment Fails

**Check Logs:**
```bash
fly logs
```

**Common Causes:**
- Missing APP_KEY
- Invalid Dockerfile
- Insufficient memory

**Solution:**
```bash
# Ensure APP_KEY is set
fly secrets list | grep APP_KEY

# Increase memory if needed
fly scale memory 1024
```

### Issue 2: Database Not Persisting

**Check Volume:**
```bash
fly volumes list
```

**Solution:**
```bash
# Ensure volume is mounted
fly volumes create microfinance_data --region iad --size 1
# Redeploy
fly deploy
```

### Issue 3: Application Crashes

**Check Status:**
```bash
fly status
fly checks list
```

**View Crash Logs:**
```bash
fly logs --level error
```

**Restart Application:**
```bash
fly apps restart microfinance-laravel
```

### Issue 4: SSL/HTTPS Issues

**Fly.io Auto-SSL:**
```bash
# Check certificates
fly certs list

# Add custom domain (if needed)
fly certs add your-custom-domain.com
```

---

## 🌐 CUSTOM DOMAIN SETUP

### Add Your Domain:

**Step 1: Add Certificate:**
```bash
fly certs add microfinance.yourdomain.com
```

**Step 2: Configure DNS:**
Add these records to your DNS:
```
Type: CNAME
Name: microfinance
Value: microfinance-laravel.fly.dev
```

**Step 3: Verify:**
```bash
fly certs show microfinance.yourdomain.com
```

**Step 4: Update Environment:**
```bash
fly secrets set APP_URL=https://microfinance.yourdomain.com
```

---

## 🔄 UPDATING THE APPLICATION

### Deploy Updates:

**After making changes:**
```bash
# Commit changes to Git
git add .
git commit -m "Your update message"
git push origin main

# Deploy to Fly.io
fly deploy

# Monitor deployment
fly logs
```

**Zero-Downtime Deployment:**
```bash
# Fly.io handles this automatically
# Old instances stay up until new ones are healthy
fly deploy --strategy rolling
```

---

## 💰 COST ESTIMATION

### Fly.io Pricing (as of 2024):

**Free Tier Includes:**
- Up to 3 shared-cpu-1x VMs
- 3GB total RAM
- 160GB bandwidth/month

**Our Configuration:**
- 1 VM with 1GB RAM
- SQLite database (no separate DB cost)
- Minimal bandwidth usage

**Estimated Cost:**
- Development/Testing: **$0/month** (within free tier)
- Production (small): **$0-5/month**
- Production (scaled): **$10-20/month**

---

## 📈 PERFORMANCE OPTIMIZATION

### Production Optimizations:

**1. Enable OPcache:**
Already configured in Dockerfile

**2. Cache Configuration:**
```bash
fly ssh console
php artisan config:cache
php artisan route:cache
php artisan view:cache
exit
```

**3. Queue Workers:**
Already configured in Supervisor

**4. Database Optimization:**
```bash
# Run in production
fly ssh console
php artisan db:optimize
exit
```

---

## 🎯 POST-DEPLOYMENT CHECKLIST

### After Successful Deployment:

**Verify Functionality:**
- [ ] Access application URL
- [ ] Test login (all roles)
- [ ] Test borrower loan application
- [ ] Test payment processing
- [ ] Test accounting modules
- [ ] Verify real-time features work
- [ ] Check notifications
- [ ] Test mobile responsiveness

**Security Checks:**
- [ ] HTTPS working
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Secrets properly set
- [ ] No sensitive data in logs

**Performance Checks:**
- [ ] Page load times acceptable (< 2s)
- [ ] Database queries optimized
- [ ] Assets loading correctly
- [ ] Livewire components working

---

## 🔐 PRODUCTION LOGIN CREDENTIALS

**After deployment, use these credentials:**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@microfinance.com | admin123 |
| Branch Manager | bm@microfinance.com | bm123 |
| Loan Officer | lo@microfinance.com | lo123 |
| Borrower | borrower@microfinance.com | borrower123 |

**⚠️ IMPORTANT:** Change these passwords immediately after first login!

---

## 📝 DEPLOYMENT COMMANDS SUMMARY

```bash
# 1. Install Fly CLI
iwr https://fly.io/install.ps1 -useb | iex

# 2. Login to Fly.io
fly auth login

# 3. Create app
fly apps create microfinance-laravel

# 4. Set secrets
fly secrets set APP_KEY="base64:your-key"
fly secrets set APP_ENV=production
fly secrets set APP_DEBUG=false

# 5. Create volume
fly volumes create microfinance_data --region iad --size 1

# 6. Deploy
fly deploy

# 7. Open app
fly open

# 8. Monitor
fly logs
```

---

## 🎊 SUCCESS CRITERIA

### Deployment is Successful When:

✅ Application accessible at https://microfinance-laravel.fly.dev
✅ Health check returns 200 OK
✅ Login page loads
✅ All users can login
✅ Dashboards load correctly
✅ Loan application works
✅ Payment processing functions
✅ Real-time features operational
✅ No errors in logs
✅ Database persists data
✅ SSL certificate active

---

## 🌟 ADDITIONAL RESOURCES

### Fly.io Documentation:
- https://fly.io/docs/languages-and-frameworks/laravel/
- https://fly.io/docs/reference/configuration/

### Monitoring:
- Fly.io Dashboard: https://fly.io/dashboard
- Application Logs: `fly logs`
- Metrics: `fly dashboard microfinance-laravel`

### Support:
- Fly.io Community: https://community.fly.io
- Laravel Documentation: https://laravel.com/docs/11.x

---

## 🎯 NEXT STEPS AFTER DEPLOYMENT

1. **Test Complete Workflow:**
   - Submit loan as borrower
   - Review as loan officer
   - Approve as branch manager
   - Disburse as admin

2. **Monitor Performance:**
   - Check page load times
   - Monitor resource usage
   - Review logs for errors

3. **User Acceptance Testing:**
   - Share URL with stakeholders
   - Collect feedback
   - Make improvements

4. **Configure Custom Domain (Optional):**
   - Set up your domain
   - Add SSL certificate
   - Update APP_URL

5. **Set Up Backups:**
   - Schedule database backups
   - Store backups securely
   - Test restoration process

---

## ✨ CONGRATULATIONS!

Once deployed, you'll have a **live, production-ready Microfinance Management System** accessible from anywhere in the world!

**Features Live on Fly.io:**
- ✅ Real-time loan application (Livewire)
- ✅ Simple interest calculation
- ✅ Complete workflow with notifications
- ✅ Branch manager payment processing
- ✅ Admin accounting system (9 modules)
- ✅ Role-based access control
- ✅ Auto-refreshing dashboards
- ✅ Mobile responsive
- ✅ Secure (HTTPS)

**Access From Anywhere:**
```
https://microfinance-laravel.fly.dev
```

---

**Deployment Guide Created:** October 27, 2024  
**Status:** Ready for Deployment  
**Estimated Deployment Time:** 20-30 minutes  
**Difficulty:** Easy (follow steps)

