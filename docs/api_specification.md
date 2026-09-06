# API Specification

This document details the RESTful API endpoints available in the NIS HMS Laravel backend.

## Authentication Endpoints

### 1. User Login
- **URL**: `/api/login`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "email": "doctor@immigration.gov.ng",
    "password": "Password123#"
  }
  ```
- **Response (MFA Enabled)**:
  ```json
  {
    "mfa_required": true,
    "email": "doctor@immigration.gov.ng",
    "message": "Multi-Factor Authentication code has been sent to your registered email."
  }
  ```
- **Response (MFA Disabled)**:
  ```json
  {
    "access_token": "1|sanctum_token_string",
    "token_type": "Bearer",
    "user": { ... }
  }
  ```

### 2. Verify MFA
- **URL**: `/api/verify-mfa`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "email": "doctor@immigration.gov.ng",
    "code": "123456"
  }
  ```

---

## Clinical Endpoints

### 1. Register Patient File
- **URL**: `/api/patients`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**:
  ```json
  {
    "first_name": "Amina",
    "last_name": "Abubakar",
    "gender": "Female",
    "date_of_birth": "1992-09-20",
    "phone": "08099887766",
    "address": "Abuja HQ",
    "immigration_service_number": "NIS/DEP/77382",
    "nin": "98765432109"
  }
  ```

### 2. Record Triage Vitals
- **URL**: `/api/clinical/vitals`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**:
  ```json
  {
    "patient_id": 1,
    "department_id": 1,
    "vitals_blood_pressure": "120/80",
    "vitals_temperature": 36.8,
    "vitals_pulse_rate": 72
  }
  ```

### 3. Record Consultation (SOAP)
- **URL**: `/api/clinical/consult/{visitId}`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**:
  ```json
  {
    "chief_complaint": "Persistent headache",
    "soap_notes_subjective": "Patient reports severe head throbbing...",
    "soap_notes_objective": "Blood pressure normal, pupil reflexes active...",
    "soap_notes_assessment": "Migraine cephalalgia...",
    "soap_notes_plan": "Prescribed analgesics and ordered rest...",
    "diagnosis_icd10": "G43.9",
    "diagnosis_description": "Migraine, unspecified",
    "prescriptions": [
      {
        "drug_name": "Paracetamol 500mg",
        "dosage": "2 tabs",
        "frequency": "TDS",
        "duration_days": 5,
        "quantity_prescribed": 30
      }
    ]
  }
  ```
