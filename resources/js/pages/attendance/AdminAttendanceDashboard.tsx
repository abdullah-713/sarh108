import React, { useState } from 'react';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Download, TrendingUp, Users, AlertTriangle, FileText } from 'lucide-react';
import { BRAND_COLORS, getChartColors } from '@/config/brand-colors';

interface DepartmentStats {
    department: string;
    total_employees: number;
    present_count: number;
    absent_count: number;
    late_count: number;
    attendance_rate: number;
}

interface OverallStats {
    total_employees: number;
    total_present: number;
    total_absent: number;
    total_late: number;
    overall_attendance_rate: number;
    working_days_this_month: number;
    public_holidays: number;
}

interface AttendanceTrendData {
    date: string;
    attendance: number;
    target: number;
}

export default function AdminAttendanceDashboard() {
    const [stats, setStats] = useState<OverallStats>({
        total_employees: 0,
        total_present: 0,
        total_absent: 0,
        total_late: 0,
        overall_attendance_rate: 0,
        working_days_this_month: 0,
        public_holidays: 0,
    });

    const [departmentStats, setDepartmentStats] = useState<DepartmentStats[]>([]);
    const [trendData, setTrendData] = useState<AttendanceTrendData[]>([]);

    const isDark = document.documentElement.classList.contains('dark');
    const chartColors = getChartColors(isDark);

    const handleExportReport = async () => {
        try {
            const response = await fetch(route('admin.attendance.export'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `attendance_report_${format(new Date(), 'yyyy-MM-dd')}.xlsx`;
                link.click();
            }
        } catch (error) {
            console.error('Export failed:', error);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 to-white dark:from-black-950 dark:to-black-900 p-4 md:p-8">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header with Export Button */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                    <div>
                        <h1 className="text-3xl md:text-4xl font-bold text-black-900 dark:text-white mb-2">
                            لوحة تحكم إدارة الحضور والانصراف
                        </h1>
                        <p className="text-black-600 dark:text-black-300">
                            إحصائيات شاملة ونقارير الحضور للمؤسسة
                        </p>
                    </div>
                    <Button
                        onClick={handleExportReport}
                        className="bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 mt-4 md:mt-0"
                    >
                        <Download className="w-5 h-5" />
                        تصدير التقرير
                    </Button>
                </div>

                {/* Overall Stats */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {/* Total Employees */}
                    <Card className="border-t-4 border-t-blue-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <p className="text-black-600 dark:text-black-400 text-sm font-medium">إجمالي الموظفين</p>
                            <p className="text-3xl font-bold text-blue-600 mt-2">{stats.total_employees}</p>
                        </div>
                    </Card>

                    {/* Present */}
                    <Card className="border-t-4 border-t-green-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <p className="text-black-600 dark:text-black-400 text-sm font-medium">الحاضرون</p>
                            <p className="text-3xl font-bold text-green-600 mt-2">{stats.total_present}</p>
                        </div>
                    </Card>

                    {/* Absent */}
                    <Card className="border-t-4 border-t-red-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <p className="text-black-600 dark:text-black-400 text-sm font-medium">الغائبون</p>
                            <p className="text-3xl font-bold text-red-600 mt-2">{stats.total_absent}</p>
                        </div>
                    </Card>

                    {/* Late */}
                    <Card className="border-t-4 border-t-orange-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <p className="text-black-600 dark:text-black-400 text-sm font-medium">المتأخرون</p>
                            <p className="text-3xl font-bold text-orange-600 mt-2">{stats.total_late}</p>
                        </div>
                    </Card>

                    {/* Attendance Rate */}
                    <Card className="border-t-4 border-t-purple-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <p className="text-black-600 dark:text-black-400 text-sm font-medium">معدل الحضور</p>
                            <p className="text-3xl font-bold text-purple-600 mt-2">{stats.overall_attendance_rate}%</p>
                        </div>
                    </Card>
                </div>

                {/* Calendar Info */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">أيام العمل</p>
                                    <p className="text-2xl font-bold text-orange-600 mt-2">{stats.working_days_this_month}</p>
                                    <p className="text-xs text-black-500 dark:text-black-400 mt-2">هذا الشهر</p>
                                </div>
                                <div className="bg-orange-100 dark:bg-orange-900 p-4 rounded-lg">
                                    <FileText className="w-6 h-6 text-orange-600" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card className="bg-white dark:bg-black-900 border border-red-200 dark:border-red-700">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">العطلات الرسمية</p>
                                    <p className="text-2xl font-bold text-red-600 mt-2">{stats.public_holidays}</p>
                                    <p className="text-xs text-black-500 dark:text-black-400 mt-2">هذا الشهر</p>
                                </div>
                                <div className="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                                    <AlertTriangle className="w-6 h-6 text-red-600" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card className="bg-white dark:bg-black-900 border border-purple-200 dark:border-purple-700">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">التقرير الأخير</p>
                                    <p className="text-lg font-bold text-purple-600 mt-2">{format(new Date(), 'd MMM', { locale: ar })}</p>
                                    <p className="text-xs text-black-500 dark:text-black-400 mt-2">محدّث الآن</p>
                                </div>
                                <div className="bg-purple-100 dark:bg-purple-900 p-4 rounded-lg">
                                    <TrendingUp className="w-6 h-6 text-purple-600" />
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Charts */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Monthly Trend */}
                    <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                        <div className="p-6">
                            <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">اتجاه الحضور الشهري</h2>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={trendData}>
                                    <CartesianGrid stroke={isDark ? '#374151' : '#e5e7eb'} />
                                    <XAxis dataKey="date" stroke={isDark ? '#9ca3af' : '#6b7280'} />
                                    <YAxis stroke={isDark ? '#9ca3af' : '#6b7280'} />
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                            border: `1px solid ${BRAND_COLORS.orange[600]}`,
                                        }}
                                    />
                                    <Legend />
                                    <Line type="monotone" dataKey="attendance" stroke={chartColors[0]} strokeWidth={2} name="الحضور الفعلي" />
                                    <Line type="monotone" dataKey="target" stroke={chartColors[1]} strokeWidth={2} strokeDasharray="5 5" name="الهدف" />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>

                    {/* Department Comparison */}
                    <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                        <div className="p-6">
                            <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">مقارنة الأقسام</h2>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={departmentStats}>
                                    <CartesianGrid stroke={isDark ? '#374151' : '#e5e7eb'} />
                                    <XAxis dataKey="department" stroke={isDark ? '#9ca3af' : '#6b7280'} />
                                    <YAxis stroke={isDark ? '#9ca3af' : '#6b7280'} />
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                            border: `1px solid ${BRAND_COLORS.orange[600]}`,
                                        }}
                                    />
                                    <Legend />
                                    <Bar dataKey="present_count" fill={chartColors[0]} name="حاضر" />
                                    <Bar dataKey="absent_count" fill={chartColors[1]} name="غائب" />
                                    <Bar dataKey="late_count" fill={chartColors[2]} name="متأخر" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>
                </div>

                {/* Department Details Table */}
                <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                    <div className="p-6">
                        <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">إحصائيات الأقسام</h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b-2 border-orange-200 dark:border-orange-700">
                                        <th className="text-right py-3 px-4 font-semibold text-black-700 dark:text-black-200">القسم</th>
                                        <th className="text-center py-3 px-4 font-semibold text-black-700 dark:text-black-200">الموظفون</th>
                                        <th className="text-center py-3 px-4 font-semibold text-black-700 dark:text-black-200">حاضر</th>
                                        <th className="text-center py-3 px-4 font-semibold text-black-700 dark:text-black-200">غائب</th>
                                        <th className="text-center py-3 px-4 font-semibold text-black-700 dark:text-black-200">متأخر</th>
                                        <th className="text-center py-3 px-4 font-semibold text-black-700 dark:text-black-200">معدل الحضور</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {departmentStats.map((dept) => (
                                        <tr
                                            key={dept.department}
                                            className="border-b border-black-100 dark:border-black-800 hover:bg-orange-50 dark:hover:bg-black-800 transition"
                                        >
                                            <td className="py-4 px-4 font-medium text-black-900 dark:text-white">{dept.department}</td>
                                            <td className="py-4 px-4 text-center">
                                                <Badge variant="outline">{dept.total_employees}</Badge>
                                            </td>
                                            <td className="py-4 px-4 text-center">
                                                <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    {dept.present_count}
                                                </Badge>
                                            </td>
                                            <td className="py-4 px-4 text-center">
                                                <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    {dept.absent_count}
                                                </Badge>
                                            </td>
                                            <td className="py-4 px-4 text-center">
                                                <Badge className="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                                                    {dept.late_count}
                                                </Badge>
                                            </td>
                                            <td className="py-4 px-4 text-center font-semibold text-orange-600">
                                                {dept.attendance_rate}%
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    );
}
