# Nigeria Immigration Service Hospital Management System (NIS HMS)

An enterprise-grade, world-class Hospital Management System designed for the **Nigeria Immigration Service (NIS)**. The application digitizes and coordinates all medical operations, covering patient records, appointments, nursing triage, consultations, pharmacy inventory, lab/radiology diagnostics, billing cashpoints, audit logs, and executive analytics.

---

## 🌟 Latest Updates

- **Monolithic Architecture Transition**: The system has been fully migrated from a separated React frontend to a high-performance **Laravel Blade + Tailwind CSS** monolithic architecture, improving load times and simplifying the deployment process.
- **Enterprise Security Hardening**: Implemented advanced security measures including Strict API Rate Limiting, XSS Payload Sanitization (`XssSanitizer`), SafeSecurityHeaders middleware, encrypted session payloads, hardened Eloquent models, and strict hospital-code-only restrictions for patient lookups.
- **Enhanced Clinical Workflows**: Integrated real-time "Diagnostic Reports" directly into the patient's comprehensive Clinical File History, allowing Doctors to securely view approved lab and radiology results independent of the active triage queue.
- **Codebase Optimization**: Redundant development scratch files, boilerplate test scripts, and the entirely obsolete React codebase were purged to create a lightweight, production-ready environment.

---

## 🚀 Technologies Stack

### Application Core (Monolith)
- **Framework**: Laravel 12 + PHP 8.4
- **Frontend UI**: Laravel Blade templating engine with **Vanilla JavaScript (ES6+)** for reactive, DOM-based interactivity without virtual DOM overhead
- **Styling**: Tailwind CSS v4 + Outfit Google Fonts
- **Icons**: Lucide Icons for premium visual aesthetics

### Security & Architecture
- **Auth & Protection**: Laravel Sanctum tokens, Custom RBAC Verification, and automated Audit Trail capture.
- **Data Protection**: Encrypted sessions, advanced XSS/CSRF mitigation, strict request throttling.
- **Architecture**: Repository Pattern + Service Layer + DTO validations.

### Database & Deployments
- **Database**: PostgreSQL 15 (normalized to 3NF)
- **Caching**: Redis Cache
- **Orchestration**: Docker Compose + multi-stage Dockerfile + Nginx reverse proxy

---

## 📁 System Architecture Directory

```
nis_hospital_management_system/
├── backend/                  # Laravel 12 Monolithic Codebase
│   ├── app/
│   │   ├── Http/Controllers/ # Web and API endpoints controllers
│   │   ├── Http/Middleware/  # RBAC, Security Headers, XSS and Audit logging middlewares
│   │   ├── Models/           # Hardened Eloquent Database models
│   │   ├── Repositories/     # Clean repository layer contracts
│   │   └── Services/         # Orchestrating business services & transactions
│   ├── database/
│   │   ├── migrations/       # Normalized 3NF PostgreSQL migrations
│   │   └── seeders/          # Pre-populating default roles and sample data
│   ├── resources/
│   │   └── views/            # Laravel Blade templates and UI logic
│   ├── routes/
│   │   ├── api.php           # Secured API endpoints
│   │   └── web.php           # Web application routing
│   └── public/               # Public assets and entry point
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

### 1. Start the Application Server
Since the system is now a streamlined monolith, you only need to run the Laravel development server.

```bash
cd backend
php artisan serve
```
Running on: `http://127.0.0.1:8000`.

### 2. Run Automated Tests
```bash
cd backend
php artisan test
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
