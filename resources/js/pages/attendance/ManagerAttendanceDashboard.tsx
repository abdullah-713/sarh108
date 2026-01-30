import React, { useState } from 'react';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';
import { BarChart, Bar, LineChart, Line, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Card } from '@/components/ui/card';
import { Users, TrendingUp, Clock, AlertTriangle } from 'lucide-react';
import { BRAND_COLORS, getChartColors } from '@/config/brand-colors';

interface TeamAttendance {
    id: number;
    employee_name: string;
    status: 'present' | 'absent' | 'late';
    check_in_time: string | null;
    check_out_time: string | null;
}

interface DashboardStats {
    total_present: number;
    total_absent: number;
    total_late: number;
    attendance_rate: number;
}

interface AttendanceTrendData {
    date: string;
    present: number;
    absent: number;
    late: number;
}

export default function ManagerAttendanceDashboard() {
    const [stats, setStats] = useState<DashboardStats>({
        total_present: 0,
        total_absent: 0,
        total_late: 0,
        attendance_rate: 0,
    });

    const [teamAttendance, setTeamAttendance] = useState<TeamAttendance[]>([]);
    const [trendData, setTrendData] = useState<AttendanceTrendData[]>([]);

    const isDark = document.documentElement.classList.contains('dark');
    const chartColors = getChartColors(isDark);

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'present':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'absent':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            case 'late':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        }
    };

    const getStatusLabel = (status: string) => {
        switch (status) {
            case 'present':
                return 'حاضر';
            case 'absent':
                return 'غائب';
            case 'late':
                return 'متأخر';
            default:
                return 'غير معروف';
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 to-white dark:from-black-950 dark:to-black-900 p-4 md:p-8">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl md:text-4xl font-bold text-black-900 dark:text-white mb-2">
                        لوحة تحكم الحضور والانصراف
                    </h1>
                    <p className="text-black-600 dark:text-black-300">
                        إدارة حضور الفريق والإحصائيات
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {/* Present Count */}
                    <Card className="border-l-4 border-l-green-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">الحاضرون</p>
                                    <p className="text-3xl font-bold text-green-600 mt-1">{stats.total_present}</p>
                                </div>
                                <div className="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                                    <Users className="w-6 h-6 text-green-600" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Absent Count */}
                    <Card className="border-l-4 border-l-red-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">الغائبون</p>
                                    <p className="text-3xl font-bold text-red-600 mt-1">{stats.total_absent}</p>
                                </div>
                                <div className="bg-red-100 dark:bg-red-900 p-3 rounded-lg">
                                    <AlertTriangle className="w-6 h-6 text-red-600" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Late Count */}
                    <Card className="border-l-4 border-l-orange-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">المتأخرون</p>
                                    <p className="text-3xl font-bold text-orange-600 mt-1">{stats.total_late}</p>
                                </div>
                                <div className="bg-orange-100 dark:bg-orange-900 p-3 rounded-lg">
                                    <Clock className="w-6 h-6 text-orange-600" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Attendance Rate */}
                    <Card className="border-l-4 border-l-blue-500 bg-white dark:bg-black-900">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-black-600 dark:text-black-400 text-sm font-medium">معدل الحضور</p>
                                    <p className="text-3xl font-bold text-blue-600 mt-1">{stats.attendance_rate}%</p>
                                </div>
                                <div className="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                                    <TrendingUp className="w-6 h-6 text-blue-600" />
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Charts Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Attendance Trend Chart */}
                    <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                        <div className="p-6">
                            <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">اتجاه الحضور</h2>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={trendData}>
                                    <CartesianGrid stroke={isDark ? '#374151' : '#e5e7eb'} />
                                    <XAxis
                                        dataKey="date"
                                        stroke={isDark ? '#9ca3af' : '#6b7280'}
                                    />
                                    <YAxis stroke={isDark ? '#9ca3af' : '#6b7280'} />
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                            border: `1px solid ${BRAND_COLORS.orange[600]}`,
                                            borderRadius: '8px',
                                        }}
                                        labelStyle={{ color: isDark ? '#ffffff' : '#000000' }}
                                    />
                                    <Legend />
                                    <Line type="monotone" dataKey="present" stroke={chartColors[0]} strokeWidth={2} name="حاضر" />
                                    <Line type="monotone" dataKey="absent" stroke={chartColors[1]} strokeWidth={2} name="غائب" />
                                    <Line type="monotone" dataKey="late" stroke={chartColors[2]} strokeWidth={2} name="متأخر" />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>

                    {/* Attendance Distribution */}
                    <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                        <div className="p-6">
                            <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">توزيع الحضور</h2>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={[
                                            { name: 'حاضر', value: stats.total_present },
                                            { name: 'غائب', value: stats.total_absent },
                                            { name: 'متأخر', value: stats.total_late },
                                        ]}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ name, value }) => `${name}: ${value}`}
                                        outerRadius={100}
                                        fill={BRAND_COLORS.orange[600]}
                                        dataKey="value"
                                    >
                                        <Cell fill={chartColors[0]} />
                                        <Cell fill={chartColors[1]} />
                                        <Cell fill={chartColors[2]} />
                                    </Pie>
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                            border: `1px solid ${BRAND_COLORS.orange[600]}`,
                                        }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>
                </div>

                {/* Team Attendance Table */}
                <Card className="bg-white dark:bg-black-900 border border-orange-200 dark:border-orange-700">
                    <div className="p-6">
                        <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4">حضور الفريق</h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b-2 border-orange-200 dark:border-orange-700">
                                        <th className="text-right py-3 px-4 font-semibold text-black-700 dark:text-black-200">الموظف</th>
                                        <th className="text-right py-3 px-4 font-semibold text-black-700 dark:text-black-200">الحالة</th>
                                        <th className="text-right py-3 px-4 font-semibold text-black-700 dark:text-black-200">وقت الحضور</th>
                                        <th className="text-right py-3 px-4 font-semibold text-black-700 dark:text-black-200">وقت الانصراف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {teamAttendance.map((record) => (
                                        <tr
                                            key={record.id}
                                            className="border-b border-black-100 dark:border-black-800 hover:bg-orange-50 dark:hover:bg-black-800 transition"
                                        >
                                            <td className="py-3 px-4 text-black-800 dark:text-black-200">{record.employee_name}</td>
                                            <td className="py-3 px-4">
                                                <span className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(record.status)}`}>
                                                    {getStatusLabel(record.status)}
                                                </span>
                                            </td>
                                            <td className="py-3 px-4 text-black-800 dark:text-black-200">
                                                {record.check_in_time ? format(new Date(record.check_in_time), 'HH:mm') : '-'}
                                            </td>
                                            <td className="py-3 px-4 text-black-800 dark:text-black-200">
                                                {record.check_out_time ? format(new Date(record.check_out_time), 'HH:mm') : '-'}
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
