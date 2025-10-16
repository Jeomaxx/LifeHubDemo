# Bills Module API Documentation

## Overview

The Bills module provides comprehensive bill management, payment tracking, reminders, and budget integration features for the Life Atlas Organizer.

## Table of Contents

1. [API Endpoints](#api-endpoints)
2. [Database Schema](#database-schema)
3. [Bill Worker (Cron)](#bill-worker)
4. [Calendar Integration](#calendar-integration)
5. [CSV Import](#csv-import)
6. [Budget Impact](#budget-impact)

---

## API Endpoints

### Base URL
All API endpoints are prefixed with `/api/`

### Authentication
All endpoints require authentication via session. Include CSRF token for non-GET requests.

### Bills CRUD Operations

#### GET /api/bills.php?action=list
Get list of bills with optional filters

**Query Parameters:**
- `status` (optional): Filter by payment status (pending, paid)
- `category` (optional): Filter by category
- `vendor` (optional): Filter by vendor
- `from_date` (optional): Filter by start date (YYYY-MM-DD)
- `to_date` (optional): Filter by end date (YYYY-MM-DD)
- `recurring` (optional): Filter by recurring status (true/false)

**Response:**
```json
{
  "success": true,
  "bills": [
    {
      "id": 1,
      "user_id": 1,
      "name": "Electric Bill",
      "amount": "150.00",
      "due_date": "2025-01-15",
      "payment_status": "pending",
      "recurring": true,
      "frequency": "monthly",
      "category": "utilities",
      "vendor": "Power Company",
      "reminder_days_before": 3,
      "notes": "Account #12345",
      "payments": []
    }
  ]
}
```

#### GET /api/bills.php?action=detail&id={billId}
Get detailed information about a specific bill

**Response:**
```json
{
  "success": true,
  "bill": {
    "id": 1,
    "name": "Electric Bill",
    "amount": "150.00",
    "due_date": "2025-01-15",
    "payments": [
      {
        "id": 1,
        "amount": "150.00",
        "payment_date": "2025-01-14",
        "payment_method": "credit_card"
      }
    ],
    "budget": {
      "id": 1,
      "category": "utilities",
      "monthly_limit": "500.00"
    }
  }
}
```

#### POST /api/bills.php?action=create
Create a new bill

**Request Body:**
```json
{
  "name": "Water Bill",
  "amount": 75.50,
  "due_date": "2025-01-20",
  "category": "utilities",
  "vendor": "Water Department",
  "payment_status": "pending",
  "recurring": true,
  "frequency": "monthly",
  "reminder_days_before": 5,
  "notes": "Account #98765",
  "auto_pay": false,
  "budget_id": 1,
  "payment_method": "bank_transfer",
  "csrf_token": "..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Bill created successfully",
  "bill": { ... }
}
```

#### PUT /api/bills.php?id={billId}
Update an existing bill

**Request Body:** (Same as create, all fields optional)

#### DELETE /api/bills.php?id={billId}&csrf_token=...
Delete a bill

**Response:**
```json
{
  "success": true,
  "message": "Bill deleted successfully"
}
```

### Bill Actions

#### POST /api/bills.php?action=mark-paid
Mark a bill as paid and record payment

**Request Body:**
```json
{
  "bill_id": 1,
  "amount": 150.00,
  "payment_date": "2025-01-14",
  "payment_method": "credit_card",
  "transaction_id": "TXN123456",
  "notes": "Paid online",
  "csrf_token": "..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Bill marked as paid",
  "payment_id": 5,
  "next_bill_id": 10
}
```

#### POST /api/bills.php?action=bulk-mark-paid
Mark multiple bills as paid

**Request Body:**
```json
{
  "bill_ids": [1, 2, 3],
  "csrf_token": "..."
}
```

**Response:**
```json
{
  "success": true,
  "results": [
    {"bill_id": 1, "success": true},
    {"bill_id": 2, "success": true},
    {"bill_id": 3, "success": false, "error": "Not found"}
  ]
}
```

#### POST /api/bills.php?action=send-reminder
Send manual reminder for a bill

**Request Body:**
```json
{
  "bill_id": 1,
  "csrf_token": "..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reminder sent",
  "channels": ["email", "telegram"]
}
```

### Analytics Endpoints

#### GET /api/bills.php?action=overdue
Get all overdue bills

#### GET /api/bills.php?action=upcoming&days=7
Get upcoming bills (default: 7 days)

#### GET /api/bills.php?action=payment-history&bill_id={id}
Get payment history for a bill or all bills

#### GET /api/bills.php?action=stats
Get bill statistics

**Response:**
```json
{
  "success": true,
  "stats": {
    "total_bills": 25,
    "pending_bills": 10,
    "overdue_bills": 2,
    "total_amount_due": "1250.00",
    "total_paid_this_month": "3500.00",
    "recurring_bills": 15
  }
}
```

#### GET /api/bills.php?action=by-vendor
Get bills grouped by vendor

---

## Calendar Integration

### Export to ICS

#### GET /api/bills_calendar.php?action=export
Export bills to ICS calendar file

Downloads: `bills_calendar.ics`

### Google Calendar Sync

#### GET /api/bills_calendar.php?action=google-sync
Get instructions for Google Calendar import

**Response:**
```json
{
  "success": true,
  "instructions": [...],
  "download_url": "/api/bills_calendar.php?action=export"
}
```

---

## CSV Import

### POST /api/bills_import.php
Import bills from CSV file

**Request:**
- Content-Type: multipart/form-data
- File field: csv_file
- CSRF token: csrf_token

**CSV Format:**
```csv
name,amount,due_date,category,vendor,recurring,frequency,notes
Electric Bill,150.00,2025-01-15,utilities,Power Co,true,monthly,Account 123
Water Bill,75.50,2025-01-20,utilities,Water Dept,true,monthly,
```

**Response:**
```json
{
  "success": true,
  "imported": 2,
  "total_rows": 2,
  "errors": [],
  "message": "Successfully imported 2 bills"
}
```

---

## Budget Impact

### GET /api/bills_budget_impact.php?action=check&bill_id={id}
Check budget impact for a specific bill

**Response:**
```json
{
  "has_budget": true,
  "budget": {
    "id": 1,
    "category": "utilities",
    "monthly_limit": "500.00"
  },
  "impact": {
    "actual_spent": "200.00",
    "pending_bills": "225.00",
    "total_committed": "425.00",
    "remaining": "75.00",
    "percent_used": 85.0,
    "warning_level": "warning",
    "message": "Budget usage high. $75.00 remaining"
  }
}
```

**Warning Levels:**
- `safe`: < 75% used
- `warning`: 75-89% used
- `danger`: 90-99% used
- `critical`: >= 100% used (over budget)

### GET /api/bills_budget_impact.php?action=summary
Get budget impact summary for all budgets

---

## Bill Export

### GET /api/bills_export.php?format=csv
Export bills to CSV

**Query Parameters:**
- `format`: csv or json (default: csv)
- `status`: Filter by status (optional)
- `category`: Filter by category (optional)
- `from_date`: Start date (optional)
- `to_date`: End date (optional)

Downloads: `bills_export_YYYY-MM-DD.csv`

### GET /api/bills_export.php?format=json
Export bills to JSON

Downloads: `bills_export_YYYY-MM-DD.json`

---

## Database Schema

### bills Table
```sql
CREATE TABLE bills (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    name VARCHAR(150) NOT NULL,
    amount NUMERIC(12,2) NOT NULL,
    due_date DATE NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    recurring BOOLEAN DEFAULT false,
    frequency VARCHAR(50),
    category VARCHAR(100),
    vendor VARCHAR(150),
    reminder_days_before INTEGER DEFAULT 3,
    notes TEXT,
    next_due_date DATE,
    last_paid_date DATE,
    auto_pay BOOLEAN DEFAULT FALSE,
    budget_id INTEGER REFERENCES budgets(id),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### bill_payments Table
```sql
CREATE TABLE bill_payments (
    id SERIAL PRIMARY KEY,
    bill_id INTEGER NOT NULL REFERENCES bills(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount NUMERIC(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Indexes
- `idx_bills_user_id_due_date` ON bills(user_id, due_date)
- `idx_bills_payment_status` ON bills(payment_status)
- `idx_bills_next_due_date` ON bills(next_due_date)
- `idx_bills_category` ON bills(category)
- `idx_bills_vendor` ON bills(vendor)
- `idx_bill_payments_bill_id` ON bill_payments(bill_id)
- `idx_bill_payments_user_id` ON bill_payments(user_id)
- `idx_bill_payments_payment_date` ON bill_payments(payment_date)

---

## Bill Worker (Cron Job)

### Script: `/cron/bill_worker.php`

**Recommended Crontab:**
```bash
*/15 * * * * php /path/to/cron/bill_worker.php
```

**Features:**
1. **Send Bill Reminders** - Sends email/Telegram reminders based on `reminder_days_before`
2. **Mark Overdue Bills** - Creates notifications for bills past due date
3. **Generate Recurring Bills** - Automatically creates next occurrence for paid recurring bills
4. **Send Escalations** - Sends urgent notifications for bills overdue >7 days

**Output Example:**
```
=== Bill Worker Started at 2025-01-14 10:00:00 ===

1. Checking for bills needing reminders...
   - Sent email reminder to user@example.com for bill #5
   Sent 1 reminders

2. Marking overdue bills...
   - Marked bill #3 as overdue (2 days)
   Marked 1 bills as overdue

3. Generating next recurring bills...
   - Generated next occurrence for 'Electric Bill' (ID: 10, Due: 2025-02-15)
   Generated 1 recurring bills

4. Checking for overdue escalations...
   - Sent escalation email for bill #7
   Sent 1 escalation notifications

=== Bill Worker Completed Successfully ===
Total items processed: 4
Finished at: 2025-01-14 10:00:05
```

**Idempotency:** The worker is designed to be idempotent and safe to run multiple times without creating duplicate notifications.

---

## Error Handling

All API endpoints return appropriate HTTP status codes:
- `200` - Success
- `400` - Bad Request (missing/invalid parameters)
- `401` - Unauthorized (not logged in)
- `403` - Forbidden (invalid CSRF token)
- `404` - Not Found
- `429` - Too Many Requests (rate limited)
- `500` - Internal Server Error

**Error Response Format:**
```json
{
  "error": "Error message description"
}
```

---

## Rate Limiting

- Bills API: 100 requests per minute
- Import API: 10 requests per hour
- General limit: Applied per IP address

---

## Security Features

1. **Authentication Required** - All endpoints require active session
2. **CSRF Protection** - All non-GET requests require valid CSRF token
3. **Rate Limiting** - Prevents API abuse
4. **Input Validation** - All inputs sanitized and validated
5. **Prepared Statements** - SQL injection prevention
6. **User Isolation** - Users can only access their own bills

---

## Integration Examples

### JavaScript/AJAX
```javascript
// Create a bill
async function createBill(billData) {
  const response = await fetch('/api/bills.php?action=create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      ...billData,
      csrf_token: document.querySelector('[name="csrf_token"]').value
    })
  });
  
  return await response.json();
}

// Mark bill as paid
async function markBillPaid(billId) {
  const response = await fetch('/api/bills.php?action=mark-paid', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      bill_id: billId,
      payment_date: new Date().toISOString().split('T')[0],
      csrf_token: document.querySelector('[name="csrf_token"]').value
    })
  });
  
  return await response.json();
}
```

### PHP
```php
// Get bill stats
$ch = curl_init('https://yoursite.com/api/bills.php?action=stats');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

print_r($response['stats']);
```

---

## Support

For issues or questions:
1. Check this documentation
2. Review the SETUP_GUIDE.md
3. Check system logs in /tmp/logs/
4. Review bill_worker.php output for cron issues
