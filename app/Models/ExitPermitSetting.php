<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitPermitSetting extends Model
{
    use HasFactory;

    protected $table = 'exit_permit_settings';

    protected $fillable = [
        'company_id',
        'require_approval',
        'max_permits_per_day',
        'max_permits_per_month',
        'max_duration_minutes',
        'min_advance_hours',
        'allow_same_day_request',
        'notify_manager',
        'notify_hr',
        'auto_approve_official',
        'exempt_employees',
        'exempt_designations',
    ];

    protected $casts = [
        'require_approval' => 'boolean',
        'allow_same_day_request' => 'boolean',
        'notify_manager' => 'boolean',
        'notify_hr' => 'boolean',
        'auto_approve_official' => 'boolean',
        'exempt_employees' => 'array',
        'exempt_designations' => 'array',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Get or create default settings
    public static function getForCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            [
                'require_approval' => true,
                'max_permits_per_day' => 1,
                'max_permits_per_month' => 5,
                'max_duration_minutes' => 120,
                'min_advance_hours' => 1,
                'allow_same_day_request' => true,
                'notify_manager' => true,
                'notify_hr' => false,
            ]
        );
    }

    // Check if employee is exempt
    public function isEmployeeExempt(Employee $employee): bool
    {
        if (!empty($this->exempt_employees) && in_array($employee->id, $this->exempt_employees)) {
            return true;
        }

        if (!empty($this->exempt_designations) && in_array($employee->designation_id, $this->exempt_designations)) {
            return true;
        }

        return false;
    }

    // Validate permit request
    public function validateRequest(Employee $employee, string $permitType, int $durationMinutes): array
    {
        $errors = [];

        // Check if exempt
        if ($this->isEmployeeExempt($employee)) {
            return [];
        }

        // Check max permits per day
        $todayCount = ExitPermit::countForEmployee($employee->id, 'day');
        if ($todayCount >= $this->max_permits_per_day) {
            $errors[] = 'تجاوزت الحد الأقصى للتصاريح اليومية';
        }

        // Check max permits per month
        $monthCount = ExitPermit::countForEmployee($employee->id, 'month');
        if ($monthCount >= $this->max_permits_per_month) {
            $errors[] = 'تجاوزت الحد الأقصى للتصاريح الشهرية';
        }

        // Check duration
        if ($durationMinutes > $this->max_duration_minutes) {
            $errors[] = 'المدة تتجاوز الحد المسموح (' . $this->max_duration_minutes . ' دقيقة)';
        }

        return $errors;
    }
}
