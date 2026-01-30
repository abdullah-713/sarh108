import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Shield, AlertTriangle, CheckCircle, XCircle, Eye, User, MapPin, Clock, Smartphone, Globe, RefreshCw, TrendingUp } from 'lucide-react';

interface LivenessStats {
    total: number;
    passed: number;
    failed: number;
    spoofing_attempts: number;
    pass_rate: number;
    spoofing_rate: number;
}

interface TamperStats {
    total: number;
    pending: number;
    confirmed: number;
    high_severity: number;
    by_type: Record<string, number>;
}

interface TamperLog {
    id: number;
    employee: { id: number; first_name: string; last_name: string } | null;
    branch: { id: number; name: string } | null;
    tamper_type: string;
    tamper_type_name: string;
    severity: string;
    severity_name: string;
    severity_color: string;
    confidence_score: number;
    description: string;
    review_status: string;
    action_taken: string;
    ip_address: string;
    created_at: string;
}

interface RepeatOffender {
    employee_id: number;
    occurrences: number;
    employee: { id: number; first_name: string; last_name: string };
}

interface Props {
    livenessStats: LivenessStats;
    tamperStats: TamperStats;
    recentTampers: TamperLog[];
    repeatOffenders: RepeatOffender[];
}

const tamperTypeIcons: Record<string, typeof MapPin> = {
    gps_spoof: MapPin,
    photo_spoof: Eye,
    time_manipulation: Clock,
    device_clone: Smartphone,
    proxy_vpn: Globe,
    emulator: Smartphone,
    rooted_device: Smartphone,
};

const tamperTypeLabels: Record<string, string> = {
    gps_spoof: 'تزوير الموقع',
    photo_spoof: 'تزوير الصورة',
    time_manipulation: 'التلاعب بالوقت',
    device_clone: 'استنساخ الجهاز',
    proxy_vpn: 'استخدام VPN',
    multiple_accounts: 'حسابات متعددة',
    rooted_device: 'جهاز مكسور',
    emulator: 'محاكي',
    automation: 'أتمتة',
};

const severityColors: Record<string, string> = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800',
};

