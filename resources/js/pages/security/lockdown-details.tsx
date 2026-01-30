import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Lock, ArrowRight, Building2, Clock, User, Users, Shield, AlertTriangle } from 'lucide-react';

interface LockdownEvent {
    id: number;
    branch: { id: number; name: string } | null;
    initiator: { id: number; name: string };
    ender: { id: number; name: string } | null;
    title: string;
    title_ar: string | null;
    description: string | null;
    lockdown_type: string;
    lockdown_type_name: string;
    status: string;
    status_name: string;
    status_color: string;
    start_time: string;
    end_time: string | null;
    actual_end_time: string | null;
    notification_message: string | null;
    notification_message_ar: string | null;
    allow_emergency_checkin: boolean;
    allow_emergency_checkout: boolean;
    attendance_logs: AttendanceLog[];
    exempt_employees: Employee[];
    created_at: string;
}

interface AttendanceLog {
    id: number;
    employee: { id: number; first_name: string; last_name: string };
    action: string;
    was_allowed: boolean;
    attempted_at: string;
    reason: string | null;
}

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
}

interface Props {
    lockdown: LockdownEvent;
}

export default function LockdownDetails({ lockdown }: Props) {
    return (
        <AppLayout>
            <Head title={`تفاصيل الإغلاق #${lockdown.id}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        onClick={() => router.get('/security/lockdown')}
                    >
                        <ArrowRight className="w-5 h-5" />
                    </Button>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Lock className="w-8 h-8 text-orange-500" />
                            {lockdown.title_ar || lockdown.title}
                        </h1>
                        <p className="mt-1 text-gray-600">
                            تفاصيل حدث الإغلاق
                        </p>
                    </div>
                    <Badge className={lockdown.status_color}>
                        {lockdown.status_name}
                    </Badge>
                </div>

                {/* Active Alert */}
                {lockdown.status === 'active' && (
                    <Card className="border-red-500 bg-red-50">
                        <CardContent className="pt-6">
                            <div className="flex items-center gap-3">
                                <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center animate-pulse">
                                    <AlertTriangle className="w-6 h-6 text-red-600" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-red-600">هذا الإغلاق نشط حالياً</h3>
                                    <p className="text-red-600 text-sm">
                                        يتم منع تسجيل الحضور/الانصراف للموظفين
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>معلومات الإغلاق</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm text-gray-500">نوع الإغلاق</p>
                                        <Badge variant="outline">{lockdown.lockdown_type_name}</Badge>
                                    </div>
                                    {lockdown.branch && (
                                        <div>
                                            <p className="text-sm text-gray-500">الفرع</p>
                                            <p className="flex items-center gap-1">
                                                <Building2 className="w-4 h-4 text-gray-400" />
                                                {lockdown.branch.name}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <p className="text-sm text-gray-500">وقت البدء</p>
                                        <p className="flex items-center gap-1">
                                            <Clock className="w-4 h-4 text-gray-400" />
                                            {new Date(lockdown.start_time).toLocaleString('ar-SA')}
                                        </p>
                                    </div>
                                    {lockdown.end_time && (
                                        <div>
                                            <p className="text-sm text-gray-500">وقت الانتهاء المخطط</p>
                                            <p>{new Date(lockdown.end_time).toLocaleString('ar-SA')}</p>
                                        </div>
                                    )}
                                    {lockdown.actual_end_time && (
                                        <div>
                                            <p className="text-sm text-gray-500">وقت الانتهاء الفعلي</p>
                                            <p className="text-green-600">
                                                {new Date(lockdown.actual_end_time).toLocaleString('ar-SA')}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {lockdown.description && (
                                    <div className="pt-4 border-t">
                                        <p className="text-sm text-gray-500 mb-2">الوصف</p>
                                        <p className="bg-gray-50 p-3 rounded-lg">{lockdown.description}</p>
                                    </div>
                                )}

                                {lockdown.notification_message_ar && (
                                    <div>
                                        <p className="text-sm text-gray-500 mb-2">رسالة الإشعار</p>
                                        <p className="bg-orange-50 p-3 rounded-lg text-orange-800">
                                            {lockdown.notification_message_ar}
                                        </p>
                                    </div>
                                )}

                                <div className="pt-4 border-t grid grid-cols-2 gap-4">
                                    <div className="flex items-center gap-2">
                                        <Shield className={`w-5 h-5 ${lockdown.allow_emergency_checkin ? 'text-green-500' : 'text-red-500'}`} />
                                        <span>
                                            الحضور الطارئ: {lockdown.allow_emergency_checkin ? 'مسموح' : 'ممنوع'}
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Shield className={`w-5 h-5 ${lockdown.allow_emergency_checkout ? 'text-green-500' : 'text-red-500'}`} />
                                        <span>
                                            الانصراف الطارئ: {lockdown.allow_emergency_checkout ? 'مسموح' : 'ممنوع'}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Attendance Logs */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="w-5 h-5" />
                                    محاولات الحضور/الانصراف
                                </CardTitle>
                                <CardDescription>
                                    المحاولات التي تمت أثناء فترة الإغلاق
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {lockdown.attendance_logs.length === 0 ? (
                                    <p className="text-center text-gray-500 py-8">
                                        لا توجد محاولات مسجلة
                                    </p>
                                ) : (
                                    <div className="space-y-3">
                                        {lockdown.attendance_logs.map((log) => (
                                            <div
                                                key={log.id}
                                                className={`p-3 rounded-lg border ${
                                                    log.was_allowed ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <User className={`w-5 h-5 ${log.was_allowed ? 'text-green-600' : 'text-red-600'}`} />
                                                        <div>
                                                            <p className="font-medium">
                                                                {log.employee.first_name} {log.employee.last_name}
                                                            </p>
                                                            <p className="text-sm text-gray-500">
                                                                {log.action === 'checkin' ? 'محاولة حضور' : 'محاولة انصراف'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div className="text-left">
                                                        <Badge variant={log.was_allowed ? 'default' : 'destructive'}>
                                                            {log.was_allowed ? 'مسموح' : 'ممنوع'}
                                                        </Badge>
                                                        <p className="text-xs text-gray-500 mt-1">
                                                            {new Date(log.attempted_at).toLocaleTimeString('ar-SA')}
                                                        </p>
                                                    </div>
                                                </div>
                                                {log.reason && (
                                                    <p className="text-sm text-gray-600 mt-2 pr-8">{log.reason}</p>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Initiator Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="w-5 h-5" />
                                    المنفذ
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="font-medium">{lockdown.initiator.name}</p>
                                <p className="text-sm text-gray-500 mt-1">
                                    {new Date(lockdown.created_at).toLocaleString('ar-SA')}
                                </p>
                                {lockdown.ender && (
                                    <div className="mt-4 pt-4 border-t">
                                        <p className="text-sm text-gray-500">أنهى بواسطة</p>
                                        <p className="font-medium text-green-600">{lockdown.ender.name}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Exempt Employees */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="w-5 h-5" />
                                    الموظفين المستثنين
                                </CardTitle>
                                <CardDescription>
                                    موظفين مسموح لهم بالحضور/الانصراف
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {lockdown.exempt_employees.length === 0 ? (
                                    <p className="text-center text-gray-500 py-4">
                                        لا يوجد موظفين مستثنين
                                    </p>
                                ) : (
                                    <div className="space-y-2">
                                        {lockdown.exempt_employees.map((employee) => (
                                            <div
                                                key={employee.id}
                                                className="flex items-center gap-2 p-2 rounded bg-green-50"
                                            >
                                                <User className="w-4 h-4 text-green-600" />
                                                <span>{employee.first_name} {employee.last_name}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
