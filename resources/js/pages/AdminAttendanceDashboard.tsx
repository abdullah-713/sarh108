import React, { useState, useEffect } from 'react';
import axios from 'axios';
import {
    BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
    ComposedChart, Area, AreaChart
} from 'recharts';
import { Download, Settings, TrendingUp, Users, Calendar, AlertTriangle } from 'lucide-react';

interface AdminDashboardData {
    summary: {
        total_employees: number;
        total_present_today: number;
        total_absent_today: number;
        total_late_today: number;
        total_on_break: number;
        average_working_hours: number;
        total_overtime_hours: number;
    };
    attendance_by_branch: any[];
    department_performance: any[];
    hourly_attendance: any[];
    monthly_trends: any[];
    overtime_summary: any[];
    critical_alerts: any[];
    compliance_score: number;
}

export default function AdminAttendanceDashboard() {
    const [data, setData] = useState<AdminDashboardData | null>(null);
    const [loading, setLoading] = useState(true);
    const [dateRange, setDateRange] = useState({
        start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
        end: new Date(),
    });
    const [reportFormat, setReportFormat] = useState<'pdf' | 'excel'>('pdf');

    useEffect(() => {
        fetchDashboardData();
    }, [dateRange]);

    const fetchDashboardData = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/v1/admin/attendance-dashboard', {
                params: {
                    start_date: dateRange.start.toISOString().split('T')[0],
                    end_date: dateRange.end.toISOString().split('T')[0],
                },
            });
            setData(response.data.data);
        } catch (error) {
            console.error('Failed to fetch dashboard data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExportReport = async () => {
        try {
            const response = await axios.post('/api/v1/admin/attendance-report/export', {
                format: reportFormat,
                start_date: dateRange.start.toISOString().split('T')[0],
                end_date: dateRange.end.toISOString().split('T')[0],
            }, {
                responseType: reportFormat === 'pdf' ? 'blob' : 'blob',
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `attendance-report.${reportFormat === 'pdf' ? 'pdf' : 'xlsx'}`);
            document.body.appendChild(link);
            link.click();
            link.parentNode?.removeChild(link);
        } catch (error) {
            console.error('Failed to export report:', error);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen bg-gray-50">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const COLORS = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#06b6d4'];

    return (
        <div className="min-h-screen bg-gray-50" dir="rtl">
            {/* Header */}
            <div className="bg-white shadow-sm sticky top-0 z-10">
                <div className="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">لوحة تحكم الحضور والانصراف</h1>
                            <p className="text-gray-600 mt-1">إحصائيات وتقارير شاملة لنظام الحضور</p>
                        </div>

                        <div className="flex gap-2">
                            <div className="flex gap-1">
                                <select
                                    value={reportFormat}
                                    onChange={(e) => setReportFormat(e.target.value as 'pdf' | 'excel')}
                                    className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                >
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                </select>
                                <button
                                    onClick={handleExportReport}
                                    className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    <Download size={18} />
                                    تصدير تقرير
                                </button>
                            </div>
                            <button className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <Settings size={20} className="text-gray-600" />
                            </button>
                        </div>
                    </div>

                    {/* Date Range */}
                    <div className="flex gap-2 mt-4">
                        <input
                            type="date"
                            value={dateRange.start.toISOString().split('T')[0]}
                            onChange={(e) => setDateRange(prev => ({
                                ...prev,
                                start: new Date(e.target.value),
                            }))}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                        <span className="self-center text-gray-600">إلى</span>
                        <input
                            type="date"
                            value={dateRange.end.toISOString().split('T')[0]}
                            onChange={(e) => setDateRange(prev => ({
                                ...prev,
                                end: new Date(e.target.value),
                            }))}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                {/* Key Metrics */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm mb-1">إجمالي الموظفين</p>
                                <p className="text-3xl font-bold text-gray-900">{data?.summary.total_employees}</p>
                            </div>
                            <Users className="text-blue-500" size={32} />
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm mb-1">حاضرون اليوم</p>
                                <p className="text-3xl font-bold text-green-600">{data?.summary.total_present_today}</p>
                            </div>
                            <TrendingUp className="text-green-500" size={32} />
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm mb-1">ساعات العمل المتوسطة</p>
                                <p className="text-3xl font-bold text-blue-600">
                                    {data?.summary.average_working_hours.toFixed(1)}
                                </p>
                            </div>
                            <Calendar className="text-blue-500" size={32} />
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm mb-1">درجة الامتثال</p>
                                <p className="text-3xl font-bold text-purple-600">
                                    {data?.compliance_score}%
                                </p>
                            </div>
                            <AlertTriangle className="text-purple-500" size={32} />
                        </div>
                    </div>
                </div>

                {/* Charts Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {/* Monthly Trends */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">الاتجاهات الشهرية</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <AreaChart data={data?.monthly_trends || []}>
                                <defs>
                                    <linearGradient id="colorPresent" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%" stopColor="#10b981" stopOpacity={0.8}/>
                                        <stop offset="95%" stopColor="#10b981" stopOpacity={0}/>
                                    </linearGradient>
                                </defs>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="date" />
                                <YAxis />
                                <Tooltip />
                                <Area type="monotone" dataKey="present" stroke="#10b981" fillOpacity={1} fill="url(#colorPresent)" />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Department Performance */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">أداء الأقسام</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <BarChart data={data?.department_performance || []}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="name" />
                                <YAxis />
                                <Tooltip />
                                <Legend />
                                <Bar dataKey="present" fill="#10b981" name="حاضر" />
                                <Bar dataKey="absent" fill="#ef4444" name="غائب" />
                                <Bar dataKey="late" fill="#f59e0b" name="متأخر" />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Hourly Distribution */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">توزيع الحضور بالساعة</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <LineChart data={data?.hourly_attendance || []}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="hour" />
                                <YAxis />
                                <Tooltip />
                                <Legend />
                                <Line type="monotone" dataKey="count" stroke="#3b82f6" name="عدد الموظفين" />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Overtime Summary */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">ملخص الساعات الإضافية</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <PieChart>
                                <Pie
                                    data={data?.overtime_summary || []}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={false}
                                    label={({ name, value }) => `${name}: ${value}h`}
                                    outerRadius={80}
                                    fill="#8884d8"
                                    dataKey="hours"
                                >
                                    {(data?.overtime_summary || []).map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Critical Alerts */}
                {data?.critical_alerts && data.critical_alerts.length > 0 && (
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">التنبيهات الحرجة</h2>
                        <div className="space-y-3">
                            {data.critical_alerts.slice(0, 10).map((alert, index) => (
                                <div key={index} className="flex items-start p-4 bg-red-50 border-r-4 border-red-500 rounded">
                                    <AlertTriangle className="text-red-600 mt-1 mr-3" size={20} />
                                    <div className="flex-1">
                                        <p className="font-semibold text-gray-900">{alert.message}</p>
                                        <p className="text-sm text-gray-600">{alert.details}</p>
                                        <p className="text-xs text-gray-500 mt-1">
                                            {new Date(alert.timestamp).toLocaleString('ar-SA')}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
