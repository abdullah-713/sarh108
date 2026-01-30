# 🚀 خطة تطوير نظام صرح الإتقان - الإصدار 2.0

**تاريخ الإنشاء:** 2026-01-30  
**الإصدار الحالي:** 1.0.4  
**الإصدار المستهدف:** 2.0.0  
**المدة المقدرة:** 12 أسبوع (3 أشهر)

---

## 📊 تحليل الوضع الحالي

### ✅ الميزات الموجودة حالياً (25%)
| # | الميزة | الحالة |
|---|--------|--------|
| 1 | إدارة الموظفين | ✅ موجود |
| 2 | إدارة الفروع | ✅ موجود |
| 3 | إدارة الأقسام | ✅ موجود |
| 4 | سياسات الحضور | ✅ موجود |
| 5 | سجلات الحضور | ✅ موجود |
| 6 | تسوية الحضور | ✅ موجود |
| 7 | إدارة الإجازات | ✅ موجود |
| 8 | الإعلانات | ✅ موجود |
| 9 | إدارة الأصول | ✅ موجود |
| 10 | التدريب | ✅ موجود |
| 11 | التقييم | ✅ موجود |
| 12 | العقود | ✅ موجود |
| 13 | التصميم (برتقالي/أسود/أبيض) | ✅ موجود |

### ❌ الميزات المطلوب تطويرها (75%)
37 ميزة جديدة من الـ50 ميزة المطلوبة

---

## 📅 خطة التطوير على 4 مراحل

---

## 🔴 المرحلة الأولى: البنية الأساسية (الأسابيع 1-3)

### الأسبوع 1: نظام التوثيق الجغرافي

#### 1.1 أولوية التوثيق الشبكي (Wi-Fi Priority)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_wifi_networks_table.php
├── app/Models/
│   └── WifiNetwork.php
├── app/Http/Controllers/
│   └── WifiNetworkController.php
├── resources/js/pages/hr/wifi-networks/
│   └── index.tsx
└── resources/js/utils/
    └── wifi-geolocation.ts
```

**المهام:**
- [ ] إنشاء جدول `wifi_networks` (ssid, bssid, branch_id, is_active)
- [ ] إنشاء API للتحقق من Wi-Fi
- [ ] إنشاء صفحة إدارة الشبكات
- [ ] تطوير JavaScript للكشف عن Wi-Fi

---

#### 1.2 النطاق الجغرافي الذكي (Smart Geofencing)
```
الملفات المطلوبة:
├── database/migrations/
│   └── add_geofence_to_branches_table.php
├── resources/js/utils/
│   └── geofencing.ts
└── app/Services/
    └── GeofencingService.php
```

**المهام:**
- [ ] إضافة حقول (latitude, longitude, radius) لجدول branches
- [ ] تطوير Haversine Formula Service
- [ ] إنشاء واجهة تحديد الموقع على الخريطة
- [ ] ربط GPS مع نظام الحضور

---

#### 1.3 النوافذ الزمنية الصارمة (Time Windows)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_time_windows_table.php
├── app/Models/
│   └── TimeWindow.php
├── app/Http/Controllers/
│   └── TimeWindowController.php
└── resources/js/pages/settings/
    └── time-windows.tsx
```

**المهام:**
- [ ] إنشاء جدول إعدادات النوافذ الزمنية
- [ ] تطوير منطق التحقق من الوقت
- [ ] إنشاء واجهة الإعدادات
- [ ] تعطيل/تفعيل زر الحضور حسب الوقت

---

### الأسبوع 2: نظام التسجيل المتقدم

#### 2.1 التسجيل بلمسة واحدة (One-Tap Check-in)
```
الملفات المطلوبة:
├── resources/js/pages/attendance/
│   └── quick-checkin.tsx
├── resources/js/components/attendance/
│   ├── CheckinButton.tsx
│   └── LocationVerifier.tsx
└── app/Http/Controllers/Api/
    └── QuickCheckinController.php
```

**المهام:**
- [ ] تصميم زر الحضور الكبير
- [ ] دمج التحقق من Wi-Fi + GPS
- [ ] إضافة تأكيد بصري وصوتي
- [ ] تسجيل البيانات في قاعدة البيانات

---

