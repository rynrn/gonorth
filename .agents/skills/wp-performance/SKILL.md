---
name: wp-performance
description: Audits and improves the performance of gonorth.co.il — including page load speed, caching, image optimization, database cleanup, and server-side tuning. Connects via SSH to inspect the server, apply caching rules, optimize images, and run WP-CLI commands. Use when the site feels slow, before/after launch, or when Google PageSpeed score needs improvement.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: performance
  triggers: performance, speed, slow, caching, PageSpeed, Core Web Vitals, images, optimization, load time, LCP, CLS, FID, database cleanup, minify
  role: expert
  scope: audit + optimization
  output-format: report + commands
---

# WP Performance — gonorth.co.il

Performance optimizer for a WordPress tourism directory. Tourism sites are image-heavy, so the focus is on fast image loading, efficient caching, and a smooth mobile experience.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
```

## Performance Targets

| Metric | Target |
|--------|--------|
| Google PageSpeed (mobile) | > 80 |
| Google PageSpeed (desktop) | > 90 |
| LCP (Largest Contentful Paint) | < 2.5s |
| CLS (Cumulative Layout Shift) | < 0.1 |
| FID / INP | < 200ms |
| Homepage load time | < 3s |
| Time to First Byte (TTFB) | < 600ms |

## Full Performance Audit

### 1. Current State Check
```bash
# Check active caching plugins
ssh gonorth "wp plugin list --status=active --path=/var/www/gonorth --allow-root | grep -i cache"

# Check PHP version (8.1+ recommended)
ssh gonorth "php -v"

# Check WordPress cron (disable if using server cron)
ssh gonorth "wp option get cron_schedules --path=/var/www/gonorth --allow-root"

# Check autoloaded options (should be < 1MB)
ssh gonorth "wp db query 'SELECT SUM(LENGTH(option_value)) as autoload_size FROM wp_options WHERE autoload=\"yes\"' --path=/var/www/gonorth --allow-root"
```

### 2. Caching Setup (WP Super Cache or W3 Total Cache)
```bash
# Install and activate WP Super Cache
ssh gonorth "wp plugin install wp-super-cache --activate --path=/var/www/gonorth --allow-root"

# Enable caching
ssh gonorth "wp super-cache enable --path=/var/www/gonorth --allow-root"

# Flush cache after changes
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
ssh gonorth "wp super-cache flush --path=/var/www/gonorth --allow-root"
```

### 3. Image Optimization
```bash
# Check total uploads size
ssh gonorth "du -sh /var/www/gonorth/wp-content/uploads/"

# Install Smush or ShortPixel for auto-optimization
ssh gonorth "wp plugin install wp-smushit --activate --path=/var/www/gonorth --allow-root"

# Bulk smush existing images via WP-CLI
ssh gonorth "wp smush scan --path=/var/www/gonorth --allow-root"
```

Essential image rules:
```php
// Add to functions.php — force WebP support
add_filter('wp_image_editors', function($editors) {
    array_unshift($editors, 'WP_Image_Editor_Imagick');
    return $editors;
});

// Lazy load all images (WP 5.5+ — already built in)
// Ensure NOT disabled anywhere in theme
```

### 4. Database Cleanup
```bash
# Clean post revisions (keep only 5)
ssh gonorth "wp post delete $(wp post list --post_type=revision --format=ids --path=/var/www/gonorth --allow-root) --force --path=/var/www/gonorth --allow-root"

# Delete spam and trashed comments
ssh gonorth "wp comment delete $(wp comment list --status=spam --format=ids --path=/var/www/gonorth --allow-root) --force --path=/var/www/gonorth --allow-root"

# Delete transients
ssh gonorth "wp transient delete --expired --path=/var/www/gonorth --allow-root"

# Optimize database tables
ssh gonorth "wp db optimize --path=/var/www/gonorth --allow-root"

# Repair if needed
ssh gonorth "wp db repair --path=/var/www/gonorth --allow-root"
```

### 5. Asset Minification (via .htaccess / Autoptimize)
```bash
# Install Autoptimize
ssh gonorth "wp plugin install autoptimize --activate --path=/var/www/gonorth --allow-root"
```

```apache
# Add to .htaccess — enable Gzip compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml
  AddOutputFilterByType DEFLATE text/css application/javascript
  AddOutputFilterByType DEFLATE application/json application/xml
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 6. GeoDirectory Map Performance
```bash
# Load maps only on pages that need them (lazy init)
# Add to theme functions.php:
```
```php
// Defer Google Maps until user interaction
add_filter('geodir_google_maps_script_args', function($args) {
    $args['loading'] = 'lazy';
    return $args;
});
```

## Performance Report Template

```
=== gonorth.co.il Performance Report ===
Date: {date}

PageSpeed Mobile:  {score}/100
PageSpeed Desktop: {score}/100
LCP:  {time}s  ✅/❌ (target <2.5s)
CLS:  {score}  ✅/❌ (target <0.1)
TTFB: {time}ms ✅/❌ (target <600ms)

Caching active:        ✅/❌
Images optimized:      ✅/❌
Gzip enabled:          ✅/❌
Browser caching set:   ✅/❌
DB cleaned:            ✅/❌
Unused plugins:        {count} found

TOP 3 ISSUES:
1. {issue}
2. {issue}
3. {issue}

RECOMMENDED NEXT STEPS:
- {action}
```

## Constraints

### MUST DO
- Run database backup before cleanup operations
- Test site after applying caching — check for stale content
- Always flush cache after any theme or plugin change
- Prioritize mobile performance (Israeli mobile usage is very high)

### MUST NOT DO
- Delete database entries without confirming backup exists
- Minify files without testing — can break JS/CSS
- Disable image lazy-loading (hurts mobile LCP)
- Cache pages for logged-in users (breaks admin experience)
