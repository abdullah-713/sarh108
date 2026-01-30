<?php

namespace Database\Seeders;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (config('app.is_demo')) {
            $this->call([
                // Core system seeders
                PermissionSeeder::class,
                RoleSeeder::class,
                DefaultSuperAdminSeeder::class,
                DefaultCompanySeeder::class,
                DefaultCompanyUserSeeder::class,
                CurrencySeeder::class,
                EmailTemplateSeeder::class,

                // HRM module seeders
                BranchSeeder::class,
                DepartmentSeeder::class,
                DesignationSeeder::class,
                DocumentTypeSeeder::class,
                EmployeeSeeder::class,
                AwardTypeSeeder::class,
                AwardSeeder::class,
                PromotionSeeder::class,
                ResignationSeeder::class,
                TerminationSeeder::class,
                WarningSeeder::class,
                TripSeeder::class,
                ComplaintSeeder::class,
                EmployeeTransferSeeder::class,
                HolidaySeeder::class,
                AnnouncementSeeder::class,
                AssetTypeSeeder::class,
                AssetSeeder::class,

                // Performance Module Seeders
                PerformanceIndicatorCategorySeeder::class,
                PerformanceIndicatorSeeder::class,
                GoalTypeSeeder::class,
                EmployeeGoalSeeder::class,
                ReviewCycleSeeder::class,
                EmployeeReviewSeeder::class,

                // Contract Management Seeders
                ContractTypeSeeder::class,
                EmployeeContractSeeder::class,
                ContractRenewalSeeder::class,
                ContractTemplateSeeder::class,

                // Document Management Seeders
                DocumentCategorySeeder::class,
                HrDocumentSeeder::class,
                DocumentAcknowledgmentSeeder::class,
                DocumentTemplateSeeder::class,

                // Leave management Seeders
                LeaveTypeSeeder::class,
                LeavePolicySeeder::class,
                LeaveApplicationSeeder::class,
                LeaveBalanceSeeder::class,

                // Attendance Management Seeders
                ShiftSeeder::class,
                AttendancePolicySeeder::class,
                AttendanceRecordSeeder::class,
                AttendanceRegularizationSeeder::class,

                // Time Tracking Seeders
                TimeEntrySeeder::class,
            ]);
        } else {
            $this->call([
                PermissionSeeder::class,
                RoleSeeder::class,
                DefaultSuperAdminSeeder::class,
                DefaultCompanySeeder::class,
                DefaultCompanyUserSeeder::class,
                CurrencySeeder::class,
                EmailTemplateSeeder::class,
            ]);
        }
    }
}
