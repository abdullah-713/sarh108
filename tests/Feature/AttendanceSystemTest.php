<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\BreakPeriod;
use App\Models\Overtime;
use App\Models\AttendancePolicy;
use App\Models\WorkHoursSetting;
use App\Services\AttendanceCalculationService;
use Carbon\Carbon;

class AttendanceSystemTest extends TestCase
{
    protected $employee;
    protected $user;
    protected $manager;
    protected $policy;

    public function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        
        // Create test employee
        $this->employee = Employee::factory()->create([
            'user_id' => $this->user->id,
            'department_id' => 1,
        ]);

        // Create manager
        $this->manager = User::factory()->create();

        // Create attendance policy
        $this->policy = AttendancePolicy::factory()->create([
            'late_arrival_grace' => 15,
            'early_departure_grace' => 15,
        ]);
    }

    /**
     * Test employee check-in.
     */
    public function test_employee_can_check_in()
    {
        $response = $this->postJson('/api/v1/attendance/check-in', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'attendance',
                'message',
            ],
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'is_present' => true,
        ]);
    }

    /**
     * Test employee cannot check in twice in same day.
     */
    public function test_employee_cannot_check_in_twice()
    {
        // First check-in
        $this->postJson('/api/v1/attendance/check-in', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        // Second check-in attempt
        $response = $this->postJson('/api/v1/attendance/check-in', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test employee check-out.
     */
    public function test_employee_can_check_out()
    {
        // First check-in
        $this->postJson('/api/v1/attendance/check-in', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        // Then check-out
        $response = $this->postJson('/api/v1/attendance/check-out', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $response->assertStatus(200);
        
        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($attendance->check_out_time);
        $this->assertNotNull($attendance->total_hours);
    }

    /**
     * Test start break period.
     */
    public function test_employee_can_start_break()
    {
        // Check-in first
        $this->postJson('/api/v1/attendance/check-in', [
            'employee_id' => $this->employee->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        // Start break
        $response = $this->postJson('/api/v1/attendance/break/start', [
            'employee_id' => $this->employee->id,
            'break_type' => 'lunch',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('break_periods', [
            'employee_id' => $this->employee->id,
            'break_type' => 'lunch',
        ]);
    }

    /**
     * Test calculate work hours.
     */
    public function test_calculate_total_work_hours()
    {
        $checkIn = Carbon::now()->subHours(8)->subMinutes(30);
        $checkOut = Carbon::now();
        $breakMinutes = 30;

        $hours = AttendanceCalculationService::calculateTotalWorkHours(
            $checkIn,
            $checkOut,
            $breakMinutes
        );

        // Should be 8 hours (8.5 - 0.5 break)
        $this->assertEquals(8.0, $hours);
    }

    /**
     * Test calculate overtime hours.
     */
    public function test_calculate_overtime()
    {
        $workHours = 9;
        $expectedHours = 8;

        $overtime = AttendanceCalculationService::calculateOvertime(
            $workHours,
            $expectedHours
        );

        $this->assertEquals(1, $overtime);
    }

    /**
     * Test late arrival detection.
     */
    public function test_late_arrival_detection()
    {
        $shiftStart = Carbon::now()->setTime(8, 0);
        $checkIn = $shiftStart->copy()->addMinutes(20);

        $isLate = AttendanceCalculationService::isLateArrival(
            $checkIn,
            $shiftStart,
            15 // grace period
        );

        $this->assertTrue($isLate);
    }

    /**
     * Test late arrival within grace period.
     */
    public function test_late_arrival_within_grace_period()
    {
        $shiftStart = Carbon::now()->setTime(8, 0);
        $checkIn = $shiftStart->copy()->addMinutes(10);

        $isLate = AttendanceCalculationService::isLateArrival(
            $checkIn,
            $shiftStart,
            15 // grace period
        );

        $this->assertFalse($isLate);
    }

    /**
     * Test early departure detection.
     */
    public function test_early_departure_detection()
    {
        $shiftEnd = Carbon::now()->setTime(17, 0);
        $checkOut = $shiftEnd->copy()->subMinutes(20);

        $isEarly = AttendanceCalculationService::isEarlyDeparture(
            $checkOut,
            $shiftEnd,
            15 // grace period
        );

        $this->assertTrue($isEarly);
    }

    /**
     * Test attendance percentage calculation.
     */
    public function test_calculate_attendance_percentage()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Create some attendance records
        for ($i = 1; $i <= 20; $i++) {
            Attendance::factory()->create([
                'employee_id' => $this->employee->id,
                'attendance_date' => $startDate->copy()->addDays($i),
                'status' => $i % 2 == 0 ? 'present' : 'absent',
            ]);
        }

        $percentage = AttendanceCalculationService::calculateAttendancePercentage(
            $this->employee->id,
            $startDate,
            $endDate
        );

        // Should be around 50%
        $this->assertGreaterThan(40, $percentage);
        $this->assertLessThan(60, $percentage);
    }

    /**
     * Test performance score calculation.
     */
    public function test_calculate_performance_score()
    {
        $attendance = Attendance::factory()->create([
            'is_late' => false,
            'is_early_departure' => false,
            'overtime_hours' => 0,
        ]);

        $score = AttendanceCalculationService::calculatePerformanceScore($attendance);

        // Should be 100 for perfect attendance
        $this->assertEquals(100, $score);
    }

    /**
     * Test geofence validation.
     */
    public function test_geofence_validation()
    {
        $location = \App\Models\GeoLocation::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 100,
        ]);

        // Within geofence
        $isWithin = $location->isWithinGeofence(24.7136, 46.6753);
        $this->assertTrue($isWithin);

        // Outside geofence
        $isOutside = $location->isWithinGeofence(24.6, 46.5);
        $this->assertFalse($isOutside);
    }

    /**
     * Test manager dashboard access.
     */
    public function test_manager_can_view_dashboard()
    {
        $this->actingAs($this->manager);

        $response = $this->getJson('/api/v1/manager/attendance-dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_employees',
                'present_today',
                'absent_today',
            ],
        ]);
    }

    /**
     * Test admin dashboard access.
     */
    public function test_admin_can_view_dashboard()
    {
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/attendance-dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'attendance_by_branch',
                'department_performance',
            ],
        ]);
    }

    /**
     * Test report generation.
     */
    public function test_generate_daily_report()
    {
        // Create some attendance records
        Attendance::factory(5)->create([
            'attendance_date' => today(),
            'status' => 'present',
        ]);

        $report = \App\Services\AttendanceReportService::generateDailyReport(today());

        $this->assertNotNull($report['date']);
        $this->assertNotNull($report['summary']);
        $this->assertEquals(today()->format('Y-m-d'), $report['date']);
    }

    /**
     * Test validation of attendance data.
     */
    public function test_validate_attendance_data()
    {
        $attendance = Attendance::factory()->create([
            'check_in_time' => Carbon::now(),
            'check_out_time' => Carbon::now()->subHour(),
        ]);

        $errors = AttendanceCalculationService::validateAttendanceData($attendance);

        // Should have error for check-out before check-in
        $this->assertNotEmpty($errors);
        $this->assertContains(
            'Check-out time cannot be before check-in time',
            $errors
        );
    }

    /**
     * Test anomaly detection.
     */
    public function test_detect_anomalies()
    {
        // Create consecutive absences
        for ($i = 0; $i < 5; $i++) {
            Attendance::factory()->create([
                'employee_id' => $this->employee->id,
                'attendance_date' => Carbon::now()->subDays($i),
                'is_absent' => true,
            ]);
        }

        $anomalies = AttendanceCalculationService::detectAnomalies(
            $this->employee->id,
            30
        );

        // Should detect consecutive absences
        $this->assertNotEmpty($anomalies);
    }
}
