# نظام الحضور والانصراف الذكي - دليل التطبيق

## 📋 نظرة عامة

نظام حضور وانصراف متقدم وشامل يوفر:
- تسجيل الحضور والانصراف في الوقت الفعلي
- إدارة فترات الاستراحة
- حساب الساعات الإضافية
- تنبيهات ذكية ومراقبة
- تقارير شاملة على مستويات متعددة
- واجهات مستخدم منفصلة لكل دور

---

## 🏗️ البنية المعمارية

### قاعدة البيانات (Database)

#### الجداول الرئيسية:

1. **attendances** - سجل الحضور اليومي
   - employee_id (معرّف الموظف)
   - attendance_date (تاريخ الحضور)
   - check_in_time (وقت الحضور)
   - check_out_time (وقت الانصراف)
   - latitude_in/longitude_in (إحداثيات الحضور)
   - latitude_out/longitude_out (إحداثيات الانصراف)
   - total_hours (إجمالي ساعات العمل)
   - break_hours (ساعات الاستراحة)
   - overtime_hours (ساعات العمل الإضافي)
   - is_late, is_absent, is_present (حالات الحضور)
   - approval_status (حالة الموافقة)

2. **break_periods** - فترات الاستراحة
   - employee_id
   - attendance_record_id
   - break_start/break_end (وقت بداية ونهاية الاستراحة)
   - break_type (غداء، صلاة، طبية)
   - break_duration (مدة الاستراحة بالدقائق)
   - exceeds_limit (هل تجاوزت الحد الأقصى)

3. **overtime** - ساعات العمل الإضافي
   - employee_id
   - overtime_date
   - hours (عدد الساعات)
   - rate_per_hour (السعر للساعة)
   - total_amount (المبلغ الإجمالي)
   - overtime_type (يومي، أسبوعي، شهري، عطلة)
   - approval_status
   - payment_status

4. **work_hours_settings** - إعدادات ساعات العمل
   - department_id / shift_id
   - daily_working_hours (عدد ساعات العمل اليومية)
   - shift_start_time / shift_end_time
   - late_arrival_grace (فترة التسامح للتأخر)
   - break_duration (مدة الاستراحة المسموحة)
   - overtime_rate_per_hour

5. **attendance_alerts** - التنبيهات
   - employee_id
   - alert_type (late_arrival, absent, break_exceeded, etc)
   - severity (info, warning, critical)
   - is_resolved

6. **geo_locations** - الموقع الجغرافي المسموح به
   - branch_id
   - latitude / longitude
   - geofence_radius (نطاق الموقع بالمتر)
   - is_check_in_location / is_check_out_location

---

## 🔌 واجهات برمجية (APIs)

### 1. APIs للموظفين

#### تسجيل الحضور
```
POST /api/v1/attendance/check-in
Content-Type: application/json

{
    "employee_id": 1,
    "latitude": 24.7136,
    "longitude": 46.6753,
    "device_info": {...}
}

Response:
{
    "success": true,
    "data": {
        "attendance": {...},
        "message": "Check-in successful",
        "response_time_ms": 250
    }
}
```

#### تسجيل الانصراف
```
POST /api/v1/attendance/check-out
Content-Type: application/json

{
    "employee_id": 1,
    "latitude": 24.7136,
    "longitude": 46.6753
}

Response:
{
    "success": true,
    "data": {
        "attendance": {...},
        "total_hours": 8.5,
        "overtime_hours": 0.5
    }
}
```

#### بدء فترة استراحة
```
POST /api/v1/attendance/break/start
Content-Type: application/json

{
    "employee_id": 1,
    "break_type": "lunch",
    "reason": "Lunch break"
}
```

#### إنهاء فترة استراحة
```
POST /api/v1/attendance/break/end
Content-Type: application/json

{
    "break_id": 123
}
```

#### الحصول على الحالة الحالية
```
GET /api/v1/attendance/current-status?employee_id=1

Response:
{
    "success": true,
    "data": {
        "status": "checked_in|on_break|checked_out|not_checked_in",
        "attendance": {...},
        "current_break": {...}
    }
}
```

### 2. APIs لمديري الأقسام

#### لوحة تحكم المدير
```
GET /api/v1/manager/attendance-dashboard?department_id=1&start_date=2026-01-01&end_date=2026-01-31

Response:
{
    "total_employees": 50,
    "present_today": 48,
    "absent_today": 2,
    "late_today": 5,
    "on_break": 10,
    "attendance_trend": [...],
    "department_stats": [...],
    "alerts": [...],
    "recent_activities": [...]
}
```

#### قائمة حضور الفريق
```
GET /api/v1/manager/team-attendance?date=2026-01-30&status=late&page=1
```

#### الموافقة على السجلات
```
POST /api/v1/manager/approve-attendance
{
    "attendance_id": 123,
    "action": "approve|reject",
    "notes": "Approved"
}
```

#### طلبات الساعات الإضافية
```
GET /api/v1/manager/overtime-requests?status=pending&page=1

POST /api/v1/manager/approve-overtime
{
    "overtime_id": 123,
    "action": "approve|reject"
}
```

### 3. APIs للإدارة

#### لوحة تحكم الإدارة
```
GET /api/v1/admin/attendance-dashboard?start_date=2026-01-01&end_date=2026-01-31

Response:
{
    "summary": {
        "total_employees": 200,
        "total_present_today": 195,
        "average_working_hours": 8.2,
        "compliance_score": 94
    },
    "attendance_by_branch": [...],
    "department_performance": [...],
    "hourly_attendance": [...],
    "monthly_trends": [...],
    "overtime_summary": [...],
    "critical_alerts": [...]
}
```

