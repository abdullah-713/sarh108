import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Users, Clock, AlertTriangle, TrendingUp, Search, Filter } from 'lucide-react';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface ManagerDashboardData {
    total_employees: number;
    present_today: number;
    absent_today: number;
    late_today: number;
    on_break: number;
    alerts: any[];
    attendance_trend: any[];
    department_stats: any[];
    recent_activities: any[];
}

export default function ManagerDashboard() {
    const [data, setData] = useState<ManagerDashboardData | null>(null);
    const [loading, setLoading] = useState(true);
    const [selectedDepartment, setSelectedDepartment] = useState<string>('all');
    const [searchTerm, setSearchTerm] = useState('');
    const [dateRange, setDateRange] = useState({
        start: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
        end: new Date(),
    });

    useEffect(() => {
        fetchDashboardData();
        const interval = setInterval(fetchDashboardData, 60000); // Refresh every minute
        return () => clearInterval(interval);
    }, [selectedDepartment, dateRange]);

    const fetchDashboardData = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/v1/manager/dashboard', {
                params: {
                    department_id: selectedDepartment !== 'all' ? selectedDepartment : undefined,
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

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen bg-gray-50">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const stats = [
        {
            title: 'إجمالي الموظفين',
            value: data?.total_employees || 0,
            icon: Users,
            color: 'bg-blue-500',
        },
        {
            title: 'الحاضرون اليوم',
            value: data?.present_today || 0,
            icon: Clock,
            color: 'bg-green-500',
        },
        {
            title: 'الغائبون',
            value: data?.absent_today || 0,
            icon: AlertTriangle,
            color: 'bg-red-500',
        },
        {
            title: 'المتأخرون',
            value: data?.late_today || 0,
            icon: TrendingUp,
            color: 'bg-yellow-500',
        },
    ];

    const COLORS = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6'];

    return (
        <div className="min-h-screen bg-gray-50" dir="rtl">
            {/* Header */}
            <div className="bg-white shadow-sm sticky top-0 z-10">
                <div className="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">لوحة تحكم المدير</h1>
                            <p className="text-gray-600 mt-1">إدارة وتتبع حضور الموظفين</p>
                        </div>

                        {/* Filters */}
                        <div className="flex gap-2">
                            <div className="relative">
                                <Search className="absolute right-3 top-3 text-gray-400" size={20} />
                                <input
                                    type="text"
                                    placeholder="بحث عن موظف..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pr-10 pl-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <select
                                value={selectedDepartment}
                                onChange={(e) => setSelectedDepartment(e.target.value)}
                                className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="all">جميع الأقسام</option>
                                <option value="maintenance">الصيانة</option>
                                <option value="management">الإدارة</option>
                            </select>
                            <Filter className="text-gray-400 self-center" size={20} />
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    {stats.map((stat, index) => {
                        const Icon = stat.icon;
                        return (
                            <div key={index} className="bg-white rounded-lg shadow p-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-gray-600 text-sm mb-1">{stat.title}</p>
                                        <p className="text-3xl font-bold text-gray-900">{stat.value}</p>
                                    </div>
                                    <div className={`${stat.color} p-3 rounded-lg`}>
                                        <Icon className="text-white" size={24} />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Charts Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {/* Attendance Trend */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">اتجاه الحضور</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <LineChart data={data?.attendance_trend || []}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="date" />
                                <YAxis />
                                <Tooltip />
                                <Legend />
                                <Line type="monotone" dataKey="present" stroke="#10b981" name="حاضر" />
                                <Line type="monotone" dataKey="absent" stroke="#ef4444" name="غائب" />
                                <Line type="monotone" dataKey="late" stroke="#f59e0b" name="متأخر" />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Department Distribution */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">توزيع الأقسام</h2>
                        <ResponsiveContainer width="100%" height={300}>
                            <PieChart>
                                <Pie
                                    data={data?.department_stats || []}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={false}
                                    label={({ name, value }) => `${name}: ${value}`}
                                    outerRadius={80}
                                    fill="#8884d8"
                                    dataKey="count"
                                >
                                    {(data?.department_stats || []).map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Alerts & Notifications */}
                {data?.alerts && data.alerts.length > 0 && (
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-bold text-gray-900 mb-4">التنبيهات النشطة</h2>
                        <div className="space-y-3">
                            {data.alerts.slice(0, 5).map((alert, index) => (
                                <div key={index} className="flex items-start p-4 bg-amber-50 border-r-4 border-amber-500 rounded">
                                    <AlertTriangle className="text-amber-600 mt-1 mr-3" size={20} />
                                    <div className="flex-1">
                                        <p className="font-semibold text-gray-900">{alert.message}</p>
                                        <p className="text-sm text-gray-600">
                                            {alert.employee_name} - {new Date(alert.alert_time).toLocaleString('ar-SA')}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Recent Activities */}
                <div className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-bold text-gray-900 mb-4">الأنشطة الحديثة</h2>
                    <div className="space-y-3">
                        {data?.recent_activities?.slice(0, 10).map((activity, index) => (
                            <div key={index} className="flex items-center justify-between py-3 border-b last:border-b-0">
                                <div>
                                    <p className="font-medium text-gray-900">{activity.employee_name}</p>
                                    <p className="text-sm text-gray-600">{activity.action}</p>
                                </div>
                                <span className="text-sm text-gray-500">
                                    {new Date(activity.timestamp).toLocaleTimeString('ar-SA')}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
