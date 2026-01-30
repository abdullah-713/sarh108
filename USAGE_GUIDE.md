# 🚀 دليل استخدام الملفات الجديدة - SARH Brand System

## 📖 مقدمة

هذا الدليل يشرح كيفية استخدام الملفات والمكونات الجديدة في نظام SARH Brand.

## 🎨 1. نظام الألوان المركزي

### ملف: `resources/js/config/brand-colors.ts`

يحتوي على جميع ألوان البراند والثوابت.

#### الاستخدام الأساسي:
```tsx
import { BRAND_COLORS, getColors } from '@/config/brand-colors';

// استخدام الألوان المباشرة
const primaryColor = BRAND_COLORS.orange[600];
const secondaryColor = BRAND_COLORS.black[700];

// الحصول على الألوان بناءً على Theme
const isDark = document.documentElement.classList.contains('dark');
const colors = getColors(isDark);
```

#### الألوان المتاحة:
```tsx
// Orange Palette (50-950)
BRAND_COLORS.orange[600]  // اللون الأساسي: #e67228

// Black Palette (50-950)
BRAND_COLORS.black[700]   // اللون الثانوي: #374151

// White & Special Colors
BRAND_COLORS.white        // #ffffff
BRAND_COLORS.success      // #10b981
BRAND_COLORS.warning      // #f59e0b
BRAND_COLORS.error        // #ef4444
BRAND_COLORS.info         // #3b82f6
```

## 🎯 2. Hook الألوان

### ملف: `resources/js/hooks/use-brand-colors.ts`

Hook React لتطبيق الألوان تلقائياً وتحديثها عند تغيير Theme.

#### الاستخدام:
```tsx
import { useBrandColors, brandColorClasses } from '@/hooks/use-brand-colors';

export default function MyComponent() {
  // تطبيق الألوان تلقائياً
  useBrandColors();
  
  return (
    <div>
      {/* استخدام branded color classes */}
      <h1 className={brandColorClasses.primary}>
        عنوان برتقالي
      </h1>
      
      <p className={brandColorClasses.secondary}>
        نص أسود
      </p>
      
      <button className="btn-primary">
        زر برتقالي
      </button>
    </div>
  );
}
```

#### الفئات المتاحة:
```tsx
brandColorClasses.primary          // text-orange-600 dark:text-orange-500
brandColorClasses.primaryLight     // text-orange-400 dark:text-orange-300
brandColorClasses.primaryDark      // text-orange-700 dark:text-orange-600
brandColorClasses.secondary        // text-black-700 dark:text-black-300
brandColorClasses.secondaryLight   // text-black-500 dark:text-black-400
brandColorClasses.accent           // text-orange-400 dark:text-orange-300
brandColorClasses.background       // bg-orange-50 dark:bg-black-900
brandColorClasses.backgroundLight  // bg-orange-100 dark:bg-black-800
brandColorClasses.border           // border-orange-200 dark:border-orange-700
brandColorClasses.borderLight      // border-orange-100 dark:border-orange-800
```

## 🎨 3. تحسينات CSS

### ملف: `resources/css/brand-enhancements.css`

يحتوي على أنماط CSS مخصصة بالبراند.

#### الأنماط المتاحة:

**Buttons:**
```html
<!-- زر أساسي -->
<button class="btn-primary">حفظ</button>

<!-- زر ثانوي -->
<button class="btn-secondary">إلغاء</button>
```

**Cards:**
```html
<div class="card-brand">
  محتوى البطاقة
</div>
```

**Badges:**
```html
<span class="badge-orange">مميز</span>
```

**Status Badges:**
```html
<span class="status-badge present">حاضر</span>
<span class="status-badge absent">غائب</span>
<span class="status-badge late">متأخر</span>
```

**Alerts:**
```html
<div class="alert-brand success">نجاح!</div>
<div class="alert-brand warning">تحذير!</div>
<div class="alert-brand error">خطأ!</div>
```

## 📱 4. مكونات Attendance

### مكون: `EmployeeAttendance.tsx`

واجهة موظف لتسجيل الحضور والانصراف.

#### الاستخدام:
```tsx
import EmployeeAttendance from '@/pages/attendance/EmployeeAttendance';

// سيعرض:
// - ساعة رقمية حية
// - زر الحضور والانصراف
// - زر الراحة
// - ملخص اليوم
```

### مكون: `ManagerAttendanceDashboard.tsx`

لوحة تحكم المدير مع إحصائيات الفريق.

#### الميزات:
- إحصائيات الفريق (حاضر/غائب/متأخر)
- رسوم بيانية (Line Chart, Pie Chart)
- جدول الحضور
- معدل الحضور

