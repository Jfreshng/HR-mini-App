# 🧩 CodeIgniter 4 JWT API

A RESTful API built with **CodeIgniter 4**, featuring secure **JWT-based authentication**, **role-based access control**, and structured **employee management** with reporting.

---

## 🚀 Features

- 🔐 **JWT Authentication** (`login`, `register`)
- 👥 **Role-based authorization** — `admin` vs `user`
- 📊 **Employee Management** — create, update, delete, filter, and search
- 🧾 **Reports** — salary summaries and employees grouped by departments
- 💡 **Consistent API response schema** for all endpoints
- 🧱 **MySQL** database backend (via XAMPP)
- ⚙️ Configurable token expiration via `.env`

---

## 🛠️ Prerequisites

| Component | Version / Notes |
|------------|----------------|
| PHP        | ≥ 8.1 |
| Composer   | Latest |
| MySQL / MariaDB | Installed via XAMPP |
| XAMPP      | Apache + MySQL configured |
| CodeIgniter 4 | Installed via Composer |

---

## ⚙️ Installation Steps

1. **Clone or copy the project**
   ```bash
   cd C:\xampp\htdocs\
   git clone <your-repo-url> Auth-project
   cd Auth-project
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   Copy `.env.example` → `.env`, then configure:
   ```ini
   app.baseURL = 'http://localhost:8060/'
   database.default.hostname = localhost
   database.default.database = aptitudedb
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi

   # JWT configuration
   JWT_SECRET = "your_super_secret_key"
   JWT_EXPIRATION = 3600   # seconds (1 hour)
   ```

4. **Start Apache (XAMPP Control Panel)**
   Ensure Apache runs on port 8060 and MySQL is active.

5. **Run CodeIgniter built-in server (optional)**
   ```bash
   php spark serve
   ```
   Visit: `http://localhost:8080` or via XAMPP Apache: `http://localhost:8060`

---

## 🧮 Database Schema

**Database:** `aptitudedb`

### SQL Scripts

```sql
CREATE DATABASE IF NOT EXISTS aptitudedb;
USE aptitudedb;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user',
  in_service TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  position VARCHAR(100),
  salary DECIMAL(10,2) DEFAULT 0.00,
  department_id INT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

---

## 🔑 Authentication & Authorization

| Role | Permissions |
|------|--------------|
| **Admin** | Can manage users, employees, and view all reports |
| **User**  | Can only access their own employee record |

---

## 🔁 API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|-----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login (email + password) → returns JWT token |

### Employees

| Method | Endpoint | Access | Description |
|--------|-----------|--------|-------------|
| GET | `/api/employees` | Admin | List employees |
| POST | `/api/employees` | Admin | Create employee |
| PUT | `/api/employees/{id}` | Admin | Update employee |
| DELETE | `/api/employees/{id}` | Admin | Delete employee |
| GET | `/api/employees/{id}` | Admin/User | Get employee by ID |

### Reports

| Method | Endpoint | Access | Description |
|--------|-----------|--------|-------------|
| GET | `/api/reports/salary` | Admin | Salary summary by department |
| GET | `/api/reports/employees` | Admin | Employees grouped by department |
| GET | `/api/reports/salary/{employeeId}` | User | View own salary report |

---

## 🧱 Response Schema

```json
{
  "status": "success",
  "code": 200,
  "message": "Operation successful",
  "metadata": {},
  "data": {},
  "errors": []
}
```

**Error example:**
```json
{
  "status": "error",
  "code": 400,
  "message": "Invalid credentials",
  "metadata": {},
  "data": null,
  "errors": []
}
```

---

## 📚 License

MIT © 2025 — Your Name
