<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeoLocation extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'geo_locations';

    protected $fillable = [
        'branch_id',
        'location_name',
        'latitude',
        'longitude',
        'geofence_radius',
        'is_check_in_location',
        'is_check_out_location',
        'is_active',
        'description',
        'working_hours',
        'allowed_users',
        'altitude',
        'accuracy',
        'address',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'geofence_radius' => 'float',
        'is_check_in_location' => 'boolean',
        'is_check_out_location' => 'boolean',
        'is_active' => 'boolean',
        'working_hours' => 'array',
        'allowed_users' => 'array',
        'altitude' => 'float',
        'accuracy' => 'float',
    ];

    /**
     * Get the branch associated with this location.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the creator of this location.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a given GPS coordinate is within the geofence.
     */
    public function isWithinGeofence($latitude, $longitude)
    {
        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->geofence_radius;
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula.
     */
    public function calculateDistance($latitude, $longitude)
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($latitude - $this->latitude);
        $dLon = deg2rad($longitude - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadiusKm * $c * 1000; // Convert to meters

        return $distance;
    }

    /**
     * Check if location is a check-in location.
     */
    public function isCheckInLocation()
    {
        return $this->is_check_in_location && $this->is_active;
    }

    /**
     * Check if location is a check-out location.
     */
    public function isCheckOutLocation()
    {
        return $this->is_check_out_location && $this->is_active;
    }

    /**
     * Check if user is allowed at this location.
     */
    public function isUserAllowed($userId)
    {
        if (!$this->allowed_users || count($this->allowed_users) === 0) {
            return true; // No restrictions
        }
        return in_array($userId, $this->allowed_users);
    }

    /**
     * Scope to get active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get check-in locations.
     */
    public function scopeCheckIn($query)
    {
        return $query->where('is_check_in_location', true);
    }

    /**
     * Scope to get check-out locations.
     */
    public function scopeCheckOut($query)
    {
        return $query->where('is_check_out_location', true);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
