# Installation & Deployment Guide

This document describes how to set up the Nigeria Immigration Service Hospital Management System (NIS HMS) for local development and production.

## Prerequisites

- **Docker** and **Docker Compose**
- **Node.js** (v20+ recommended)
- **Composer** (v2.6+ for backend dependencies)
- **PHP** (v8.4+ for local commands without containers)

---

## Local Setup with Docker Compose

1. **Clone the Repository** and navigate to the project directory:
   ```bash
   cd nis_hospital_management_system
   ```

2. **Backend Configuration**:
   Copy the example environment file and configure parameters:
   ```bash
   cp backend/.env.example backend/.env
   ```

3. **Spin up Containers**:
   ```bash
   docker-compose up -d --build
   ```

4. **Run Migrations & Seed Database**:
   Log into the application container and seed:
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

5. **Start Frontend Dev Server**:
   Navigate to the frontend folder, install dependencies, and run:
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
   Access the frontend at `http://localhost:5173`.

---

## Production Deployment Checklist

- **Reverse Proxy**: Use the Nginx service configuration to reverse-proxy frontend build assets and forward `/api` requests to PHP-FPM container.
- **SSL Certificates**: Enforce HTTPS on Nginx using Let's Encrypt or corporate cloud certificates.
- **PostgreSQL Backups**: Configure pg_dump cron jobs to create daily incremental backups.
- **Redis Sessions**: Ensure the Redis server is isolated and secured with passwords.
