<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Staff;
use App\Models\Patient;
use App\Models\Ward;
use App\Models\Bed;
use App\Models\PharmacyItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            'super_admin' => 'Super Administrator',
            'hospital_admin' => 'Hospital Administrator',
            'medical_director' => 'Medical Director',
            'chief_medical_officer' => 'Chief Medical Officer',
            'doctor' => 'Doctor',
            'consultant' => 'Consultant',
            'nurse' => 'Nurse',
            'pharmacist' => 'Pharmacist',
            'lab_scientist' => 'Laboratory Scientist',
            'radiographer' => 'Radiographer',
            'records_officer' => 'Medical Records Officer',
            'cashier' => 'Cashier',
            'account_officer' => 'Account Officer',
            'receptionist' => 'Receptionist',
            'health_info_officer' => 'Health Information Officer',
            'store_officer' => 'Store Officer',
            'inventory_officer' => 'Inventory Officer',
            'procurement_officer' => 'Procurement Officer',
            'hr_officer' => 'HR Officer',
            'ict_admin' => 'ICT Administrator',
            'ambulance_officer' => 'Ambulance Officer',
            'ward_manager' => 'Ward Manager',
            'theatre_manager' => 'Theatre Manager',
            'dental_officer' => 'Dental Officer',
            'eye_clinic_officer' => 'Eye Clinic Officer',
            'physiotherapist' => 'Physiotherapist',
            'staff' => 'Staff',
            'patient' => 'Patient'
        ];

        $roleModels = [];
        foreach ($roles as $key => $displayName) {
            $roleModels[$key] = Role::create([
                'name' => $key,
                'display_name' => $displayName,
                'description' => "Official role for $displayName inside the Nigeria Immigration Service Hospitals"
            ]);
        }

        // 2. Seed Permissions
        $permissions = [
            // Admin Panel
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'module' => 'admin'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'module' => 'admin'],
            ['name' => 'view_audit_logs', 'display_name' => 'View Audit Logs', 'module' => 'admin'],
            
            // Clinical Operations
            ['name' => 'view_patients', 'display_name' => 'View Patients', 'module' => 'clinical'],
            ['name' => 'register_patients', 'display_name' => 'Register Patients', 'module' => 'clinical'],
            ['name' => 'consult_patients', 'display_name' => 'Consult Patients', 'module' => 'clinical'],
            ['name' => 'nursing_vitals', 'display_name' => 'Record Vital Signs', 'module' => 'clinical'],
            ['name' => 'ward_observations', 'display_name' => 'Ward Observations', 'module' => 'clinical'],
            
            // Diagnostics
            ['name' => 'request_tests', 'display_name' => 'Request Tests', 'module' => 'diagnostics'],
            ['name' => 'fill_lab_results', 'display_name' => 'Fill Laboratory Results', 'module' => 'diagnostics'],
            ['name' => 'fill_radiology_results', 'display_name' => 'Fill Radiology Results', 'module' => 'diagnostics'],
            ['name' => 'approve_diagnostics', 'display_name' => 'Approve Diagnostic Reports', 'module' => 'diagnostics'],
            
            // Pharmacy & Stock
            ['name' => 'view_inventory', 'display_name' => 'View Inventory', 'module' => 'pharmacy'],
            ['name' => 'manage_inventory', 'display_name' => 'Manage Stock & Procurement', 'module' => 'pharmacy'],
            ['name' => 'dispense_drugs', 'display_name' => 'Dispense Prescriptions', 'module' => 'pharmacy'],
            
            // Financials
            ['name' => 'create_invoices', 'display_name' => 'Create Invoices', 'module' => 'finance'],
            ['name' => 'collect_payments', 'display_name' => 'Collect Payments', 'module' => 'finance'],
            ['name' => 'view_revenue_reports', 'display_name' => 'View Revenue Reports', 'module' => 'finance'],
            
            // Executives
            ['name' => 'view_executive_dashboard', 'display_name' => 'View Executive Dashboard', 'module' => 'executive']
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['name']] = Permission::create($p);
        }

        // Link permissions to roles
        // Super admin, ICT admin, and Hospital admin get everything
        $allPermissions = Permission::all();
        $roleModels['super_admin']->permissions()->sync($allPermissions);
        $roleModels['ict_admin']->permissions()->sync($allPermissions);
        $roleModels['hospital_admin']->permissions()->sync($allPermissions);

        // Doctor permissions
        $doctorPermissions = [
            $permissionModels['view_patients']->id,
            $permissionModels['consult_patients']->id,
            $permissionModels['request_tests']->id,
        ];
        $roleModels['doctor']->permissions()->sync($doctorPermissions);
        
        // Consultant permissions (same as doctor)
        $roleModels['consultant']->permissions()->sync($doctorPermissions);
        
        // Dental Officer permissions (same as doctor)
        $roleModels['dental_officer']->permissions()->sync($doctorPermissions);

        // Eye Clinic Officer permissions (same as doctor)
        $roleModels['eye_clinic_officer']->permissions()->sync($doctorPermissions);

        // Physiotherapist permissions (same as doctor but without request tests)
        $roleModels['physiotherapist']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['consult_patients']->id,
        ]);

        // Nurse permissions
        $nursePermissions = [
            $permissionModels['view_patients']->id,
            $permissionModels['nursing_vitals']->id,
            $permissionModels['ward_observations']->id,
        ];
        $roleModels['nurse']->permissions()->sync($nursePermissions);
        
        // Ward Manager permissions (same as nurse)
        $roleModels['ward_manager']->permissions()->sync($nursePermissions);

        // Theatre Manager permissions
        $roleModels['theatre_manager']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['ward_observations']->id,
        ]);

        // Pharmacist permissions
        $pharmacistPermissions = [
            $permissionModels['view_inventory']->id,
            $permissionModels['dispense_drugs']->id,
            $permissionModels['manage_inventory']->id,
        ];
        $roleModels['pharmacist']->permissions()->sync($pharmacistPermissions);
        
        // Store Officer, Inventory Officer, Procurement Officer permissions
        $roleModels['store_officer']->permissions()->sync([$permissionModels['view_inventory']->id]);
        $roleModels['inventory_officer']->permissions()->sync([$permissionModels['view_inventory']->id, $permissionModels['manage_inventory']->id]);
        $roleModels['procurement_officer']->permissions()->sync([$permissionModels['view_inventory']->id, $permissionModels['manage_inventory']->id]);

        // Lab Scientist permissions
        $roleModels['lab_scientist']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['fill_lab_results']->id,
            $permissionModels['approve_diagnostics']->id,
        ]);

        // Radiographer permissions
        $roleModels['radiographer']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['fill_radiology_results']->id,
            $permissionModels['approve_diagnostics']->id,
        ]);

        // Cashier & Account Officer permissions
        $cashierPermissions = [
            $permissionModels['create_invoices']->id,
            $permissionModels['collect_payments']->id,
            $permissionModels['view_revenue_reports']->id,
        ];
        $roleModels['cashier']->permissions()->sync($cashierPermissions);
        $roleModels['account_officer']->permissions()->sync($cashierPermissions);

        // Records Officer & Receptionist permissions
        $recordsPermissions = [
            $permissionModels['view_patients']->id,
            $permissionModels['register_patients']->id,
        ];
        $roleModels['records_officer']->permissions()->sync($recordsPermissions);
        $roleModels['receptionist']->permissions()->sync($recordsPermissions);

        // Health Information Officer permissions
        $roleModels['health_info_officer']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['view_audit_logs']->id,
        ]);

        // Medical Director / Chief Medical Officer / Executive permissions
        $execPermissions = [
            $permissionModels['view_executive_dashboard']->id,
            $permissionModels['view_patients']->id,
            $permissionModels['view_revenue_reports']->id,
        ];
        $roleModels['medical_director']->permissions()->sync($execPermissions);
        $roleModels['chief_medical_officer']->permissions()->sync($execPermissions);

        // Ambulance Officer permissions
        $roleModels['ambulance_officer']->permissions()->sync([
            $permissionModels['view_patients']->id,
            $permissionModels['nursing_vitals']->id,
        ]);

        // HR Officer permissions
        $roleModels['hr_officer']->permissions()->sync([
            $permissionModels['manage_users']->id,
        ]);

        // Staff & Patient permissions (basic view)
        $roleModels['staff']->permissions()->sync([$permissionModels['view_patients']->id]);
        $roleModels['patient']->permissions()->sync([$permissionModels['view_patients']->id]);

        // 3. Seed Departments
        $departments = [
            ['name' => 'General Outpatient', 'code' => 'GOPD', 'description' => 'General Outpatient Department'],
            ['name' => 'Emergency Unit', 'code' => 'ER', 'description' => 'Accident and Emergency Department'],
            ['name' => 'Dental Clinic', 'code' => 'DENT', 'description' => 'Dental Care and Services Unit'],
            ['name' => 'Eye Clinic', 'code' => 'EYE', 'description' => 'Ophthalmology Unit'],
            ['name' => 'Paediatrics Ward', 'code' => 'PAED', 'description' => 'Child Health and Development Department'],
            ['name' => 'Obstetrics & Gynaecology', 'code' => 'OBG', 'description' => 'Maternity and Women Health Department'],
            ['name' => 'Cardiology Unit', 'code' => 'CARD', 'description' => 'Heart Health and Diagnostics Department'],
            ['name' => 'Radiology Department', 'code' => 'RAD', 'description' => 'Imaging, X-ray, Ultrasound and MRI Services'],
            ['name' => 'Laboratory Unit', 'code' => 'LAB', 'description' => 'Medical Pathology Lab and Analysis'],
            ['name' => 'Pharmacy Unit', 'code' => 'PHARM', 'description' => 'Drug dispensing and medication unit'],
            ['name' => 'ICT Unit', 'code' => 'ICT', 'description' => 'Information and Communication Technology Unit'],
            ['name' => 'Administration & HR', 'code' => 'ADMIN', 'description' => 'Hospital Admin, Logistics, and Management']
        ];

        $deptModels = [];
        foreach ($departments as $d) {
            $deptModels[$d['code']] = Department::create($d);
        }

        // 4. Seed Users, Staff, and Role links
        $accounts = [
            [
                'name' => 'Admin Officer',
                'email' => 'admin@nishms.gov.ng',
                'role' => 'super_admin',
                'dept' => 'ICT',
                'first_name' => 'Yakubu',
                'last_name' => 'Musa',
                'rank' => 'Deputy Superintendent of Immigration (DSI)',
                'service_number' => 'NIS/2012/2839'
            ],
            [
                'name' => 'Dr. Ibrahim Bello',
                'email' => 'director@nishms.gov.ng',
                'role' => 'medical_director',
                'dept' => 'ADMIN',
                'first_name' => 'Ibrahim',
                'last_name' => 'Bello',
                'rank' => 'Comptroller of Immigration (Medical)',
                'service_number' => 'NIS/2005/1029',
                'license' => 'MDCN/D/73829'
            ],
            [
                'name' => 'Dr. Chioma Nwachukwu',
                'email' => 'doctor@nishms.gov.ng',
                'role' => 'doctor',
                'dept' => 'GOPD',
                'first_name' => 'Chioma',
                'last_name' => 'Nwachukwu',
                'rank' => 'Superintendent of Immigration (SI)',
                'service_number' => 'NIS/2018/4820',
                'license' => 'MDCN/D/89201'
            ],
            [
                'name' => 'Nurse Funmilayo Adebayo',
                'email' => 'nurse@nishms.gov.ng',
                'role' => 'nurse',
                'dept' => 'ER',
                'first_name' => 'Funmilayo',
                'last_name' => 'Adebayo',
                'rank' => 'Deputy Superintendent of Immigration (DSI)',
                'service_number' => 'NIS/2014/3748',
                'license' => 'NMCN/R/92839'
            ],
            [
                'name' => 'Pharmacist Aliyu Garba',
                'email' => 'pharmacist@nishms.gov.ng',
                'role' => 'pharmacist',
                'dept' => 'PHARM',
                'first_name' => 'Aliyu',
                'last_name' => 'Garba',
                'rank' => 'Assistant Superintendent of Immigration I (ASI-I)',
                'service_number' => 'NIS/2020/5839',
                'license' => 'PCN/P/29302'
            ],
            [
                'name' => 'Scientist Emeka Okafor',
                'email' => 'lab@nishms.gov.ng',
                'role' => 'lab_scientist',
                'dept' => 'LAB',
                'first_name' => 'Emeka',
                'last_name' => 'Okafor',
                'rank' => 'Assistant Superintendent of Immigration I (ASI-I)',
                'service_number' => 'NIS/2021/6920',
                'license' => 'MLSCN/L/10293'
            ],
            [
                'name' => 'Cashier Zainab Ahmed',
                'email' => 'cashier@nishms.gov.ng',
                'role' => 'cashier',
                'dept' => 'ADMIN',
                'first_name' => 'Zainab',
                'last_name' => 'Ahmed',
                'rank' => 'Inspector of Immigration (II)',
                'service_number' => 'NIS/2022/7901'
            ],
            [
                'name' => 'Records Officer John Danjuma',
                'email' => 'records@nishms.gov.ng',
                'role' => 'records_officer',
                'dept' => 'ADMIN',
                'first_name' => 'John',
                'last_name' => 'Danjuma',
                'rank' => 'Assistant Inspector of Immigration (AII)',
                'service_number' => 'NIS/2023/8912'
            ]
        ];

        foreach ($accounts as $acc) {
            $user = User::create([
                'name' => $acc['name'],
                'email' => $acc['email'],
                'password' => Hash::make('Password123#'), // Secure default seed password
                'status' => 'active'
            ]);

            // Assign role
            $user->roles()->attach($roleModels[$acc['role']]->id);

            // Create Staff details
            Staff::create([
                'user_id' => $user->id,
                'department_id' => $deptModels[$acc['dept']]->id,
                'first_name' => $acc['first_name'],
                'last_name' => $acc['last_name'],
                'service_number' => $acc['service_number'],
                'rank' => $acc['rank'],
                'professional_license' => $acc['license'] ?? null,
                'phone' => '08031234567',
                'status' => 'active'
            ]);
        }

        // 5. Seed Sample Patients
        $patients = [
            [
                'first_name' => 'Babajide',
                'last_name' => 'Sanwo',
                'gender' => 'Male',
                'date_of_birth' => '1985-05-12',
                'phone' => '08055554433',
                'email' => 'babajide@mail.com',
                'address' => 'NIS Barracks, Phase II, Abuja',
                'immigration_service_number' => 'NIS/OFF/10293', // Officer Patient
                'nin' => '12345678901',
                'allergies' => 'Penicillin',
                'blood_group' => 'O+',
                'genotype' => 'AA',
                'disability' => 'None'
            ],
            [
                'first_name' => 'Amina',
                'last_name' => 'Abubakar',
                'gender' => 'Female',
                'date_of_birth' => '1992-09-20',
                'phone' => '08099887766',
                'email' => 'amina.abu@gmail.com',
                'address' => 'Gwagwalada Area, Abuja',
                'immigration_service_number' => 'NIS/DEP/77382', // Officer Dependant
                'nin' => '98765432109',
                'allergies' => 'Sulfonamides, Dust',
                'blood_group' => 'A+',
                'genotype' => 'AS',
                'disability' => 'None'
            ],
            [
                'first_name' => 'Oluwaseun',
                'last_name' => 'Adekunle',
                'gender' => 'Male',
                'date_of_birth' => '2001-11-04',
                'phone' => '07033445566',
                'email' => 'seun.ade@yahoo.com',
                'address' => 'Lugbe Housing Estate, Abuja',
                'immigration_service_number' => null, // Civilian Patient
                'nin' => '45678912304',
                'allergies' => 'None',
                'blood_group' => 'B+',
                'genotype' => 'AA',
                'disability' => 'None'
            ]
        ];

        foreach ($patients as $p) {
            Patient::create($p);
        }

        // 6. Seed Wards & Beds
        $wards = [
            ['name' => 'Male General Ward', 'description' => 'In-patient ward for male adults'],
            ['name' => 'Female General Ward', 'description' => 'In-patient ward for female adults'],
            ['name' => 'Pediatric Ward', 'description' => 'Ward for infant and child recovery'],
            ['name' => 'Intensive Care Unit (ICU)', 'description' => 'High-monitoring clinical recovery room']
        ];

        foreach ($wards as $w) {
            $ward = Ward::create($w);
            // Create 5 beds in each ward
            for ($i = 1; $i <= 5; $i++) {
                Bed::create([
                    'ward_id' => $ward->id,
                    'bed_number' => "Bed-0" . $i,
                    'status' => 'available'
                ]);
            }
        }

        // 7. Seed Pharmacy Items (Drug Inventory)
        $pharmacyItems = [
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Acetaminophen',
                'code' => 'DRG-PARA-500',
                'category' => 'Analgesics',
                'batch_number' => 'BCH-2026-001',
                'expiry_date' => '2028-12-31',
                'quantity_in_stock' => 1500,
                'reorder_level' => 100,
                'price_per_unit' => 20.00
            ],
            [
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin Trihydrate',
                'code' => 'DRG-AMOX-500',
                'category' => 'Antibiotics',
                'batch_number' => 'BCH-2026-002',
                'expiry_date' => '2027-06-30',
                'quantity_in_stock' => 800,
                'reorder_level' => 50,
                'price_per_unit' => 50.00
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'code' => 'DRG-IBU-400',
                'category' => 'NSAIDs',
                'batch_number' => 'BCH-2026-003',
                'expiry_date' => '2028-03-15',
                'quantity_in_stock' => 1200,
                'reorder_level' => 80,
                'price_per_unit' => 30.00
            ],
            [
                'name' => 'Artemether/Lumefantrine 80/480mg',
                'generic_name' => 'ACT (Artemisinin-based Combination Therapy)',
                'code' => 'DRG-ACT-80',
                'category' => 'Antimalarials',
                'batch_number' => 'BCH-2026-004',
                'expiry_date' => '2027-10-31',
                'quantity_in_stock' => 350,
                'reorder_level' => 30,
                'price_per_unit' => 1200.00
            ]
        ];

        foreach ($pharmacyItems as $item) {
            PharmacyItem::create($item);
        }
    }
}
