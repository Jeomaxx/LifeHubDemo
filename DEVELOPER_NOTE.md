# Life Atlas Organizer - Developer Documentation

## 📚 Table of Contents

- [Project Overview](#project-overview)
- [Architecture](#architecture)
- [Development Setup](#development-setup)
- [Code Standards](#code-standards)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [Frontend Architecture](#frontend-architecture)
- [Security Implementation](#security-implementation)
- [Testing Guidelines](#testing-guidelines)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## 🎯 Project Overview

Life Atlas Organizer is a comprehensive personal life management platform built with PHP and PostgreSQL. It centralizes 16+ life modules into a unified dashboard with real-time cryptocurrency tracking, automated backups, and multi-channel notifications.

### Technology Stack

**Backend:**
- PHP 8.2+
- PostgreSQL (Neon-backed via Replit)
- Session-based authentication
- RESTful API architecture

**Frontend:**
- Vanilla JavaScript (ES6+)
- HTML5 & CSS3
- Chart.js for analytics
- Font Awesome icons
- No build tools required

**External Services:**
- CoinGecko API (crypto prices)
- SMTP (email notifications)
- Telegram Bot API (push notifications)

### Key Features

- 16 life management modules
- Real-time cryptocurrency tracking
- Automated cron jobs
- Multi-channel notifications
- Data export/import (CSV/JSON)
- System management panel
- Dark/light themes
- Mobile-responsive design

## 🏗️ Architecture

### Directory Structure

```
project/
├── api/                    # API endpoints
│   ├── crypto.php          # Crypto operations
│   ├── system.php          # System management
│   ├── tasks-enhanced.php  # Task enhancements
│   └── [module]-api.php    # Module APIs
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/
│   │   ├── main.js         # Core JavaScript
│   │   ├── charts.js       # Chart utilities
│   │   └── crypto.js       # Crypto frontend
│   └── images/
├── cron/                   # Cron job scripts
│   ├── cron_fetch_crypto.php
│   ├── reminders.php
│   └── backup.php
├── includes/               # Core PHP files
│   ├── config.php          # Configuration
│   ├── auth.php            # Authentication
│   ├── db.php              # Database class
│   ├── functions.php       # Helper functions
│   ├── header.php          # Page header
│   ├── footer.php          # Page footer
│   ├── i18n.php            # Internationalization
│   ├── notifications.php   # Notification system
│   └── totp.php            # 2FA (future)
├── lang/                   # Language files
│   ├── en.php
│   └── ar.php
├── uploads/                # User uploads
│   └── backups/            # Backup storage
├── [module].php            # Module pages
├── database.sql            # Database schema
└── index.php               # Entry point
```

### MVC-Style Architecture

**Model Layer:**
- `includes/db.php` - Database abstraction
- Direct SQL queries in module files
- PostgreSQL-specific features utilized

**View Layer:**
- `includes/header.php` & `includes/footer.php` - Layout templates
- Module PHP files contain embedded HTML
- JavaScript for dynamic UI updates

**Controller Layer:**
- `api/` directory - RESTful endpoints
- Module PHP files handle page logic
- `includes/functions.php` - Business logic helpers

## 💻 Development Setup

### Prerequisites

```bash
# Check PHP version
php -v  # Should be 8.2+

# Check PostgreSQL
psql --version

# Required PHP extensions
php -m | grep -E "pgsql|pdo_pgsql|curl|json|mbstring|openssl"
```

### Local Setup

1. **Clone Repository**
```bash
git clone <repository-url>
cd life-atlas-organizer
```

2. **Configure Database**
```bash
# Create database
createdb life_atlas_dev

# Import schema
psql -U username -d life_atlas_dev -f database.sql
```

3. **Configure Application**
```php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'life_atlas_dev');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Development mode
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

4. **Start Development Server**
```bash
php -S localhost:8000
```

5. **Access Application**
```
http://localhost:8000
```

### Development Workflow

1. Create feature branch
2. Make changes
3. Test locally
4. Commit changes
5. Push to repository
6. Deploy to staging
7. Test on staging
8. Deploy to production

## 📝 Code Standards

### PHP Coding Standards

```php
<?php
// PSR-12 compliant formatting

class Example {
    private $property;
    
    public function method($param) {
        // Indentation: 4 spaces
        if ($condition) {
            // Code here
        }
        
        return $result;
    }
}

// Function naming: camelCase
function getUserData($userId) {
    // Implementation
}

// Constants: UPPER_CASE
define('MAX_LOGIN_ATTEMPTS', 5);

// Security: Always sanitize input
$clean = sanitize($_POST['input']);

// Database: Always use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
```

### JavaScript Standards

```javascript
// ES6+ features used
const API_URL = '/api';

// Async/await for API calls
async function fetchData(endpoint) {
    try {
        const response = await fetch(`${API_URL}/${endpoint}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
    }
}

// Arrow functions
const filterItems = items => items.filter(item => item.active);

// Template literals
const message = `User ${username} logged in at ${timestamp}`;

// Destructuring
const {id, name, email} = user;
```

### CSS/HTML Standards

```css
/* BEM-style naming */
.card {}
.card__header {}
.card__body {}
.card--featured {}

/* CSS Custom Properties */
:root {
    --primary-color: #3b82f6;
    --text-color: #1f2937;
}

/* Mobile-first responsive */
.container {
    /* Mobile styles */
}

@media (min-width: 768px) {
    .container {
        /* Tablet styles */
    }
}
```

## 🗄️ Database Schema

### Core Tables

**users**
- Authentication and user data
- Admin flag for elevated permissions
- Telegram chat_id for notifications

**Crypto Tables**
- `crypto_coins` - Coin metadata
- `crypto_portfolio` - User holdings
- `crypto_alerts` - Price alerts
- `crypto_price_history` - Historical prices
- `crypto_transactions` - Transaction log

**Module Tables**
- `assets`, `bills`, `birthdays`
- `finance`, `goals`, `habits`
- `health`, `hobbies`, `investments`
- `journal`, `learning`, `media`
- `subscriptions`, `tasks`

**Task Enhancement Tables**
- `task_dependencies` - Task relationships
- `pomodoro_sessions` - Focus tracking

**System Tables**
- `backups` - Backup metadata
- `notifications` - Notification queue

### Database Class (`includes/db.php`)

```php
class Database {
    private static $instance = null;
    private $pdo;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch single row
    public function fetchOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Execute query
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
```

## 🔌 API Documentation

### API Structure

All API endpoints follow RESTful conventions:

```
/api/{module}.php?action={action}
```

### Authentication

APIs check authentication via session:

```php
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
```

### Response Format

```json
{
    "success": true|false,
    "message": "Description",
    "data": {}  // Optional
}
```

### Crypto API Endpoints

**Add to Portfolio**
```
POST /api/crypto.php?action=add
{
    "crypto_id": "bitcoin",
    "crypto_symbol": "btc",
    "crypto_name": "Bitcoin",
    "amount": 0.5,
    "purchase_price": 45000,
    "purchase_date": "2024-01-01",
    "notes": "Optional notes"
}
```

**Create Alert**
```
POST /api/crypto.php?action=create_alert
{
    "crypto_id": "bitcoin",
    "crypto_symbol": "btc",
    "alert_type": "above|below",
    "target_price": 50000
}
```

**Import CSV**
```
POST /api/crypto.php?action=import_csv
Content-Type: multipart/form-data
csv_file: [file]
```

**Export Portfolio**
```
GET /api/crypto.php?action=export
Returns: CSV file download
```

### System API Endpoints

**Create Backup**
```
POST /api/system.php?action=create_backup
```

**Test Cron Jobs**
```
GET /api/system.php?action=test_cron
```

**Test Email**
```
POST /api/system.php?action=test_email
```

**Test Telegram**
```
POST /api/system.php?action=test_telegram
```

## 🎨 Frontend Architecture

### JavaScript Modules

**main.js** - Core functionality
- Modal management
- Form handling
- Toast notifications
- Theme toggling
- Global search

**charts.js** - Chart utilities
- Chart.js initialization
- Data formatting
- Color schemes

**crypto.js** - Cryptocurrency frontend
- Price fetching
- Portfolio updates
- Alert management
- Top coins display

### State Management

Simple state management via global objects:

```javascript
// Global state
window.AppState = {
    user: null,
    theme: 'light',
    cryptoPrices: {}
};

// Update state
function updateCryptoPrices(prices) {
    window.AppState.cryptoPrices = prices;
    renderPortfolio();
}
```

### API Communication

```javascript
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showToast('Request failed', 'error');
    }
}
```

## 🔒 Security Implementation

### Input Sanitization

```php
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Usage
$clean_email = sanitize($_POST['email']);
```

### CSRF Protection

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
    
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        return false;
    }
    
    return true;
}
```

### Password Hashing

```php
// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Verify password
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}
```

### SQL Injection Prevention

```php
// Always use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Never concatenate user input
// BAD: "SELECT * FROM users WHERE id = " . $_GET['id']
```

## 🧪 Testing Guidelines

### Manual Testing Checklist

**Authentication:**
- [ ] Registration works
- [ ] Login works
- [ ] Logout works
- [ ] Password reset works
- [ ] Session timeout works

**Core Modules:**
- [ ] CRUD operations for each module
- [ ] Data validation works
- [ ] Error handling works
- [ ] Search/filter works

**Crypto Module:**
- [ ] Portfolio displays correctly
- [ ] Prices update
- [ ] Alerts trigger
- [ ] CSV import works
- [ ] CSV export works

**System:**
- [ ] Backups create successfully
- [ ] Cron jobs run
- [ ] Email sends
- [ ] Telegram sends

### Testing Cron Jobs

```bash
# Test crypto price fetcher
php /path/to/cron/cron_fetch_crypto.php

# Test reminders
php /path/to/cron/reminders.php

# Test backup
php /path/to/cron/backup.php
```

### Database Testing

```sql
-- Test data integrity
SELECT COUNT(*) FROM tasks WHERE user_id NOT IN (SELECT id FROM users);

-- Test indexes
EXPLAIN ANALYZE SELECT * FROM crypto_portfolio WHERE user_id = 1;

-- Test foreign keys
SELECT 
    tc.table_name, 
    tc.constraint_name,
    tc.constraint_type
FROM information_schema.table_constraints tc
WHERE tc.constraint_type = 'FOREIGN KEY';
```

## 🚀 Deployment

See `DEPLOYMENT.md` for comprehensive deployment guide.

### Quick Deployment Checklist

- [ ] Update `includes/config.php`
- [ ] Import `database.sql`
- [ ] Set file permissions
- [ ] Configure cron jobs
- [ ] Setup SMTP (optional)
- [ ] Setup Telegram (optional)
- [ ] Enable HTTPS
- [ ] Test all features
- [ ] Create admin account

## 🐛 Troubleshooting

### Common Issues

**Database Connection Failed**
```php
// Check connection
try {
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

**Cron Jobs Not Running**
```bash
# Check cron logs
tail -f /var/log/cron
tail -f /home/username/logs/crypto.log

# Test manually
php -f /path/to/cron/script.php

# Check permissions
ls -la /path/to/cron/
```

**Email Not Sending**
```php
// Test SMTP connection
$smtp = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
if ($smtp) {
    echo "SMTP connection successful";
    fclose($smtp);
} else {
    echo "SMTP connection failed: $errstr ($errno)";
}
```

## 🤝 Contributing

### Development Process

1. **Fork repository**
2. **Create feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make changes**
4. **Follow code standards**
5. **Test thoroughly**
6. **Commit changes**
   ```bash
   git commit -m "Add amazing feature"
   ```
7. **Push to branch**
   ```bash
   git push origin feature/amazing-feature
   ```
8. **Open Pull Request**

### Code Review Guidelines

- Code follows PSR-12 standards
- All functions documented
- Security best practices followed
- Prepared statements used
- Input sanitized
- Error handling implemented
- Tests pass
- No debug code left

### Git Commit Messages

```
feat: Add cryptocurrency portfolio tracking
fix: Resolve database connection timeout
docs: Update API documentation
refactor: Simplify authentication logic
test: Add crypto module tests
chore: Update dependencies
```

## 📋 Maintenance

### Regular Tasks

**Daily:**
- Monitor error logs
- Check backup creation
- Review system health

**Weekly:**
- Review security logs
- Update dependencies
- Database vacuum/analyze

**Monthly:**
- Security audit
- Performance review
- Backup restoration test
- Update documentation

### Monitoring

```bash
# Check disk usage
df -h

# Check database size
psql -c "SELECT pg_size_pretty(pg_database_size('life_atlas_db'));"

# Check PHP errors
tail -f /var/log/php_errors.log

# Monitor cron jobs
tail -f /home/username/logs/*.log
```

## 📚 Additional Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Chart.js Documentation](https://www.chartjs.org/docs/)
- [CoinGecko API](https://www.coingecko.com/en/api)
- [Telegram Bot API](https://core.telegram.org/bots/api)

---

**Happy Coding! 🚀**

For deployment help, see `DEPLOYMENT.md`  
For security guidelines, see `SECURITY.md`
