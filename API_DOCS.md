# API Documentation - Approval System Backend

Base URL for all endpoints is `/api` (e.g., `http://localhost:8000/api`).
All endpoints except `/login` require authentication via Bearer Token header: `Authorization: Bearer <your_access_token>`.

---

## 1. Authentication

### Login
Authenticate a user and retrieve an access token.
- **Endpoint:** `POST /login`
- **Auth Required:** No
- **Body (JSON / Form Data):**
  ```json
  {
    "email": "user@example.com",
    "password": "yourpassword"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Login berhasil",
    "data": {
      "user": { ... },
      "access_token": "1|abcdef123456...",
      "token_type": "Bearer"
    }
  }
  ```
- **Error Response (401 Unauthorized):**
  ```json
  {
    "code": 401,
    "message": "Email atau password salah",
    "errors": "Unauthorized"
  }
  ```

### 🧪 Dummy Accounts for Testing
You can use these pre-configured accounts (from `DatabaseSeeder`) to test the API across different roles. The password for all accounts is **`password123`**.

| Role       | Email                | Password      |
|------------|----------------------|---------------|
| **User**     | `user@aqi.com`       | `password123` |
| **Verifier** | `verifier@aqi.com`   | `password123` |
| **Approver** | `approver@aqi.com`   | `password123` |

### Logout
Revoke the current user's access token.
- **Endpoint:** `POST /logout`
- **Auth Required:** Yes
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Logout berhasil",
    "data": null
  }
  ```

---

## 2. General Endpoints (All Roles)

### Get Statistics
Get statistical data for dashboard charts and cards.
- **Endpoint:** `GET /claims/stats`
- **Auth Required:** Yes (User, Verifier, Approver)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Get Stats Berhasil",
    "data": {
      "total_claims": 10,
      "approved_claims": 5,
      ...
    }
  }
  ```

### Get History
Get the history of processed claims for Verifier and Approver roles. 
- *Verifier* sees claims with status: `reviewed`, `approved`, `rejected`.
- *Approver* sees claims with status: `approved`, `rejected`.
- **Endpoint:** `GET /claims/history`
- **Auth Required:** Yes (Verifier, Approver)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Get History Berhasil",
    "data": [ ... ]
  }
  ```

---

## 3. Role: User Endpoints

### Create Claim
Create a new claim.
- **Endpoint:** `POST /claims`
- **Auth Required:** Yes (User)
- **Body (`multipart/form-data`):**
  - `title` (string, required)
  - `description` (string, required)
  - `amount` (numeric, min: 0, required)
  - `attachment` (file: jpeg, png, jpg, max: 2048, optional)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Klaim berhasil dibuat",
    "data": {
      "id": 1,
      "title": "Claim Title",
      "description": "Claim Description",
      "amount": 50000,
      "attachment_path": "claims/...",
      ...
    }
  }
  ```

### Get My Claims
Get a list of claims created by the authenticated user.
- **Endpoint:** `GET /claims/my`
- **Auth Required:** Yes (User)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Get Data Berhasil",
    "data": [ ... ]
  }
  ```

### Submit Claim
Submit a newly created claim for verification.
- **Endpoint:** `PATCH /claims/{id}/submit`
- **Auth Required:** Yes (User)
- **Body (JSON / Form Data):**
  ```json
  {
    "status": "submitted"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Status berhasil diubah menjadi submitted",
    "data": { ... }
  }
  ```

---

## 4. Role: Verifier Endpoints

### Get Submitted Claims
Get a list of all claims currently waiting for verification (`submitted` status).
- **Endpoint:** `GET /claims/submitted`
- **Auth Required:** Yes (Verifier)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Get Data Berhasil",
    "data": [ ... ]
  }
  ```

### Verify Claim
Mark a submitted claim as reviewed.
- **Endpoint:** `PATCH /claims/{id}/verify`
- **Auth Required:** Yes (Verifier)
- **Body (JSON / Form Data):**
  ```json
  {
    "status": "reviewed"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Status berhasil diubah menjadi reviewed",
    "data": { ... }
  }
  ```

---

## 5. Role: Approver Endpoints

### Get Reviewed Claims
Get a list of all claims that have been verified and are waiting for final approval (`reviewed` status).
- **Endpoint:** `GET /claims/reviewed`
- **Auth Required:** Yes (Approver)
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Get Data Berhasil",
    "data": [ ... ]
  }
  ```

### Approve Claim
Approve a reviewed claim.
- **Endpoint:** `PATCH /claims/{id}/approve`
- **Auth Required:** Yes (Approver)
- **Body (JSON / Form Data):**
  ```json
  {
    "status": "approved"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Status berhasil diubah menjadi approved",
    "data": { ... }
  }
  ```

### Reject Claim
Reject a reviewed claim.
- **Endpoint:** `PATCH /claims/{id}/reject`
- **Auth Required:** Yes (Approver)
- **Body (JSON / Form Data):**
  ```json
  {
    "status": "rejected"
  }
  ```
- **Success Response (200 OK):**
  ```json
  {
    "code": 200,
    "message": "Status berhasil diubah menjadi rejected",
    "data": { ... }
  }
  ```

---

## ⚠️ Common Error Responses

**Validation Error (422 Unprocessable Entity):**
```json
{
  "code": 422,
  "message": "Validasi gagal",
  "errors": {
    "field_name": ["Error detail message"]
  }
}
```

**Server Error (500 Internal Server Error):**
```json
{
  "code": 500,
  "message": "Something went wrong",
  "errors": "Optional exception message detail (depends on endpoint)"
}
```
