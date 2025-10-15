# Security Guide - Life Atlas Organizer

## 🔒 Security Overview

Life Atlas Organizer implements multiple layers of security to protect user data and prevent common web vulnerabilities.

## ✅ Implemented Security Features

### 1. Authentication & Session Security

**Password Security:**
- ✅ Bcrypt password hashing (cost factor: 12)
- ✅ Minimum password requirements enforced
- ✅ Secure password reset flow
- ✅ Session-based authentication

**Session Management:**
```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);     // Prevent JavaScript access
ini_set('session.cookie_secure', 1);       // HTTPS only
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
ini_set('session.use_strict_mode', 1);     // Prevent session fixation
ini_set('session.gc_maxlifetime', 86400);  // 24 hour timeout
```

### 2. SQL Injection Prevention

**Prepared Statements:**
```php
// Always use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ✅ GOOD
$users = $db->fetchAll("SELECT * FROM users WHERE id = ?", [$userId]);

// ❌ NEVER DO THIS
$users = $db->query("SELECT * FROM users WHERE id = " . $_GET['id']);
```

**All database queries use:**
- PDO prepared statements
- Parameter binding
- No string concatenation

### 3. Cross-Site Scripting (XSS) Prevention

**Output Escaping:**
```php
// Sanitize all user input before output
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Usage in templates
<h1><?php echo sanitize($user['name']); ?></h1>
```

**Content Security:**
- All user input escaped before display
- HTML special characters encoded
- UTF-8 encoding enforced

### 4. Cross-Site Request Forgery (CSRF) Protection

**Token Implementation:**
```php
// Generate token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

// Validate token
function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    
    // Check expiry (1 hour)
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        return false;
    }
    
    return true;
}
```

**Implementation:**
- Token required for all POST requests
- Auto-refresh on expiry
- Per-session tokens

### 5. File Upload Security

**Validation:**
```php
// Allowed file types
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Validate file
if (!in_array($_FILES['upload']['type'], $allowedTypes)) {
    throw new Exception('Invalid file type');
}

// Limit file size (20MB)
if ($_FILES['upload']['size'] > 20 * 1024 * 1024) {
    throw new Exception('File too large');
}

// Generate unique filename
$filename = bin2hex(random_bytes(16)) . '_' . basename($_FILES['upload']['name']);
```

**Protection Measures:**
- File type validation
- Size limits enforced
- Unique filename generation
- Files stored outside web root (recommended)

### 6. API Security

**Authentication:**
```php
// All API endpoints check authentication
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
```

**Rate Limiting:**
```php
// Future implementation
function checkRateLimit($userId, $action, $limit = 100, $window = 3600) {
    // Check if user exceeded rate limit
    // $limit requests per $window seconds
}
```

### 7. Configuration Security

**Protected Files:**
```apache
# .htaccess in includes/
Order deny,allow
Deny from all

# .htaccess in cron/
Order deny,allow
Deny from all
```

**Environment Variables:**
```php
// Use environment variables for sensitive data
define('DB_PASS', getenv('DB_PASSWORD') ?: 'fallback');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_TOKEN') ?: '');
```

### 8. HTTPS Enforcement

**Force HTTPS:**
```apache
# .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Security Headers:**
```apache
<IfModule mod_headers.c>
    # Prevent MIME sniffing
    Header set X-Content-Type-Options "nosniff"
    
    # Clickjacking protection
    Header set X-Frame-Options "SAMEORIGIN"
    
    # XSS protection
    Header set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Permissions policy
    Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    
    # HSTS (after HTTPS is working)
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

## 🔍 Security Checklist

### Pre-Production Checklist

**Configuration:**
- [ ] HTTPS enabled and enforced
- [ ] Error display disabled (`display_errors = 0`)
- [ ] Debug mode disabled
- [ ] Strong database passwords
- [ ] Secure session configuration
- [ ] CSRF protection enabled

