# Entity Relationship Diagram (ERD)

This document shows the database structure and entity relationships of the NIS HMS PostgreSQL schema.

```mermaid
erDiagram
    users ||--o{ role_user : "has roles"
    roles ||--o{ role_user : "assigned"
    roles ||--o{ permission_role : "has permissions"
    permissions ||--o{ permission_role : "assigned"
    
    users ||--o| staff : "owns profile"
    departments ||--o{ staff : "employs"
    
    patients ||--o{ appointments : "books"
    staff ||--o{ appointments : "attends"
    departments ||--o{ appointments : "belongs to"
    
    patients ||--o{ visits : "makes"
    staff ||--o{ visits : "consults"
    
    visits ||--o| admissions : "triggers"
    beds ||--o{ admissions : "allocates"
    wards ||--o{ beds : "contains"
    
    visits ||--o{ prescriptions : "orders"
    prescriptions ||--o{ prescription_items : "contains"
    
    visits ||--o{ lab_requests : "requests"
    lab_requests ||--o| lab_results : "provides"
    
    visits ||--o{ radiology_requests : "requests"
    radiology_requests ||--o| radiology_results : "provides"
    
    patients ||--o{ invoices : "billed"
    invoices ||--o{ invoice_items : "contains"
    invoices ||--o{ payments : "paid"
    
    users ||--o{ audit_logs : "triggers actions"
```

## Third Normal Form (3NF) Compliance

- **No Transitive Dependencies**: Keys determine only attributes directly related to the model (e.g. `beds` belongs to a specific `ward_id`, and `bed_number` is unique to that ward).
- **Relational Integrity**: Foreign key constraints are set with cascading deletions or nullable rules (`onDelete('set null')` or `onDelete('cascade')`).