export default function SecurityDashboard({ livenessStats, tamperStats, recentTampers, repeatOffenders }: Props) {
    const handleReviewTamper = (tamperId: number, status: string) => {
        router.put(`/ai/security/tamper/${tamperId}/review`, { status });
    };

    return (
        <AppLayout>
            <Head title="لوحة الأمان" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Shield className="w-8 h-8 text-orange-500" />
                            لوحة الأمان
                        </h1>
                        <p className="mt-1 text-gray-600">
                            مراقبة فحوصات الحيوية ومحاولات التلاعب
                        </p>
                    </div>

                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => router.get('/ai/security/liveness-logs')}>
                            سجل الفحوصات
                        </Button>
                        <Button variant="outline" onClick={() => router.get('/ai/security/tamper-logs')}>
                            سجل التلاعب
                        </Button>
                    </div>
                </div>

                {/* Liveness Stats */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">إجمالي الفحوصات</p>
                                <p className="text-3xl font-bold text-gray-900">{livenessStats.total}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">ناجحة</p>
                                <p className="text-3xl font-bold text-green-600">{livenessStats.passed}</p>
                                <p className="text-xs text-green-500">{livenessStats.pass_rate}%</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">فاشلة</p>
                                <p className="text-3xl font-bold text-yellow-600">{livenessStats.failed}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">محاولات تزوير</p>
                                <p className="text-3xl font-bold text-red-600">{livenessStats.spoofing_attempts}</p>
                                <p className="text-xs text-red-500">{livenessStats.spoofing_rate}%</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-green-50 to-emerald-50">
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <CheckCircle className="w-8 h-8 mx-auto text-green-500 mb-1" />
                                <p className="text-sm text-gray-600">نسبة النجاح</p>
                                <p className="text-2xl font-bold text-green-600">{livenessStats.pass_rate}%</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Tamper Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">محاولات التلاعب</p>
                                    <p className="text-3xl font-bold text-gray-900">{tamperStats.total}</p>
                                </div>
                                <AlertTriangle className="w-10 h-10 text-orange-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">بانتظار المراجعة</p>
                                    <p className="text-3xl font-bold text-blue-600">{tamperStats.pending}</p>
                                </div>
                                <Clock className="w-10 h-10 text-blue-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">مؤكدة</p>
                                    <p className="text-3xl font-bold text-red-600">{tamperStats.confirmed}</p>
                                </div>
                                <XCircle className="w-10 h-10 text-red-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">خطورة عالية</p>
                                    <p className="text-3xl font-bold text-orange-600">{tamperStats.high_severity}</p>
                                </div>
                                <AlertTriangle className="w-10 h-10 text-red-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Tampers */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertTriangle className="w-5 h-5 text-orange-500" />
                                محاولات التلاعب الأخيرة
                            </CardTitle>
                            <CardDescription>آخر 10 محاولات مكتشفة</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentTampers.length === 0 ? (
                                <div className="text-center py-8 text-gray-500">
                                    <Shield className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                    <p>لا توجد محاولات تلاعب</p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {recentTampers.map((tamper) => {
                                        const Icon = tamperTypeIcons[tamper.tamper_type] || AlertTriangle;
                                        return (
                                            <div
                                                key={tamper.id}
                                                className="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50"
                                            >
                                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                                    tamper.severity === 'critical' ? 'bg-red-100' :
                                                    tamper.severity === 'high' ? 'bg-orange-100' :
                                                    'bg-yellow-100'
                                                }`}>
                                                    <Icon className={`w-5 h-5 ${
                                                        tamper.severity === 'critical' ? 'text-red-600' :
                                                        tamper.severity === 'high' ? 'text-orange-600' :
                                                        'text-yellow-600'
                                                    }`} />
                                                </div>

                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-medium">
                                                            {tamper.employee 
                                                                ? `${tamper.employee.first_name} ${tamper.employee.last_name}`
                                                                : 'غير معروف'}
                                                        </span>
                                                        <Badge className={severityColors[tamper.severity]}>
                                                            {tamper.severity_name}
                                                        </Badge>
                                                    </div>
                                                    <p className="text-sm text-gray-600">
                                                        {tamperTypeLabels[tamper.tamper_type] || tamper.tamper_type}
                                                    </p>
                                                    <p className="text-xs text-gray-400 mt-1">
                                                        {new Date(tamper.created_at).toLocaleString('ar-SA')}
                                                        {tamper.ip_address && ` • ${tamper.ip_address}`}
                                                    </p>
                                                </div>

                                                {tamper.review_status === 'pending' && (
                                                    <div className="flex gap-1">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="text-red-600"
                                                            onClick={() => handleReviewTamper(tamper.id, 'confirmed')}
                                                        >
                                                            تأكيد
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => handleReviewTamper(tamper.id, 'false_positive')}
                                                        >
                                                            رفض
                                                        </Button>
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Repeat Offenders & Stats */}
                    <div className="space-y-6">
                        {/* Repeat Offenders */}
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <User className="w-5 h-5 text-red-500" />
                                    المخالفين المتكررين
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {repeatOffenders.length === 0 ? (
                                    <p className="text-center text-gray-500 py-4">لا يوجد مخالفين متكررين</p>
                                ) : (
                                    <div className="space-y-2">
                                        {repeatOffenders.map((offender, index) => (
                                            <div key={offender.employee_id} className="flex items-center justify-between p-2 rounded bg-red-50">
                                                <div className="flex items-center gap-2">
                                                    <span className="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-xs font-bold text-red-600">
                                                        {index + 1}
                                                    </span>
                                                    <span className="font-medium text-sm">
                                                        {offender.employee.first_name} {offender.employee.last_name}
                                                    </span>
                                                </div>
                                                <Badge variant="destructive">
                                                    {offender.occurrences} مخالفة
                                                </Badge>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Tamper Types Distribution */}
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <TrendingUp className="w-5 h-5 text-orange-500" />
                                    توزيع أنواع التلاعب
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {Object.keys(tamperStats.by_type).length === 0 ? (
                                    <p className="text-center text-gray-500 py-4">لا توجد بيانات</p>
                                ) : (
                                    <div className="space-y-2">
                                        {Object.entries(tamperStats.by_type).map(([type, count]) => (
                                            <div key={type} className="flex items-center justify-between">
                                                <span className="text-sm text-gray-600">
                                                    {tamperTypeLabels[type] || type}
                                                </span>
                                                <Badge variant="outline">{count}</Badge>
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