**File Security:**
- [ ] Config files protected (`.htaccess`)
- [ ] Sensitive directories blocked
- [ ] File permissions set correctly (755/644)
- [ ] Uploads directory secured
- [ ] `.git` folder removed/blocked

**Database Security:**
- [ ] Strong passwords used
- [ ] Minimal privileges granted
- [ ] Remote access restricted
- [ ] Regular backups configured
- [ ] SQL injection tests passed

**Application Security:**
- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] Prepared statements used everywhere
- [ ] CSRF tokens on all forms
- [ ] Password hashing verified

**API Security:**
- [ ] Authentication required
- [ ] Rate limiting implemented (future)
- [ ] Input validation on all endpoints
- [ ] Error messages don't leak info

**Infrastructure:**
- [ ] Firewall configured
- [ ] Only necessary ports open
- [ ] Regular updates scheduled
- [ ] Monitoring configured
- [ ] Backup tested

## 🚨 Common Vulnerabilities & Prevention

### 1. SQL Injection

**Vulnerability:**
```php
// ❌ VULNERABLE
$id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = $id";
```

**Prevention:**
```php
// ✅ SAFE
$id = $_GET['id'];
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
```

### 2. Cross-Site Scripting (XSS)

**Vulnerability:**
```php
// ❌ VULNERABLE
<div><?php echo $_POST['comment']; ?></div>
```

**Prevention:**
```php
// ✅ SAFE
<div><?php echo sanitize($_POST['comment']); ?></div>
```

### 3. Cross-Site Request Forgery (CSRF)

**Vulnerability:**
```html
<!-- ❌ VULNERABLE -->
<form method="POST" action="/delete-account">
    <button>Delete Account</button>
</form>
```

**Prevention:**
```html
<!-- ✅ SAFE -->
<form method="POST" action="/delete-account">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <button>Delete Account</button>
</form>
```

### 4. Session Fixation

**Prevention:**
```php
// Regenerate session ID on login
session_start();
session_regenerate_id(true);
```

### 5. File Inclusion

**Vulnerability:**
```php
// ❌ VULNERABLE
include $_GET['page'] . '.php';
```

**Prevention:**
```php
// ✅ SAFE - Whitelist approach
$allowed_pages = ['home', 'about', 'contact'];
$page = $_GET['page'] ?? 'home';

if (in_array($page, $allowed_pages)) {
    include $page . '.php';
} else {
    include '404.php';
}
```

### 6. Insecure Direct Object References

**Vulnerability:**
```php
// ❌ VULNERABLE - No authorization check
$file = $_GET['file'];
readfile("/uploads/" . $file);
```

**Prevention:**
```php
// ✅ SAFE - Verify ownership
$fileId = $_GET['id'];
$file = $db->fetchOne("SELECT * FROM files WHERE id = ? AND user_id = ?", [$fileId, $userId]);

if ($file) {
    readfile($file['path']);
} else {
    http_response_code(403);
    die('Access denied');
}
```

## 🔐 Password Security

### Password Requirements

**Minimum Requirements:**
- At least 8 characters
- Mix of uppercase and lowercase
- At least one number
- At least one special character

**Validation:**
```php
function validatePassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return false; // No uppercase
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return false; // No lowercase
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return false; // No number
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return false; // No special char
    }
    
    return true;
}
```

### Password Storage

```php
// Hash password with bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verify password
if (password_verify($input, $hash)) {
    // Password correct
    
    // Check if rehash needed (algorithm updated)
    if (password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12])) {
        $newHash = password_hash($input, PASSWORD_BCRYPT, ['cost' => 12]);
        // Update database with new hash
    }
}
```

## 🛡️ Defense in Depth

### Layer 1: Infrastructure
- Firewall rules
- DDoS protection
- Regular updates
- Intrusion detection

### Layer 2: Application
- Input validation
- Output encoding
- Authentication
- Authorization