### مكون: `AdminAttendanceDashboard.tsx`

لوحة تحكم الإدارة مع إحصائيات شاملة.

#### الميزات:
- إحصائيات شاملة
- رسوم بيانية متقدمة
- جدول أقسام بالتفاصيل
- زر تصدير التقارير

## 🔧 5. Tailwind CSS Classes

### استخدام الألوان في HTML:

```html
<!-- نصوص -->
<p class="text-orange-600 dark:text-orange-500">نص برتقالي</p>
<p class="text-black-700 dark:text-black-300">نص أسود</p>

<!-- خلفيات -->
<div class="bg-orange-50 dark:bg-black-900">خلفية فاتحة</div>
<div class="bg-orange-600">خلفية برتقالية</div>

<!-- حدود -->
<div class="border-orange-200 dark:border-orange-700">حد برتقالي</div>

<!-- Gradients -->
<div class="bg-gradient-to-r from-orange-600 to-orange-500">تدرج</div>

<!-- Shadow -->
<div class="shadow-lg glow-orange">مع إضاءة</div>
```

## 📝 6. RTL Support

### CSS للـ RTL:

تم إضافة جميع أنماط RTL في `resources/css/rtl.css`.

**الاستخدام التلقائي:**
```tsx
// الـ RTL يطبق تلقائياً عند إضافة dir="rtl" للـ HTML
// لا تحتاج لأي شيء إضافي!

<html dir="rtl">
  {/* جميع العناصر ستتوافق مع RTL تلقائياً */}
</html>
```

## 🌓 7. Dark Mode

### تبديل Dark Mode:

```tsx
// في أي مكان في التطبيق
const toggleDarkMode = () => {
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', 
    document.documentElement.classList.contains('dark') ? 'dark' : 'light'
  );
};
```

### استخدام في Tailwind:

```html
<!-- Light mode -->
<div class="bg-white text-black">
  <!-- Dark mode -->
  <div class="dark:bg-black-900 dark:text-white">
    محتوى
  </div>
</div>
```

## 💡 8. أمثلة عملية

### مثال 1: مكون بسيط بالبراند
```tsx
import { brandColorClasses } from '@/hooks/use-brand-colors';

export default function WelcomeCard() {
  return (
    <div className="card-brand p-6">
      <h2 className={brandColorClasses.primary}>
        مرحباً بك في SARH
      </h2>
      <p className={brandColorClasses.secondary}>
        النظام الحديث لإدارة الموارد البشرية
      </p>
      <button className="btn-primary mt-4">
        ابدأ الآن
      </button>
    </div>
  );
}
```

### مثال 2: مكون مع رسم بياني
```tsx
import { useChartColors } from '@/hooks/use-brand-colors';
import { LineChart, Line, XAxis, YAxis } from 'recharts';

export default function Chart({ data }) {
  const chartColors = useChartColors();
  
  return (
    <LineChart data={data}>
      <XAxis />
      <YAxis />
      <Line dataKey="value" stroke={chartColors[0]} />
    </LineChart>
  );
}
```

### مثال 3: جدول بالبراند
```tsx
export default function StatusTable() {
  return (
    <table className="table-brand w-full">
      <thead>
        <tr>
          <th>الموظف</th>
          <th>الحالة</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>محمد أحمد</td>
          <td>
            <span className="status-badge present">حاضر</span>
          </td>
        </tr>
      </tbody>
    </table>
  );
}
```

## 🎓 9. أفضل الممارسات

### ✅ افعل:
```tsx
// استخدم الألوان المركزية
import { BRAND_COLORS } from '@/config/brand-colors';
const color = BRAND_COLORS.orange[600];

// استخدم Tailwind classes
<div className="text-orange-600 dark:text-orange-500">
```

### ❌ لا تفعل:
```tsx
// لا تستخدم ألوان عشوائية
style={{ color: '#ff0000' }}

// لا تنسَ dark: prefix
<div className="text-orange-600">
```

## 📚 الموارد الإضافية

- [Tailwind CSS Docs](https://tailwindcss.com/)
- [React Hooks Guide](https://react.dev/reference/react)
- [Brand Colors Config](./resources/js/config/brand-colors.ts)
- [CSS Enhancements](./resources/css/brand-enhancements.css)

## 🤝 الدعم والمساعدة

للمزيد من المعلومات:
- اقرأ: [BRAND_REDESIGN_GUIDE.md](./BRAND_REDESIGN_GUIDE.md)
- اقرأ: [DESIGN_UPDATES.md](./DESIGN_UPDATES.md)
- اطلب المساعدة من الفريق

---

**تم التحديث**: يناير 30، 2024  
**الإصدار**: 1.0
