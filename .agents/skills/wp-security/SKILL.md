---
name: wp-security
description: Audits, hardens, and monitors security for gonorth.co.il WordPress site. Checks for vulnerabilities, outdated plugins, weak configurations, file permissions, and brute force exposure. Applies hardening via SSH and WP-CLI. Use when running a security audit, after a plugin update, before launch, or when suspicious activity is detected.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: security
  triggers: security, vulnerability, hack, malware, audit, permissions, firewall, brute force, login, hardening, plugin update, Wordfence, SSL, .htaccess
  role: expert
  scope: audit + hardening
  output-format: report + commands
---

# WP Security — gonorth.co.il

WordPress security auditor and hardener. Runs checks, identifies risks, and applies fixes directly via SSH and WP-CLI.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
```

## Full Security Audit Checklist

Run these in order when doing a full audit:

### 1. WordPress Core & Versions
```bash
# Check WP version
ssh gonorth "wp core version --path=/var/www/gonorth --allow-root"

# Check if WP core has updates
ssh gonorth "wp core check-update --path=/var/www/gonorth --allow-root"

# Update WP core if needed
ssh gonorth "wp core update --path=/var/www/gonorth --allow-root"
```

### 2. Plugin Audit
```bash
# List all plugins with versions
ssh gonorth "wp plugin list --path=/var/www/gonorth --allow-root"

# Check for plugin updates
ssh gonorth "wp plugin update --all --dry-run --path=/var/www/gonorth --allow-root"

# List inactive plugins (remove unused ones)
ssh gonorth "wp plugin list --status=inactive --path=/var/www/gonorth --allow-root"

# Update all plugins
ssh gonorth "wp plugin update --all --path=/var/www/gonorth --allow-root"
```

### 3. User Security
```bash
# List all admin users
ssh gonorth "wp user list --role=administrator --path=/var/www/gonorth --allow-root"

# Check for default 'admin' username (must not exist)
ssh gonorth "wp user get admin --path=/var/www/gonorth --allow-root 2>&1 || echo 'OK: no admin user'"

# Force strong password for user
ssh gonorth "wp user update {user_id} --user_pass='{strong_password}' --path=/var/www/gonorth --allow-root"
```

### 4. File Permissions
```bash
# Check wp-config.php permissions (should be 600 or 640)
ssh gonorth "stat -c '%a %n' /var/www/gonorth/wp-config.php"

# Fix permissions
ssh gonorth "chmod 640 /var/www/gonorth/wp-config.php"
ssh gonorth "find /var/www/gonorth -type d -exec chmod 755 {} \;"
ssh gonorth "find /var/www/gonorth -type f -exec chmod 644 {} \;"

# Lock down wp-config
ssh gonorth "chmod 600 /var/www/gonorth/wp-config.php"
```

### 5. .htaccess Hardening
```bash
# Read current .htaccess
ssh gonorth "cat /var/www/gonorth/.htaccess"
```

Essential .htaccess security rules:
```apache
# Protect wp-config.php
<Files wp-config.php>
  order allow,deny
  deny from all
</Files>

# Disable directory browsing
Options -Indexes

# Protect .htaccess itself
<Files .htaccess>
  order allow,deny
  deny from all
</Files>

# Block access to sensitive files
<FilesMatch "\.(sql|log|sh|bak|config|dist|fla|psd|ini|log|sh|inc|swp|dist)$">
  Order allow,deny
  Deny from all
</FilesMatch>

# Limit XML-RPC (disable if not needed)
<Files xmlrpc.php>
  order deny,allow
  deny from all
</Files>
```

### 6. wp-config.php Hardening
```bash
ssh gonorth "cat /var/www/gonorth/wp-config.php | grep -E 'DEBUG|DISALLOW|AUTH_KEY|SECURE_AUTH'"
```

Ensure these are set:
```php
// Disable file editor in admin
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true); // prevents plugin/theme install from admin

// Disable debug in production
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Force SSL for admin
define('FORCE_SSL_ADMIN', true);

// Limit post revisions
define('WP_POST_REVISIONS', 5);

// Auto-update minor releases
define('WP_AUTO_UPDATE_CORE', 'minor');
```

### 7. Malware Scan
```bash
# Check for suspicious PHP in uploads (common attack vector)
ssh gonorth "find /var/www/gonorth/wp-content/uploads -name '*.php' -type f"

# Check for recently modified files (last 7 days)
ssh gonorth "find /var/www/gonorth -name '*.php' -mtime -7 -not -path '*/cache/*'"

# Verify WordPress file integrity
ssh gonorth "wp core verify-checksums --path=/var/www/gonorth --allow-root"

# Verify plugin checksums
ssh gonorth "wp plugin verify-checksums --all --path=/var/www/gonorth --allow-root"
```

### 8. SSL Check
```bash
ssh gonorth "curl -s -o /dev/null -w '%{http_code}' https://gonorth.co.il"
# Should return 200

# Check cert expiry
ssh gonorth "echo | openssl s_client -connect gonorth.co.il:443 2>/dev/null | openssl x509 -noout -dates"
```

## Security Score Report Template

```
=== gonorth.co.il Security Report ===
Date: {date}

✅ / ❌ WordPress core up to date
✅ / ❌ All plugins up to date
✅ / ❌ No inactive plugins present
✅ / ❌ No 'admin' username exists
✅ / ❌ wp-config.php permissions correct (600)
✅ / ❌ File editor disabled
✅ / ❌ Debug mode off
✅ / ❌ SSL active and valid
✅ / ❌ .htaccess hardened
✅ / ❌ No PHP files in uploads folder
✅ / ❌ Core file checksums pass
✅ / ❌ XML-RPC restricted

RISK LEVEL: LOW / MEDIUM / HIGH
RECOMMENDED ACTIONS: [list]
```

## Constraints

### MUST DO
- Always run in read/audit mode first, then apply fixes
- Report findings before making any changes
- Back up before applying hardening rules
- Check SSL certificate expiry monthly

### MUST NOT DO
- Delete plugins without user confirmation
- Modify wp-config.php without showing the user the diff first
- Block admin IP by mistake via .htaccess
- Enable WP_DEBUG on a live production site
