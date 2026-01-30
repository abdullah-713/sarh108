<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'name_ar',
        'description',
        'zone_type',
        'center_latitude',
        'center_longitude',
        'radius_meters',
        'polygon_coordinates',
        'floor_number',
        'building',
        'requires_authentication',
        'track_time_in_zone',
        'min_time_minutes',
        'max_time_minutes',
        'allowed_employees',
        'allowed_departments',
        'allowed_designations',
        'is_active',
        'color',
        'display_order',
    ];

    protected $casts = [
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'polygon_coordinates' => 'array',
        'allowed_employees' => 'array',
        'allowed_departments' => 'array',
        'allowed_designations' => 'array',
        'requires_authentication' => 'boolean',
        'track_time_in_zone' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['zone_type_name'];

    // Zone types with Arabic names
    public static array $zoneTypes = [
        'indoor' => 'داخلي',
        'outdoor' => 'خارجي',
        'parking' => 'موقف سيارات',
        'gate' => 'بوابة',
        'cafeteria' => 'كافتيريا',
        'meeting' => 'غرفة اجتماعات',
        'restricted' => 'منطقة مقيدة',
        'custom' => 'مخصص',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ZoneAccessLog::class);
    }

    // Accessors
    public function getZoneTypeNameAttribute(): string
    {
        return self::$zoneTypes[$this->zone_type] ?? $this->zone_type;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    // Check if employee is allowed in zone
    public function isEmployeeAllowed(Employee $employee): bool
    {
        // If no restrictions, allow all
        if (empty($this->allowed_employees) && 
            empty($this->allowed_departments) && 
            empty($this->allowed_designations)) {
            return true;
        }

        // Check specific employees
        if (!empty($this->allowed_employees) && in_array($employee->id, $this->allowed_employees)) {
            return true;
        }

        // Check departments
        if (!empty($this->allowed_departments) && in_array($employee->department_id, $this->allowed_departments)) {
            return true;
        }

        // Check designations
        if (!empty($this->allowed_designations) && in_array($employee->designation_id, $this->allowed_designations)) {
            return true;
        }

        return false;
    }

    // Check if point is within zone
    public function isPointInZone(float $latitude, float $longitude): bool
    {
        if ($this->polygon_coordinates) {
            return $this->isPointInPolygon($latitude, $longitude, $this->polygon_coordinates);
        }

        if ($this->center_latitude && $this->center_longitude) {
            $distance = $this->calculateDistance($latitude, $longitude, $this->center_latitude, $this->center_longitude);
            return $distance <= $this->radius_meters;
        }

        return false;
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    protected function isPointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        $n = count($polygon);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            if ((($yi > $lon) != ($yj > $lon)) && ($lat < ($xj - $xi) * ($lon - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Get employee's current zone
    public static function getEmployeeCurrentZone(Employee $employee, float $latitude, float $longitude): ?self
    {
        $zones = self::active()
            ->forBranch($employee->branch_id)
            ->orderBy('display_order')
            ->get();

        foreach ($zones as $zone) {
            if ($zone->isPointInZone($latitude, $longitude)) {
                return $zone;
            }
        }

        return null;
    }

    // Log zone access
    public function logAccess(Employee $employee, string $accessType, float $latitude, float $longitude, ?int $accuracy = null): ZoneAccessLog
    {
        $wasAuthorized = $this->isEmployeeAllowed($employee);

        return $this->accessLogs()->create([
            'employee_id' => $employee->id,
            'branch_id' => $this->branch_id,
            'access_type' => $accessType,
            'access_time' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => $accuracy,
            'was_authorized' => $wasAuthorized,
            'denial_reason' => $wasAuthorized ? null : 'غير مصرح للموظف بدخول هذه المنطقة',
        ]);
    }
}