#### 2.2 التحقق الجماعي للمشرفين (Bulk Verification)
```
الملفات المطلوبة:
├── resources/js/pages/attendance/
│   └── bulk-checkin.tsx
├── app/Http/Controllers/
│   └── BulkCheckinController.php
└── database/migrations/
    └── create_bulk_checkin_logs_table.php
```

**المهام:**
- [ ] إنشاء صفحة التحضير الجماعي
- [ ] تطوير منطق التحضير المتعدد
- [ ] إضافة سجل للتحضير الجماعي
- [ ] صلاحيات المشرفين فقط

---

### الأسبوع 3: المؤشرات والخصومات

#### 3.1 مؤشرات الحالة الحية (Live Status)
```
الملفات المطلوبة:
├── resources/js/components/attendance/
│   ├── LiveStatusIndicator.tsx
│   └── StatusBadge.tsx
├── app/Http/Controllers/Api/
│   └── LiveStatusController.php
└── resources/js/hooks/
    └── useLiveStatus.ts
```

**المهام:**
- [ ] إنشاء مكون المؤشرات (أخضر/برتقالي/أحمر)
- [ ] تطوير API للحالة الحية
- [ ] تحديث تلقائي كل 30 ثانية
- [ ] دمج في Dashboard

---

#### 3.2 بروتوكول الخصم التصاعدي (Tiered Deduction)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_deduction_tiers_table.php
├── app/Models/
│   └── DeductionTier.php
├── app/Services/
│   └── DeductionService.php
└── resources/js/pages/settings/
    └── deduction-settings.tsx
```

**المهام:**
- [ ] إنشاء جدول مستويات الخصم
- [ ] تطوير خدمة حساب الخصم التلقائي
- [ ] إنشاء واجهة إعدادات الخصم
- [ ] ربط مع نظام الحضور

---

## 🟠 المرحلة الثانية: التنافسية (الأسابيع 4-6)

### الأسبوع 4: ترتيب الفروع والشارات

#### 4.1 مؤشر أداء الفروع (Branch Performance Index)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_branch_performance_table.php
├── app/Models/
│   └── BranchPerformance.php
├── app/Services/
│   └── BranchRankingService.php
├── app/Http/Controllers/
│   └── BranchRankingController.php
└── resources/js/pages/reports/
    └── branch-ranking.tsx
```

**المهام:**
- [ ] إنشاء جدول أداء الفروع
- [ ] تطوير خوارزمية الترتيب
- [ ] إنشاء صفحة الترتيب مع الرسوم البيانية
- [ ] تحديث يومي تلقائي (Cron Job)

---

#### 4.2 شارات التميز (Excellence Badges)
```
الملفات المطلوبة:
├── database/migrations/
│   ├── create_badges_table.php
│   └── create_employee_badges_table.php
├── app/Models/
│   ├── Badge.php
│   └── EmployeeBadge.php
├── app/Services/
│   └── BadgeService.php
├── resources/js/components/badges/
│   └── BadgeDisplay.tsx
└── resources/js/pages/hr/badges/
    └── index.tsx
```

**المهام:**
- [ ] إنشاء جداول الشارات
- [ ] تطوير أنواع الشارات (منتظم، مبكر، استمرارية، MVP)
- [ ] تصميم أيقونات الشارات
- [ ] منح تلقائي للشارات

---

### الأسبوع 5: MVP والاستمرارية

#### 5.1 موظف الفترة المثالي (MVP)
```
الملفات المطلوبة:
├── app/Services/
│   └── MVPCalculationService.php
├── app/Http/Controllers/
│   └── MVPController.php
└── resources/js/pages/hr/
    └── mvp-leaderboard.tsx
```

**المهام:**
- [ ] تطوير معايير MVP
- [ ] حساب تلقائي للمتميزين
- [ ] صفحة العشرة الأوائل
- [ ] تكريم علني

---

#### 5.2 شارة الاستمرارية (Consistency Streak)
```
الملفات المطلوبة:
├── database/migrations/
│   └── add_streak_to_employees_table.php
├── app/Services/
│   └── StreakService.php
└── app/Console/Commands/
    └── UpdateStreaksCommand.php
```