### Layer 3: Data
- Encryption at rest
- Encryption in transit
- Access controls
- Audit logging

## 📊 Security Monitoring

### Logging

**Security Events to Log:**
```php
// Failed login attempts
logSecurityEvent('failed_login', $email, $_SERVER['REMOTE_ADDR']);

// Successful logins
logSecurityEvent('successful_login', $userId, $_SERVER['REMOTE_ADDR']);

// Permission denied
logSecurityEvent('permission_denied', $userId, $resource);

// CSRF token failures
logSecurityEvent('csrf_failure', $userId, $_SERVER['REQUEST_URI']);
```

**Log Analysis:**
```bash
# Monitor failed logins
grep "failed_login" /var/log/app/security.log | tail -n 100

# Detect brute force
grep "failed_login" /var/log/app/security.log | cut -d' ' -f4 | sort | uniq -c | sort -nr

# Monitor admin access
grep "admin_access" /var/log/app/security.log
```

### Alert Triggers

**Set up alerts for:**
- Multiple failed login attempts (>5 in 5 minutes)
- Admin account access
- Database errors
- File upload failures
- CSRF token failures
- Unusual API usage

## 🔄 Regular Security Tasks

### Daily
- [ ] Review failed login attempts
- [ ] Check error logs
- [ ] Monitor disk usage

### Weekly
- [ ] Review access logs
- [ ] Check security alerts
- [ ] Update dependencies
- [ ] Database backup verification

### Monthly
- [ ] Security audit
- [ ] Penetration testing
- [ ] Code review
- [ ] Update documentation
- [ ] Review user permissions

### Quarterly
- [ ] Full security assessment
- [ ] Disaster recovery drill
- [ ] Update security policies
- [ ] Staff security training

## 🚨 Incident Response Plan

### If Security Breach Detected

**1. Immediate Actions (0-1 hour)**
- [ ] Take affected systems offline
- [ ] Preserve evidence (logs, database state)
- [ ] Assess scope of breach
- [ ] Activate incident response team

**2. Containment (1-4 hours)**
- [ ] Isolate compromised systems
- [ ] Change all passwords
- [ ] Revoke compromised sessions
- [ ] Block attacker IPs
- [ ] Review access logs

**3. Eradication (4-24 hours)**
- [ ] Identify attack vector
- [ ] Patch vulnerabilities
- [ ] Remove malware/backdoors
- [ ] Restore from clean backups
- [ ] Verify system integrity

**4. Recovery (1-3 days)**
- [ ] Restore services gradually
- [ ] Monitor for reinfection
- [ ] Enhanced logging enabled
- [ ] Additional security measures

**5. Post-Incident (1 week)**
- [ ] Document incident
- [ ] Notify affected users
- [ ] Update security procedures
- [ ] Implement lessons learned
- [ ] Report to authorities (if required)

## 📞 Security Contacts

**Report Security Issues:**
- Email: security@yourdomain.com
- Encrypted: Use PGP key
- Response time: 24-48 hours

**Responsible Disclosure:**
1. Report vulnerability privately
2. Allow 90 days for fix
3. Coordinate public disclosure
4. Credit given for valid reports

## 📚 Security Resources

**Learning:**
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [PostgreSQL Security](https://www.postgresql.org/docs/current/security.html)

**Tools:**
- [OWASP ZAP](https://www.zaproxy.org/) - Vulnerability scanner
- [Burp Suite](https://portswigger.net/burp) - Web security testing
- [Snyk](https://snyk.io/) - Dependency scanning

**Standards:**
- [PCI DSS](https://www.pcisecuritystandards.org/) - Payment security
- [GDPR](https://gdpr.eu/) - Data protection
- [ISO 27001](https://www.iso.org/isoiec-27001-information-security.html) - Information security

---

**Security is everyone's responsibility. Stay vigilant! 🛡️**

For deployment security, see `DEPLOYMENT.md`  
For development practices, see `DEVELOPER_NOTE.md`
