# Nigeria Immigration Service Hospital Management System (NIS HMS)

An enterprise-grade, world-class Hospital Management System designed for the **Nigeria Immigration Service (NIS)**. The application digitizes and coordinates all medical operations, covering patient records, appointments, nursing triage, consultations, pharmacy inventory, lab/radiology diagnostics, billing cashpoints, audit logs, and executive analytics.

---

## 🚀 Technologies Stack

### Frontend Portal
- **Framework**: React 19 + TypeScript + Vite
- **Styling**: Tailwind CSS v4 + Outfit Google Fonts
- **State & Server sync**: Axios Client with authorization interceptors
- **Form validation**: Zod schema constraints

### Backend REST API
- **Framework**: Laravel 12 + PHP 8.4
- **Auth & Protection**: Laravel Sanctum tokens + custom Multi-Factor Authentication (MFA)
- **Architecture**: Repository Pattern + Service Layer + DTO validations
- **Middlewares**: Custom RBAC Verification + Automating Audit Trail capture

### Database & Deployments
- **Database**: PostgreSQL 15 (normalized to 3NF)
- **Caching**: Redis Cache
- **Orchestration**: Docker Compose + multi-stage Dockerfile + Nginx reverse proxy

---

## 📁 System Architecture Directory

```
nis_hospital_management_system/
├── backend/                  # Laravel 12 REST API Codebase
│   ├── app/
│   │   ├── Http/Controllers/ # API endpoints controllers
│   │   ├── Http/Middleware/  # RBAC and Audit logging middlewares
│   │   ├── Models/           # Eloquent Database models
│   │   ├── Repositories/     # Clean repository layer contracts
│   │   └── Services/         # Orchestrating business services & transactions
│   ├── database/
│   │   ├── migrations/       # Normalized 3NF PostgreSQL migrations
│   │   └── seeders/          # Pre-populating default roles and sample data
│   └── routes/api.php        # Secured API endpoints routing
│
├── frontend/                 # Vite + React + TS Codebase
│   ├── src/
│   │   ├── components/       # Collapsible Sidebar & Glassmorphic Navbar
│   │   ├── contexts/         # Authentication & Theme controllers
│   │   ├── pages/            # Role-based dashboards & clinical modules
│   │   └── index.css         # Styling directives and custom Tailwind variables
│
├── docs/                     # Architecture & ERD Specifications
│   ├── architecture_diagrams.md
│   ├── erd_diagram.md
│   ├── api_specification.md
│   ├── security_documentation.md
│   ├── installation_deployment_guide.md
│   └── user_administrator_manuals.md
│
├── docker-compose.yml        # Docker Multi-container Orchestration
├── Dockerfile                # PHP 8.4-FPM production image specification
└── nginx.conf                # Nginx reverse proxy config file
```

---

## ⚡ Quick Start Command Guides

### 1. Backend REST API Server
```bash
cd backend
php artisan serve
```
Running on: `http://127.0.0.1:8000`.

### 2. Frontend Portal Server
```bash
cd frontend
npm run dev
```
Running on: `http://localhost:5173`.

### 3. Run Automated Tests
```bash
cd backend
php vendor/bin/phpunit
```

---

## 🛡️ Security Credentials & Seed User Profiles

The database seeder pre-populates the following credentials for immediate system exploration:

- **Super Administrator (ICT Admin)**:
  - Email: `admin@nishms.gov.ng`
  - Password: `Password123#`
- **Medical Director (Executive)**:
  - Email: `director@nishms.gov.ng`
  - Password: `Password123#`
- **Doctor (Clinical)**:
  - Email: `doctor@nishms.gov.ng`
  - Password: `Password123#`
- **Nurse (Triage)**:
  - Email: `nurse@nishms.gov.ng`
  - Password: `Password123#`
- **Pharmacist (Medicine)**:
  - Email: `pharmacist@nishms.gov.ng`
  - Password: `Password123#`
- **Laboratory Scientist (Diagnostics)**:
  - Email: `lab@nishms.gov.ng`
  - Password: `Password123#`
- **Cashier (Accounts)**:
  - Email: `cashier@nishms.gov.ng`
  - Password: `Password123#`
- **Medical Records Officer (Demographics)**:
  - Email: `records@nishms.gov.ng`
  - Password: `Password123#`