**المهام:**
- [ ] إضافة حقل streak لجدول الموظفين
- [ ] تطوير منطق الاستمرارية
- [ ] إعادة تصفير عند الانقطاع
- [ ] Cron Job يومي

---

### الأسبوع 6: التقارير والشفافية

#### 6.1 تقارير الشفافية (Transparency Reports)
```
الملفات المطلوبة:
├── app/Http/Controllers/
│   └── TransparencyReportController.php
└── resources/js/pages/reports/
    └── transparency-reports.tsx
```

**المهام:**
- [ ] تقارير علنية للجميع
- [ ] عرض نسب الحضور والتأخير
- [ ] مقارنة بين الفروع
- [ ] تصدير PDF

---

#### 6.2 شريط الأخبار العاجلة (News Ticker)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_news_ticker_table.php
├── app/Models/
│   └── NewsTicker.php
├── app/Http/Controllers/
│   └── NewsTickerController.php
├── resources/js/components/
│   └── NewsTicker.tsx
└── resources/js/pages/settings/
    └── news-ticker.tsx
```

**المهام:**
- [ ] إنشاء جدول الأخبار
- [ ] تصميم شريط متحرك
- [ ] إضافة للـ Dashboard
- [ ] إدارة الأخبار

---

## 🟡 المرحلة الثالثة: الذكاء الاصطناعي (الأسابيع 7-9)

### الأسبوع 7: التنبؤ والتحليل

#### 7.1 خوارزمية التنبؤ بالمخاطر (Risk Prediction)
```
الملفات المطلوبة:
├── app/Services/AI/
│   └── RiskPredictionService.php
├── app/Http/Controllers/
│   └── AIAnalyticsController.php
└── resources/js/pages/ai/
    └── risk-predictions.tsx
```

**المهام:**
- [ ] تحليل آخر 30 يوم
- [ ] حساب نسبة المخاطر (0-100%)
- [ ] تنبيهات استباقية
- [ ] عرض بصري للتوقعات

---

#### 7.2 تحليل المشاعر الرقمي (Sentiment Metrics)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_sentiment_metrics_table.php
├── app/Services/AI/
│   └── SentimentAnalysisService.php
└── resources/js/utils/
    └── sentiment-tracker.ts
```

**المهام:**
- [ ] تتبع سرعة الاستجابة
- [ ] قياس التفاعل
- [ ] تحليل السلوك الرقمي
- [ ] تقارير للمدراء

---

### الأسبوع 8: التحقق الحيوي

#### 8.1 التحقق الحيوي العشوائي (Random Liveness Check)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_liveness_checks_table.php
├── app/Models/
│   └── LivenessCheck.php
├── app/Services/
│   └── LivenessCheckService.php
├── app/Console/Commands/
│   └── TriggerLivenessCheckCommand.php
├── resources/js/components/
│   └── LivenessCheckModal.tsx
└── resources/js/hooks/
    └── useLivenessCheck.ts
```

**المهام:**
- [ ] إرسال طلب عشوائي
- [ ] نافذة منبثقة مع عداد 60 ثانية
- [ ] تسجيل الاستجابة
- [ ] تنبيه عند عدم الاستجابة

---

### الأسبوع 9: كشف التلاعب

#### 9.1 كشف التلاعب بالموقع (Mock Location Detector)
```
الملفات المطلوبة:
├── resources/js/utils/
│   └── mock-location-detector.ts
├── app/Services/Security/
│   └── TamperingDetectionService.php
└── database/migrations/
    └── create_tampering_logs_table.php
```

**المهام:**
- [ ] مقارنة Wi-Fi مع GPS
- [ ] كشف التناقضات
- [ ] قفل تلقائي عند الشك
- [ ] سجل كامل

---

## 🟢 المرحلة الرابعة: الميزات المتقدمة (الأسابيع 10-12)

### الأسبوع 10: مناطق العمل والأذونات

#### 10.1 مسح مناطق العمل (Zone-Based QR)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_work_zones_table.php
├── app/Models/
│   └── WorkZone.php
├── app/Http/Controllers/
│   └── WorkZoneController.php
├── resources/js/pages/hr/
│   └── work-zones/
│       ├── index.tsx
│       └── scanner.tsx
└── resources/js/components/
    └── QRScanner.tsx
```

