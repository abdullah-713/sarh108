<?php

namespace App\Services;

use App\Models\RiskPrediction;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RiskPredictionService
{
    /**
     * تحليل وتوقع مخاطر الغياب
     */
    public function predictAbsenceRisks(array $companyUserIds): Collection
    {
        $predictions = collect();

        $employees = Employee::whereIn('created_by', $companyUserIds)
            ->where('status', 'active')
            ->with(['attendances' => function ($q) {
                $q->where('date', '>=', Carbon::now()->subDays(90))
                    ->orderBy('date', 'desc');
            }])
            ->get();

        foreach ($employees as $employee) {
            $factors = $this->analyzeAbsencePattern($employee);
            
            if ($factors['risk_score'] >= 40) {
                $prediction = RiskPrediction::generatePrediction(
                    $employee,
                    'absence',
                    $factors
                );
                $predictions->push($prediction);
            }
        }

        return $predictions;
    }

    /**
     * تحليل نمط الغياب للموظف
     */
    protected function analyzeAbsencePattern(Employee $employee): array
    {
        $attendances = $employee->attendances;
        $totalDays = $attendances->count();
        
        if ($totalDays < 10) {
            return ['risk_score' => 0, 'confidence' => 0];
        }

        $absentDays = $attendances->where('status', 'absent')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        
        // تحليل أيام الأسبوع الأكثر غياباً
        $weekdayAbsences = $attendances
            ->where('status', 'absent')
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->dayOfWeek;
            })
            ->map->count()
            ->toArray();

        // أكثر يوم غياباً
        $mostAbsentDay = !empty($weekdayAbsences) ? array_search(max($weekdayAbsences), $weekdayAbsences) : null;

        // التحقق من نمط الغياب
        $recentAbsences = $attendances
            ->where('status', 'absent')
            ->where('date', '>=', Carbon::now()->subDays(14))
            ->count();

        // حساب درجة المخاطرة
        $absenceRate = ($absentDays / $totalDays) * 100;
        $lateRate = ($lateDays / $totalDays) * 100;
        $recentPattern = $recentAbsences > 0;

        $riskScore = 0;
        $riskScore += min(40, $absenceRate * 4); // max 40
        $riskScore += min(20, $lateRate * 2); // max 20
        $riskScore += $recentPattern ? 20 : 0;
        $riskScore += $recentAbsences * 10; // 10 per recent absence

        // توقع التاريخ
        $predictedDate = Carbon::now();
        if ($mostAbsentDay !== null) {
            $predictedDate = $predictedDate->next($mostAbsentDay);
        } else {
            $predictedDate = $predictedDate->addDays(rand(1, 7));
        }

        return [
            'risk_score' => min(100, $riskScore),
            'confidence' => $totalDays >= 30 ? 80 : 60,
            'absence_rate' => $absenceRate,
            'late_rate' => $lateRate,
            'recent_absences' => $recentAbsences,
            'most_absent_day' => $mostAbsentDay,
            'pattern_detected' => $recentPattern,
            'predicted_date' => $predictedDate,
        ];
    }

    /**
     * توقع مخاطر التأخير
     */
    public function predictLateRisks(array $companyUserIds): Collection
    {
        $predictions = collect();

        $employees = Employee::whereIn('created_by', $companyUserIds)
            ->where('status', 'active')
            ->with(['attendances' => function ($q) {
                $q->where('date', '>=', Carbon::now()->subDays(60))
                    ->orderBy('date', 'desc');
            }])
            ->get();

        foreach ($employees as $employee) {
            $factors = $this->analyzeLatePattern($employee);
            
            if ($factors['risk_score'] >= 50) {
                $prediction = RiskPrediction::generatePrediction(
                    $employee,
                    'late',
                    $factors
                );
                $predictions->push($prediction);
            }
        }

        return $predictions;
    }

    /**
     * تحليل نمط التأخير
     */
    protected function analyzeLatePattern(Employee $employee): array
    {
        $attendances = $employee->attendances;
        $totalDays = $attendances->count();
        
        if ($totalDays < 10) {
            return ['risk_score' => 0, 'confidence' => 0];
        }

        $lateDays = $attendances->where('status', 'late')->count();
        $lateRate = ($lateDays / $totalDays) * 100;

        // متوسط دقائق التأخير
        $avgLateMinutes = $attendances
            ->where('status', 'late')
            ->where('late_minutes', '>', 0)
            ->avg('late_minutes') ?? 0;

        // التأخيرات الأخيرة
        $recentLates = $attendances
            ->where('status', 'late')
            ->where('date', '>=', Carbon::now()->subDays(7))
            ->count();

        $riskScore = 0;
        $riskScore += min(40, $lateRate * 2);
        $riskScore += min(30, $avgLateMinutes);
        $riskScore += $recentLates * 15;

        return [
            'risk_score' => min(100, $riskScore),
            'confidence' => $totalDays >= 20 ? 75 : 55,
            'late_rate' => $lateRate,
            'average_late_minutes' => $avgLateMinutes,
            'recent_lates' => $recentLates,
            'predicted_date' => Carbon::tomorrow(),
        ];
    }

    /**
     * توقع مخاطر الإرهاق
     */
    public function predictBurnoutRisks(array $companyUserIds): Collection
    {
        $predictions = collect();

        $employees = Employee::whereIn('created_by', $companyUserIds)
            ->where('status', 'active')
            ->with(['attendances' => function ($q) {
                $q->where('date', '>=', Carbon::now()->subDays(60));
            }])
            ->get();

        foreach ($employees as $employee) {
            $factors = $this->analyzeBurnoutIndicators($employee);
            
            if ($factors['risk_score'] >= 60) {
                $prediction = RiskPrediction::generatePrediction(
                    $employee,
                    'burnout',
                    $factors
                );
                $predictions->push($prediction);
            }
        }

        return $predictions;
    }

    /**
     * تحليل مؤشرات الإرهاق
     */
    protected function analyzeBurnoutIndicators(Employee $employee): array
    {
        $attendances = $employee->attendances;

        // ساعات العمل الإضافية
        $overtimeHours = $attendances->sum('overtime_hours') ?? 0;
        $avgOvertimePerDay = $attendances->count() > 0 
            ? $overtimeHours / $attendances->count() 
            : 0;

        // أيام العمل المتتالية بدون إجازة
        $consecutiveWorkDays = 0;
        $maxConsecutive = 0;
        
        foreach ($attendances->sortBy('date') as $attendance) {
            if ($attendance->status == 'present' || $attendance->status == 'late') {
                $consecutiveWorkDays++;
                $maxConsecutive = max($maxConsecutive, $consecutiveWorkDays);
            } else {
                $consecutiveWorkDays = 0;
            }
        }

        // تغير في نمط الحضور
        $recentLates = $attendances
            ->where('status', 'late')
            ->where('date', '>=', Carbon::now()->subDays(14))
            ->count();

        $riskScore = 0;
        $riskScore += min(30, $avgOvertimePerDay * 15);
        $riskScore += min(30, ($maxConsecutive - 5) * 5);
        $riskScore += $recentLates * 10;

        return [
            'risk_score' => min(100, max(0, $riskScore)),
            'confidence' => 70,
            'overtime_hours' => $overtimeHours,
            'average_overtime_per_day' => $avgOvertimePerDay,
            'max_consecutive_days' => $maxConsecutive,
            'recent_lates' => $recentLates,
            'predicted_date' => Carbon::now()->addDays(14),
        ];
    }

    /**
     * الحصول على التوقعات النشطة
     */
    public function getActivePredictions(array $companyUserIds, ?string $riskType = null): Collection
    {
        return RiskPrediction::getActiveForCompany($companyUserIds, 'pending', null)
            ->when($riskType, function ($collection) use ($riskType) {
                return $collection->where('risk_type', $riskType);
            });
    }

    /**
     * تحديث حالة التوقع
     */
    public function updatePredictionStatus(
        RiskPrediction $prediction,
        string $status,
        ?int $reviewerId = null,
        ?string $notes = null
    ): RiskPrediction {
        $prediction->update([
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => Carbon::now(),
            'review_notes' => $notes,
        ]);

        return $prediction->fresh();
    }

    /**
     * تسجيل نتيجة التوقع
     */
    public function recordOutcome(
        RiskPrediction $prediction,
        bool $wasAccurate,
        ?string $notes = null
    ): RiskPrediction {
        $prediction->update([
            'was_accurate' => $wasAccurate,
            'outcome_date' => Carbon::now(),
            'outcome_notes' => $notes,
            'status' => $wasAccurate ? 'occurred' : 'false_alarm',
        ]);

        return $prediction->fresh();
    }

    /**
     * تشغيل جميع التحليلات
     */
    public function runAllAnalyses(array $companyUserIds): array
    {
        $absencePredictions = $this->predictAbsenceRisks($companyUserIds);
        $latePredictions = $this->predictLateRisks($companyUserIds);
        $burnoutPredictions = $this->predictBurnoutRisks($companyUserIds);

        return [
            'absence_predictions' => $absencePredictions->count(),
            'late_predictions' => $latePredictions->count(),
            'burnout_predictions' => $burnoutPredictions->count(),
            'total' => $absencePredictions->count() + $latePredictions->count() + $burnoutPredictions->count(),
        ];
    }
}
