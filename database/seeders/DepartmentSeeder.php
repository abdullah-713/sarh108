<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DepartmentSeeder extends Seeder
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

        // Department names with descriptions
        $departments = [
            ['name' => 'مركز الصيانة الرئيسي', 'description' => 'المركز الرئيسي لصيانة وإصلاح السيارات'],
            ['name' => 'مركز الصيانة الموسمية', 'description' => 'قسم متخصص في الصيانة الدورية والموسمية'],
            ['name' => 'قسم الإصلاحات الكهربائية', 'description' => 'متخصص في إصلاح الأنظمة الكهربائية والبطاريات'],
            ['name' => 'قسم الإصلاحات الميكانيكية', 'description' => 'متخصص في إصلاح المحركات وأنظمة الحركة'],
            ['name' => 'قسم تجميل السيارات', 'description' => 'قسم تنظيف وتجميل وحماية الطلاء'],
            ['name' => 'قسم الاستقبال والاستشارات', 'description' => 'قسم استقبال العملاء وتقديم الاستشارات الفنية'],
            ['name' => 'قسم إدارة المستودع', 'description' => 'إدارة مستودع قطع الغيار والمواد'],
            ['name' => 'الإدارة والموارد البشرية', 'description' => 'إدارة العمليات والموارد البشرية'],
        ];

        foreach ($companies as $company) {
            // Get all branches for this company
            $branches = Branch::where('created_by', $company->id)->get();

            if ($branches->isEmpty()) {
                $this->command->warn('No branches found for company: ' . $company->name . '. Please run BranchSeeder first.');
                continue;
            }

            foreach ($branches as $branch) {
                // Create 5-8 departments for each branch
                $departmentCount = rand(2,3);

                for ($i = 0; $i < $departmentCount; $i++) {
                    $department = $departments[$i];
                    $departmentName = $department['name'];
                    $departmentDescription = $department['description'];

                    // Check if department already exists for this branch
                    if (Department::where('name', $departmentName)->where('branch_id', $branch->id)->exists()) {
                        continue;
                    }

                    try {
                        Department::create([
                            'name' => $departmentName,
                            'branch_id' => $branch->id,
                            'description' => $departmentDescription,
                            'status' => 'active',
                            'created_by' => $company->id,
                        ]);
                    } catch (\Exception $e) {
                        $this->command->error('Failed to create department: ' . $departmentName . ' for branch: ' . $branch->name);
                        continue;
                    }
                }
            }
        }

        $this->command->info('Department seeder completed successfully!');
    }
}