**المهام:**
- [ ] إنشاء جدول مناطق العمل
- [ ] توليد QR Codes فريدة
- [ ] تطوير ماسح QR
- [ ] تسجيل الدخول للمناطق

---

#### 10.2 بروتوكول إذن الخروج (Digital Exit Permit)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_exit_permits_table.php
├── app/Models/
│   └── ExitPermit.php
├── app/Http/Controllers/
│   └── ExitPermitController.php
└── resources/js/pages/hr/
    └── exit-permits/
        ├── index.tsx
        └── request.tsx
```

**المهام:**
- [ ] إنشاء نظام طلب الإذن
- [ ] موافقة/رفض فوري
- [ ] أنواع مختلفة
- [ ] سجل كامل

---

### الأسبوع 11: التحكم والأمان

#### 11.1 وضع الإغلاق الشامل (Lockdown Mode)
```
الملفات المطلوبة:
├── app/Http/Middleware/
│   └── CheckLockdownMode.php
├── app/Services/
│   └── LockdownService.php
└── resources/js/pages/admin/
    └── lockdown.tsx
```

**المهام:**
- [ ] زر Lockdown للطوارئ
- [ ] إغلاق فوري للنظام
- [ ] إشعارات طارئة
- [ ] تفعيل انتقائي

---

#### 11.2 الصندوق الأسود (Black Box Log)
```
الملفات المطلوبة:
├── database/migrations/
│   └── create_audit_logs_table.php
├── app/Models/
│   └── AuditLog.php
├── app/Services/
│   └── AuditLogService.php
└── resources/js/pages/admin/
    └── audit-logs.tsx
```

**المهام:**
- [ ] سجل غير قابل للحذف
- [ ] تسجيل كل الإجراءات
- [ ] بحث وفلترة
- [ ] تصدير التقارير

---

### الأسبوع 12: التكامل والتحسينات

#### 12.1 العقل المدبر (System Brain)
```
الملفات المطلوبة:
├── app/Http/Controllers/
│   └── SystemBrainController.php
└── resources/js/pages/admin/
    └── system-brain/
        ├── index.tsx
        ├── time-settings.tsx
        ├── deduction-settings.tsx
        ├── network-settings.tsx
        └── notification-settings.tsx
```

**المهام:**
- [ ] لوحة تحكم مركزية
- [ ] جميع الإعدادات في مكان واحد
- [ ] تنظيم منطقي
- [ ] سهولة الاستخدام

---

#### 12.2 PWA + Offline Mode
```
الملفات المطلوبة:
├── public/
│   ├── manifest.json
│   └── sw.js
├── resources/js/utils/
│   └── offline-sync.ts
└── resources/views/
    └── app.blade.php (تحديث)