#### تصدير التقارير
```
POST /api/v1/admin/attendance-report/export
{
    "format": "pdf|excel",
    "start_date": "2026-01-01",
    "end_date": "2026-01-31",
    "branch_id": 1
}
```

#### سجل الموظف
```
GET /api/v1/admin/employee/1/history?start_date=2026-01-01&end_date=2026-01-31&page=1
```

#### الإحصائيات
```
GET /api/v1/admin/statistics?period=month|week|day|year
```

---

## 🎨 مكونات الواجهة الأمامية (Frontend)

### 1. واجهة الموظف (EmployeeAttendance.tsx)
- عرض الوقت الفعلي مع دقة الثانية
- أزرار تسجيل الحضور والانصراف
- إدارة الاستراحات
- عرض حالة الموقع الجغرافي
- سجل الحضور الشخصي

### 2. لوحة تحكم المدير (ManagerAttendanceDashboard.tsx)
- إحصائيات فريق اليوم
- عرض الموظفين الحاضرين والغائبين
- رسوم بيانية للاتجاهات
- قائمة التنبيهات النشطة
- الأنشطة الحديثة

### 3. لوحة تحكم الإدارة (AdminAttendanceDashboard.tsx)
- إحصائيات شاملة
- تقارير متقدمة
- رسوم بيانية متعددة الأبعاد
- تصدير البيانات
- إدارة الموارد

---

## 🛠️ الخدمات الأساسية

### AttendanceReportService
توليد التقارير على مستويات مختلفة:
- `generateDailyReport()` - تقرير يومي
- `generateWeeklyReport()` - تقرير أسبوعي
- `generateMonthlyReport()` - تقرير شهري
- `generateYearlyReport()` - تقرير سنوي
- `generateEmployeeReport()` - تقرير خاص بموظف
- `generateDepartmentReport()` - تقرير القسم

### AttendanceCalculationService
حسابات ودوال الحضور:
- `calculateTotalWorkHours()` - حساب ساعات العمل
- `calculateOvertime()` - حساب الساعات الإضافية
- `isLateArrival()` - التحقق من التأخر
- `isEarlyDeparture()` - التحقق من الانصراف المبكر
- `calculatePerformanceScore()` - حساب درجة الأداء
- `detectAnomalies()` - الكشف عن التشوهات
- `validateAttendanceData()` - التحقق من سلامة البيانات

### AttendanceNotificationService
إدارة التنبيهات:
- `createLateArrivalAlert()` - تنبيه التأخر
- `createAbsenceAlert()` - تنبيه الغياب
- `createBreakExceededAlert()` - تنبيه تجاوز الاستراحة
- `createOvertimeAlert()` - تنبيه الساعات الإضافية
- `createGeofenceViolationAlert()` - تنبيه الموقع

---

## 📊 الحسابات الرياضية

### 1. حساب ساعات العمل
```
WorkHours = (CheckOutTime - CheckInTime - BreakMinutes) / 60
```

### 2. حساب الساعات الإضافية
```
Overtime = WorkHours - ExpectedWorkHours
if Overtime > 0: Valid
else: 0
```

### 3. حساب تأخر الوصول
```
LateMinutes = CheckInTime - (ShiftStartTime + GracePeriod)
if LateMinutes > 0: IsLate
```

### 4. درجة الأداء
```
Score = 100
if IsLate: Score -= 10
if IsEarlyDeparture: Score -= 10
if BreakExceeded: Score -= 5
if Overtime > 0: Score += min(10, Overtime)
Final: min(100, max(0, Score))
```

### 5. نسبة الحضور
```
AttendancePercentage = (PresentDays / TotalWorkingDays) * 100
```

---

## 🔒 أمان النظام

### حماية البيانات:
1. **تشفير الموقع الجغرافي** - تشفير إحداثيات GPS قبل الحفظ
2. **التحقق من الهوية** - OTP أو بيومترياء
3. **التحقق من الموقع** - Geofencing
4. **التحقق من الجهاز** - Device fingerprinting
5. **Audit Logging** - تسجيل جميع العمليات

### أدوار وصلاحيات:
- **Superadmin**: الوصول الكامل
- **Admin**: إدارة النظام والتقارير
- **Manager**: إدارة فريقهم فقط
- **Employee**: عرض بياناتهم فقط

---

## 🚀 الميزات المتقدمة

### 1. QR Code Check-in
- توليد رموز QR ديناميكية
- تحديث الرمز كل 5 دقائق
- ربط الرمز بالموقع والموظف

### 2. GPS Tracking
- التحقق من موقع الموظف
- نطاق جغرافي قابل للتخصيص
- تنبيهات عند مغادرة الموقع

### 3. Biometric Integration
- بصمة الإصبع
- التعرف على الوجه
- المصادقة متعددة العوامل

### 4. Real-time Notifications
- إشعارات فورية للمديرين
- تنبيهات الاستثناءات
- تحديثات الحالة

### 5. Predictive Analytics
- كشف الأنماط غير الطبيعية
- التنبؤ بالغياب
- تحليل الإنتاجية

---

## 📱 متطلبات الأداء

- **وقت الاستجابة**: < 500ms
- **دقة الموقع**: ± 10 متر
- **توفر النظام**: 99.9%
- **أمان البيانات**: SSL/TLS
- **النسخ الاحتياطي**: يومي

---

## 📞 الدعم الفني

للتواصل أو الإبلاغ عن مشاكل:
- البريد الإلكتروني: support@sarh.online
- الرقم الموحد: +966-XX-XXXX-XXXX
- ساعات الدعم: 24/7
