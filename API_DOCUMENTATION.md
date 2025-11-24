# AuditFlow API Documentation

## Base URL
```
http://localhost:8080/api
```

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {access_token}
```

---

## Authentication Endpoints

### Login
**POST** `/login`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "access_token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "roles": [
      {
        "id": 1,
        "name": "admin"
      }
    ]
  }
}
```

### Logout
**POST** `/logout` 🔒

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

### Get Current User
**GET** `/user` 🔒

**Response (200):**
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "roles": [...]
}
```

---

## Client Management

### List Clients
**GET** `/clients` 🔒

**Response (200):**
```json
[
  {
    "id": 1,
    "business_name": "ABC Corp",
    "contact_person": "John Doe",
    "email": "john@abc.com",
    "phone": "1234567890",
    "address": "123 Main St",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

### Create Client
**POST** `/clients` 🔒

**Request Body:**
```json
{
  "business_name": "ABC Corp",
  "contact_person": "John Doe",
  "email": "john@abc.com",
  "phone": "1234567890",
  "address": "123 Main St",
  "pan_number": "ABCDE1234F",
  "gst_number": "22ABCDE1234F1Z5",
  "business_type": "Private Limited",
  "filing_cycle": "Monthly"
}
```

**Response (201):**
```json
{
  "id": 1,
  "business_name": "ABC Corp",
  ...
}
```

### Get Client
**GET** `/clients/{id}` 🔒

### Update Client
**PUT** `/clients/{id}` 🔒

### Delete Client
**DELETE** `/clients/{id}` 🔒

---

## Employee Management

### List Employees
**GET** `/employees` 🔒

**Response (200):**
```json
[
  {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@auditflow.com",
    "roles": [
      {
        "id": 2,
        "name": "manager"
      }
    ]
  }
]
```

### Create Employee
**POST** `/employees` 🔒

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@auditflow.com",
  "password": "password123",
  "role": "manager"
}
```

**Roles:** `admin`, `manager`, `employee`

### Get Employee
**GET** `/employees/{id}` 🔒

### Update Employee
**PUT** `/employees/{id}` 🔒

**Request Body:**
```json
{
  "name": "Jane Smith Updated",
  "email": "jane.updated@auditflow.com",
  "password": "newpassword123",
  "role": "employee"
}
```

### Delete Employee
**DELETE** `/employees/{id}` 🔒

---

## File/Case Management

### List Files
**GET** `/files` 🔒

**Response (200):**
```json
[
  {
    "id": 1,
    "client_id": 1,
    "file_number": "F-2024-001",
    "year": "2024",
    "service_type": "Income Tax",
    "status": "in_progress",
    "assigned_to": 2,
    "description": "Annual tax filing",
    "client": {...},
    "assignee": {...}
  }
]
```

### Create File
**POST** `/files` 🔒

**Request Body:**
```json
{
  "client_id": 1,
  "file_number": "F-2024-001",
  "year": "2024",
  "service_type": "Income Tax",
  "status": "pending",
  "assigned_to": 2,
  "description": "Annual tax filing"
}
```

**Status Values:** `pending`, `in_progress`, `completed`, `on_hold`

### Get File
**GET** `/files/{id}` 🔒

### Update File
**PUT** `/files/{id}` 🔒

### Delete File
**DELETE** `/files/{id}` 🔒

---

## Work Logs

### Create Work Log
**POST** `/files/{id}/worklogs` 🔒

**Request Body:**
```json
{
  "date": "2024-01-15",
  "hours": 3.5,
  "description": "Prepared tax computation"
}
```

**Response (201):**
```json
{
  "id": 1,
  "file_id": 1,
  "user_id": 2,
  "date": "2024-01-15",
  "hours": 3.5,
  "description": "Prepared tax computation",
  "created_at": "2024-01-15T10:00:00.000000Z"
}
```

### Get Work Logs by File
**GET** `/files/{id}/logs` 🔒

**Response (200):**
```json
[
  {
    "id": 1,
    "file_id": 1,
    "user_id": 2,
    "date": "2024-01-15",
    "hours": 3.5,
    "description": "Prepared tax computation",
    "user": {
      "id": 2,
      "name": "Jane Smith"
    }
  }
]
```

### Update Work Log
**PUT** `/worklogs/{id}` 🔒

### Delete Work Log
**DELETE** `/worklogs/{id}` 🔒

---

## Document Management

### Upload Document
**POST** `/files/{id}/documents` 🔒

**Request (multipart/form-data):**
```
file: [binary file]
```

**Response (201):**
```json
{
  "id": 1,
  "file_id": 1,
  "file_name": "tax_return.pdf",
  "file_path": "documents/abc123.pdf",
  "file_type": "application/pdf",
  "file_size": 102400,
  "uploaded_by": 2
}
```

### List Documents by File
**GET** `/files/{id}/documents` 🔒

### Delete Document
**DELETE** `/documents/{id}` 🔒

---

## Billing & Invoices

### List Invoices
**GET** `/invoices` 🔒

**Query Parameters:**
- `client_id` (optional): Filter by client

**Response (200):**
```json
[
  {
    "id": 1,
    "client_id": 1,
    "invoice_number": "INV-2024-001",
    "invoice_date": "2024-01-01",
    "due_date": "2024-01-15",
    "total_amount": 10000.00,
    "tax_amount": 1800.00,
    "status": "unpaid",
    "notes": "Q1 2024 services",
    "client": {...}
  }
]
```

### Create Invoice
**POST** `/invoices` 🔒

**Request Body:**
```json
{
  "client_id": 1,
  "invoice_number": "INV-2024-001",
  "invoice_date": "2024-01-01",
  "due_date": "2024-01-15",
  "total_amount": 10000.00,
  "tax_amount": 1800.00,
  "notes": "Q1 2024 services"
}
```

**Response (201):**
```json
{
  "id": 1,
  "status": "unpaid",
  ...
}
```

### Get Invoice
**GET** `/invoices/{id}` 🔒

### Record Payment
**POST** `/invoices/{id}/payments` 🔒

**Request Body:**
```json
{
  "amount": 5000.00,
  "payment_date": "2024-01-10",
  "payment_method": "bank_transfer"
}
```

**Payment Methods:** `cash`, `bank_transfer`, `cheque`, `online`

**Response (201):**
```json
{
  "id": 1,
  "invoice_id": 1,
  "amount": 5000.00,
  "payment_date": "2024-01-10",
  "payment_method": "bank_transfer"
}
```

> **Note:** Invoice status automatically updates to `partial` or `paid` based on total payments received.

---

## Notifications

### Get Notifications
**GET** `/notifications` 🔒

**Response (200):**
```json
[
  {
    "id": "abc-123",
    "type": "App\\Notifications\\InvoiceGenerated",
    "data": {
      "message": "New invoice INV-2024-001 created",
      "invoice_id": 1
    },
    "read_at": null,
    "created_at": "2024-01-01T10:00:00.000000Z"
  }
]
```

### Get Unread Count
**GET** `/notifications/unread-count` 🔒

**Response (200):**
```json
{
  "count": 5
}
```

### Mark as Read
**PUT** `/notifications/{id}/read` 🔒

**Response (200):**
```json
{
  "message": "Notification marked as read"
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server Error"
}
```

---

## Notes

🔒 = Requires authentication

- All timestamps are in UTC
- All monetary amounts are in decimal format (e.g., 10000.00)
- File uploads have a maximum size of 10MB
- Invoice status is automatically managed based on payments
