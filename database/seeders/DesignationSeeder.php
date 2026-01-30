<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all companies
        $companies = User::where('type', 'company')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('No company users found. Please run DefaultCompanySeeder first.');
            return;
        }

        // Designation names and descriptions by department type
        $designationsByDepartment = [
            'مركز الصيانة الرئيسي' => [
                ['name' => 'مدير الصيانة', 'description' => 'يشرف على عمليات الصيانة والإصلاح والجودة'],
                ['name' => 'فني صيانة أول', 'description' => 'متخصص في صيانة وإصلاح السيارات ذو خبرة عالية'],
                ['name' => 'فني صيانة', 'description' => 'يقوم بعمليات الصيانة الأساسية والإصلاحات'],
                ['name' => 'مساعد فني', 'description' => 'يساعد الفنيين ويقوم بأعمال الدعم الفنية']
            ],
            'مركز الصيانة الموسمية' => [
                ['name' => 'مشرف الصيانة الموسمية', 'description' => 'يشرف على الصيانة الدورية والموسمية'],
                ['name' => 'فني الصيانة الدورية', 'description' => 'متخصص في الصيانة الدورية والتشخيص'],
            ],
            'قسم الإصلاحات الكهربائية' => [
                ['name' => 'مهندس كهربائي', 'description' => 'متخصص في الأنظمة الكهربائية والإلكترونية'],
                ['name' => 'فني كهربائي', 'description' => 'يقوم بإصلاح وصيانة الأنظمة الكهربائية'],
            ],
            'قسم الإصلاحات الميكانيكية' => [
                ['name' => 'مهندس ميكانيكي', 'description' => 'متخصص في إصلاح المحركات والأنظمة الميكانيكية'],
                ['name' => 'فني ميكانيكي', 'description' => 'يقوم بإصلاح وصيانة الأجزاء الميكانيكية'],
            ],
            'قسم تجميل السيارات' => [
                ['name' => 'متخصص تجميل السيارات', 'description' => 'متخصص في تنظيف وتجميل وحماية الطلاء'],
                ['name' => 'عامل تنظيف', 'description' => 'يقوم بتنظيف وتحضير السيارات'],
            ],
            'قسم الاستقبال والاستشارات' => [
                ['name' => 'موظف الاستقبال والاستشارات', 'description' => 'يستقبل العملاء ويقدم الاستشارات الفنية'],
                ['name' => 'مسئول الخدمة', 'description' => 'يدير طلبات العملاء وتتبع الخدمات'],
            ],
            'قسم إدارة المستودع' => [
                ['name' => 'مدير المستودع', 'description' => 'يدير مستودع قطع الغيار والمواد'],
                ['name' => 'موظف مستودع', 'description' => 'يتولى تنظيم وترتيب وحفظ قطع الغيار'],
            ],
            'الإدارة والموارد البشرية' => [
                ['name' => 'مدير عام', 'description' => 'المسؤول الأول عن إدارة الشركة'],
                ['name' => 'مسئول إدارة', 'description' => 'يتولى المهام الإدارية والموارد البشرية'],
                ['name' => 'محاسب', 'description' => 'يتولى العمليات المحاسبية والمالية'],
            ],
            'Operations' => [],
            'Customer Service' => [],
            'Research & Development' => [],
            'Legal' => [],
            'Administration' => []
        ];

        foreach ($companies as $company) {
            // Get all departments for this company
            $departments = Department::where('created_by', $company->id)->get();

            if ($departments->isEmpty()) {
                $this->command->warn('No departments found for company: ' . $company->name . '. Please run DepartmentSeeder first.');
                continue;
            }

            foreach ($departments as $department) {
                // Get designations for this department type
                $designations = $designationsByDepartment[$department->name] ?? [
                    ['name' => 'Manager', 'description' => 'Manages department operations and oversees team performance'],
                    ['name' => 'Executive', 'description' => 'Executes departmental tasks and supports management activities'],
                    ['name' => 'Assistant', 'description' => 'Provides administrative support and assists in daily operations']
                ];

                // Create 1-3 designations for each department
                $designationCount = rand(1, min(3, count($designations)));

                for ($i = 0; $i < $designationCount; $i++) {
                    $designation = $designations[$i];
                    $designationName = $designation['name'];
                    $designationDescription = $designation['description'];

                    // Check if designation already exists for this department
                    if (Designation::where('name', $designationName)->where('department_id', $department->id)->exists()) {
                        continue;
                    }

                    try {
                        Designation::create([
                            'name' => $designationName,
                            'department_id' => $department->id,
                            'description' => $designationDescription,
                            'status' => 'active',
                            'created_by' => $company->id,
                        ]);
                    } catch (\Exception $e) {
                        $this->command->error('Failed to create designation: ' . $designationName . ' for department: ' . $department->name);
                        continue;
                    }
                }
            }
        }

        $this->command->info('Designation seeder completed successfully!');
    }
}
