import React from 'react';
import { PageTemplate } from '@/components/page-template';
import { RefreshCw, Users, Building2, Calendar, Clock, TrendingUp, BarChart3, Bell } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, BarChart, Bar, XAxis, YAxis, CartesianGrid, Legend, LineChart, Line, AreaChart, Area } from 'recharts';
import { format } from 'date-fns';

interface CompanyDashboardData {
  stats: {
    totalEmployees: number;
    totalBranches: number;
    totalDepartments: number;
    newEmployeesThisMonth: number;
    attendanceRate: number;
    presentToday: number;
    pendingLeaves: number;
    onLeaveToday: number;
  };
  charts: {
    departmentStats: Array<{name: string; value: number; color: string}>;
    hiringTrend: Array<{month: string; hires: number}>;
    leaveTypesStats: Array<{name: string; value: number; color: string}>;
    employeeGrowthChart: Array<{month: string; employees: number}>;
  };
  recentActivities: {
    leaves: Array<any>;
    announcements: Array<any>;
  };
  userType: string;
}

interface PageAction {
  label: string;
  icon: React.ReactNode;
  variant: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
  onClick: () => void;
}

export default function Dashboard({ dashboardData }: { dashboardData: CompanyDashboardData }) {
  const { t } = useTranslation();
  const { auth } = usePage().props as any;

  const pageActions: PageAction[] = [
    {
      label: t('Refresh'),
      icon: <RefreshCw className="h-4 w-4" />,
      variant: 'outline',
      onClick: () => window.location.reload()
    }
  ];

  const stats = dashboardData?.stats || {
    totalEmployees: 0,
    totalBranches: 0,
    totalDepartments: 0,
    newEmployeesThisMonth: 0,
    attendanceRate: 0,
    presentToday: 0,
    pendingLeaves: 0,
    onLeaveToday: 0
  };

  const charts = {
    departmentStats: [],
    hiringTrend: [],
    leaveTypesStats: [],
    employeeGrowthChart: [],
    ...(dashboardData?.charts ?? {})
  };



  const recentActivities = {
    leaves: [],
    announcements: [],
    ...(dashboardData?.recentActivities ?? {})
  };

  const userType = dashboardData?.userType || 'employee';
  const isCompanyUser = userType === 'company';
  
  const getStatusColor = (status: string) => {
    const colors = {
      'approved': 'bg-green-50 text-green-700 ring-green-600/20',
      'pending': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
      'rejected': 'bg-red-50 text-red-700 ring-red-600/20',
      'New': 'bg-blue-50 text-blue-700 ring-blue-600/20',
      'Screening': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
      'Interview': 'bg-purple-50 text-purple-700 ring-purple-600/20',
      'Hired': 'bg-green-50 text-green-700 ring-green-600/20',
      'Rejected': 'bg-red-50 text-red-700 ring-red-600/20'
    };
    return colors[status] || 'bg-gray-50 text-gray-700 ring-gray-600/20';
  };

  return (
    <PageTemplate 
      title={t('Dashboard')}
      url="/dashboard"
      actions={pageActions}
    >
      <div className="space-y-6">
        {/* Key Metrics - Modern Design */}
        <div className="grid gap-4 sm:gap-6 grid-cols-2 lg:grid-cols-4">
          <Card className="group overflow-hidden">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-1 sm:space-y-2">
                  <p className="text-xs sm:text-sm font-medium text-muted-foreground">{t('Total Employees')}</p>
                  <p className="text-xl sm:text-3xl font-bold bg-gradient-to-r from-orange-500 to-orange-600 bg-clip-text text-transparent">{stats.totalEmployees}</p>
                  {isCompanyUser && (
                    <p className="text-xs text-green-600 font-medium flex items-center gap-1">
                      <TrendingUp className="h-3 w-3" />
                      +{stats.newEmployeesThisMonth} {t('this month')}
                    </p>
                  )}
                </div>
                <div className="rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 p-2.5 sm:p-3 shadow-lg shadow-orange-500/25 group-hover:scale-110 transition-transform duration-300">
                  <Users className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="group overflow-hidden">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-1 sm:space-y-2">
                  <p className="text-xs sm:text-sm font-medium text-muted-foreground">{t('Branches')}</p>
                  <p className="text-xl sm:text-3xl font-bold">{stats.totalBranches}</p>
                  <p className="text-xs text-muted-foreground">{stats.totalDepartments} {t('departments')}</p>
                </div>
                <div className="rounded-xl bg-gradient-to-br from-gray-800 to-gray-900 p-2.5 sm:p-3 shadow-lg shadow-black/25 group-hover:scale-110 transition-transform duration-300">
                  <Building2 className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="group overflow-hidden">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-1 sm:space-y-2">
                  <p className="text-xs sm:text-sm font-medium text-muted-foreground">{t('Attendance Rate')}</p>
                  <p className="text-xl sm:text-3xl font-bold">{stats.attendanceRate}%</p>
                  <p className="text-xs text-muted-foreground">{stats.presentToday} {t('present today')}</p>
                </div>
                <div className="rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 p-2.5 sm:p-3 shadow-lg shadow-orange-400/25 group-hover:scale-110 transition-transform duration-300">
                  <Clock className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="group overflow-hidden">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-1 sm:space-y-2">
                  <p className="text-xs sm:text-sm font-medium text-muted-foreground">{t('Pending Leaves')}</p>
                  <p className="text-xl sm:text-3xl font-bold">{stats.pendingLeaves}</p>
                  <p className="text-xs text-muted-foreground">{stats.onLeaveToday} {t('on leave today')}</p>
                </div>
                <div className="rounded-xl bg-gradient-to-br from-gray-700 to-gray-800 p-2.5 sm:p-3 shadow-lg shadow-black/25 group-hover:scale-110 transition-transform duration-300">
                  <Calendar className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
              </div>
            </CardContent>
          </Card>

        </div>

        {/* Charts Section */}
        <div className="grid gap-6 lg:grid-cols-2">
          {/* Department Distribution Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                <BarChart3 className="h-5 w-5" />
                {t('Department Distribution')}
              </CardTitle>
            </CardHeader>
            <CardContent>
              {charts.departmentStats.length > 0 ? (
                <ResponsiveContainer width="100%" height={200}>
                  <PieChart>
                    <Pie
                      data={charts.departmentStats}
                      cx="50%"
                      cy="50%"
                      innerRadius={40}
                      outerRadius={80}
                      dataKey="value"
                    >
                      {charts.departmentStats.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  {t('No department data available')}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Hiring Trend Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                <TrendingUp className="h-5 w-5" />
                {t('Hiring Trend (6 Months)')}
              </CardTitle>
            </CardHeader>
            <CardContent>
              {charts.hiringTrend.length > 0 ? (
                <ResponsiveContainer width="100%" height={200}>
                  <BarChart data={charts.hiringTrend}>
                    <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" />
                    <XAxis dataKey="month" stroke="var(--muted-foreground)" fontSize={12} />
                    <YAxis stroke="var(--muted-foreground)" fontSize={12} />
                    <Tooltip 
                      contentStyle={{ 
                        backgroundColor: 'var(--card)', 
                        border: '1px solid var(--border)',
                        borderRadius: '12px',
                        boxShadow: '0 10px 25px -5px rgba(0,0,0,0.1)'
                      }} 
                    />
                    <Bar dataKey="hires" fill="url(#orangeGradient)" radius={[6, 6, 0, 0]} />
                    <defs>
                      <linearGradient id="orangeGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#ff8531" />
                        <stop offset="100%" stopColor="#e67228" />
                      </linearGradient>
                    </defs>
                  </BarChart>
                </ResponsiveContainer>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  {t('No hiring data available')}
                </div>
              )}
            </CardContent>
          </Card>


          {/* Leave Types Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                <Calendar className="h-5 w-5" />
                {t('Leave Types')}
              </CardTitle>
            </CardHeader>
            <CardContent>
              {charts.leaveTypesStats.length > 0 ? (
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={charts.leaveTypesStats}
                      cx="50%"
                      cy="50%"
                      innerRadius={50}
                      outerRadius={80}
                      dataKey="value"
                    >
                      {charts.leaveTypesStats.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip />
                    <Legend wrapperStyle={{ fontSize: '12px' }} />
                  </PieChart>
                </ResponsiveContainer>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  {t('No leave types available')}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Recent Activities */}
        <div className="grid gap-6 lg:grid-cols-2">
          {/* Recent Leave Applications */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center justify-between text-lg font-semibold">
                <div className="flex items-center gap-2">
                  <Calendar className="h-5 w-5" />
                  {t('Recent Leave Applications')}
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="secondary">{recentActivities.leaves.length}</Badge>
                  <button 
                    onClick={() => window.location.href = route('hr.leave-applications.index')}
                    className="px-2 py-1 text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md font-medium transition-colors"
                  >
                    {t('View All')}
                  </button>
                </div>
              </CardTitle>
            </CardHeader>
            <CardContent>
              {recentActivities.leaves.length > 0 ? (
                <div className="space-y-3 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                  {recentActivities.leaves.map((leave, index) => (
                    <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <p className="font-medium">{leave.employee?.name || 'Employee'}</p>
                          <Badge variant="outline" className={`text-xs ring-1 ring-inset ${getStatusColor(leave.status)}`}>
                            {leave.status}
                          </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                          {leave.leave_type?.name || 'Leave'} • {(() => {
                            try {
                              return leave.start_date ? format(new Date(leave.start_date), 'MMM dd') : 'N/A';
                            } catch {
                              return 'Invalid date';
                            }
                          })()} - {(() => {
                            try {
                              return leave.end_date ? format(new Date(leave.end_date), 'MMM dd') : 'N/A';
                            } catch {
                              return 'Invalid date';
                            }
                          })()}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  {t('No recent leave applications')}
                </div>
              )}
            </CardContent>
          </Card>


          {/* Recent Announcements */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center justify-between text-lg font-semibold">
                <div className="flex items-center gap-2">
                  <Bell className="h-5 w-5" />
                  {t('Recent Announcements')}
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="secondary">{recentActivities.announcements.length}</Badge>
                  <button 
                    onClick={() => window.location.href = route('hr.announcements.index')}
                    className="px-2 py-1 text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md font-medium transition-colors"
                  >
                    {t('View All')}
                  </button>
                </div>
              </CardTitle>
            </CardHeader>
            <CardContent>
              {recentActivities.announcements.length > 0 ? (
                <div className="space-y-3 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                  {recentActivities.announcements.map((announcement, index) => (
                    <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <p className="font-medium">{announcement.title}</p>
                          {announcement.is_high_priority && (
                            <Badge variant="outline" className="text-xs ring-1 ring-inset bg-red-50 text-red-700 ring-red-600/20">
                              High Priority
                            </Badge>
                          )}
                        </div>
                        <p className="text-sm text-muted-foreground">
                          {announcement.category} • {(() => {
                            try {
                              return announcement.created_at ? format(new Date(announcement.created_at), 'MMM dd, yyyy') : 'N/A';
                            } catch {
                              return 'Invalid date';
                            }
                          })()}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  {t('No recent announcements')}
                </div>
              )}
            </CardContent>
          </Card>

        </div>

        {/* Employee Growth Chart - Full Width */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg font-semibold">
              <TrendingUp className="h-5 w-5" />
              {t('Employee Growth')} ({new Date().getFullYear()})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {charts.employeeGrowthChart.length > 0 ? (
              <ResponsiveContainer width="100%" height={400}>
                <AreaChart data={charts.employeeGrowthChart}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                  <XAxis dataKey="month" stroke="#6b7280" />
                  <YAxis stroke="#6b7280" />
                  <Tooltip 
                    contentStyle={{ 
                      backgroundColor: '#ffffff', 
                      border: '1px solid #e5e7eb', 
                      borderRadius: '8px',
                      boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                    }} 
                  />
                  <Area 
                    type="monotone" 
                    dataKey="employees" 
                    stroke="#3b82f6" 
                    strokeWidth={3}
                    fillOpacity={0.2} 
                    fill="#3b82f6"
                    dot={{ fill: '#3b82f6', strokeWidth: 2, r: 5 }}
                  />
                </AreaChart>
              </ResponsiveContainer>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                {t('No employee growth data available')}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </PageTemplate>
  );
}