```

**المهام:**
- [ ] إعداد Service Worker
- [ ] حفظ محلي للبيانات
- [ ] مزامنة عند عودة الاتصال
- [ ] تثبيت كتطبيق

---

## 📊 ملخص المراحل

| المرحلة | الأسابيع | الميزات | الأولوية |
|---------|----------|---------|----------|
| 🔴 الأولى | 1-3 | 8 ميزات | حرجة |
| 🟠 الثانية | 4-6 | 10 ميزات | عالية |
| 🟡 الثالثة | 7-9 | 8 ميزات | متوسطة |
| 🟢 الرابعة | 10-12 | 11 ميزات | منخفضة |

---

## 📁 هيكل الملفات الجديدة

```
sarh108/
├── app/
│   ├── Console/Commands/
│   │   ├── DailyResetCommand.php
│   │   ├── UpdateStreaksCommand.php
│   │   ├── TriggerLivenessCheckCommand.php
│   │   └── UpdateBranchRankingsCommand.php
│   ├── Http/Controllers/
│   │   ├── WifiNetworkController.php
│   │   ├── BulkCheckinController.php
│   │   ├── BranchRankingController.php
│   │   ├── BadgeController.php
│   │   ├── MVPController.php
│   │   ├── NewsTickerController.php
│   │   ├── WorkZoneController.php
│   │   ├── ExitPermitController.php
│   │   ├── LivenessCheckController.php
│   │   ├── AIAnalyticsController.php
│   │   ├── SystemBrainController.php
│   │   └── AuditLogController.php
│   ├── Models/
│   │   ├── WifiNetwork.php
│   │   ├── TimeWindow.php
│   │   ├── DeductionTier.php
│   │   ├── BranchPerformance.php
│   │   ├── Badge.php
│   │   ├── EmployeeBadge.php
│   │   ├── NewsTicker.php
│   │   ├── WorkZone.php
│   │   ├── ExitPermit.php
│   │   ├── LivenessCheck.php
│   │   └── AuditLog.php
│   └── Services/
│       ├── GeofencingService.php
│       ├── DeductionService.php
│       ├── BranchRankingService.php
│       ├── BadgeService.php
│       ├── StreakService.php
│       ├── LivenessCheckService.php
│       ├── LockdownService.php
│       ├── AuditLogService.php
│       ├── AI/
│       │   ├── RiskPredictionService.php
│       │   └── SentimentAnalysisService.php
│       └── Security/
│           └── TamperingDetectionService.php
├── database/migrations/
│   ├── create_wifi_networks_table.php
│   ├── create_time_windows_table.php
│   ├── create_deduction_tiers_table.php
│   ├── create_branch_performance_table.php
│   ├── create_badges_table.php
│   ├── create_employee_badges_table.php
│   ├── create_news_ticker_table.php
│   ├── create_work_zones_table.php
│   ├── create_exit_permits_table.php
│   ├── create_liveness_checks_table.php
│   ├── create_sentiment_metrics_table.php
│   ├── create_tampering_logs_table.php
│   └── create_audit_logs_table.php
└── resources/js/
    ├── pages/
    │   ├── attendance/
    │   │   ├── quick-checkin.tsx
    │   │   └── bulk-checkin.tsx
    │   ├── hr/
    │   │   ├── wifi-networks/
    │   │   ├── badges/
    │   │   ├── work-zones/
    │   │   ├── exit-permits/
    │   │   └── mvp-leaderboard.tsx
    │   ├── reports/
    │   │   ├── branch-ranking.tsx
    │   │   └── transparency-reports.tsx
    │   ├── ai/
    │   │   └── risk-predictions.tsx
    │   ├── admin/
    │   │   ├── lockdown.tsx
    │   │   ├── audit-logs.tsx
    │   │   └── system-brain/
    │   └── settings/
    │       ├── time-windows.tsx
    │       ├── deduction-settings.tsx
    │       └── news-ticker.tsx
    ├── components/
    │   ├── attendance/
    │   │   ├── CheckinButton.tsx
    │   │   ├── LocationVerifier.tsx
    │   │   └── LiveStatusIndicator.tsx
    │   ├── badges/
    │   │   └── BadgeDisplay.tsx
    │   ├── NewsTicker.tsx
    │   ├── QRScanner.tsx
    │   └── LivenessCheckModal.tsx
    └── utils/
        ├── wifi-geolocation.ts
        ├── geofencing.ts
        ├── mock-location-detector.ts
        ├── sentiment-tracker.ts
        └── offline-sync.ts
```

---

## ⚡ أولويات التنفيذ الفوري

### هذا الأسبوع (الأولوية القصوى):

1. **إضافة حقول GPS للفروع** ✅
   ```sql
   ALTER TABLE branches ADD latitude DECIMAL(10,8);
   ALTER TABLE branches ADD longitude DECIMAL(11,8);
   ALTER TABLE branches ADD radius INT DEFAULT 100;
   ```

2. **إنشاء جدول Wi-Fi Networks** ✅
   ```sql
   CREATE TABLE wifi_networks (
     id INT PRIMARY KEY AUTO_INCREMENT,
     ssid VARCHAR(100),
     bssid VARCHAR(50),
     branch_id INT,
     is_active BOOLEAN DEFAULT TRUE
   );
   ```

3. **تطوير صفحة الحضور السريع**

4. **إضافة مؤشرات الحالة الحية**

---

## 📞 الدعم والمتابعة

للبدء بتنفيذ أي مرحلة، قل لي:
- "ابدأ المرحلة الأولى"
- "طور ميزة X"
- "أنشئ جدول Y"

---

**آخر تحديث:** 2026-01-30  
**الحالة:** جاهز للتنفيذ 🚀
