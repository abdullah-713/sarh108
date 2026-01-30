import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertTriangle, MapPin, Eye, Clock, Smartphone, Globe, User, CheckCircle, XCircle, ArrowRight } from 'lucide-react';
import { useState } from 'react';

interface TamperLog {
    id: number;
    employee_id: number;
    employee: { id: number; first_name: string; last_name: string } | null;
    branch: { id: number; name: string } | null;
    tamper_type: string;
    tamper_type_name: string;
    severity: string;
    severity_name: string;
    severity_color: string;
    confidence_score: number;
    description: string;
    evidence: object | null;
    review_status: string;
    reviewed_by: number | null;
    reviewer: { id: number; first_name: string; last_name: string } | null;
    action_taken: string | null;
    ip_address: string;
    created_at: string;
    reviewed_at: string | null;
}

interface PaginatedLogs {
    data: TamperLog[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    logs: PaginatedLogs;
    filters: {
        status: string;
        tamper_type: string | null;
        severity: string | null;
    };
}

const tamperTypeIcons: Record<string, typeof MapPin> = {
    gps_spoof: MapPin,
    photo_spoof: Eye,
    time_manipulation: Clock,
    device_clone: Smartphone,
    proxy_vpn: Globe,
    emulator: Smartphone,
    rooted_device: Smartphone,
    automation: Smartphone,
    multiple_accounts: User,
};

const tamperTypeLabels: Record<string, string> = {
    gps_spoof: 'تزوير الموقع GPS',
    photo_spoof: 'تزوير الصورة',
    time_manipulation: 'التلاعب بالوقت',
    device_clone: 'استنساخ الجهاز',
    proxy_vpn: 'استخدام VPN/Proxy',
    multiple_accounts: 'حسابات متعددة',
    rooted_device: 'جهاز مكسور (Root)',
    emulator: 'محاكي Android',
    automation: 'أداة أتمتة',
};

const severityColors: Record<string, string> = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800',
};

const severityNames: Record<string, string> = {
    low: 'منخفضة',
    medium: 'متوسطة',
    high: 'عالية',
    critical: 'حرجة',
};

const reviewStatusColors: Record<string, string> = {
    pending: 'bg-blue-100 text-blue-800',
    investigating: 'bg-purple-100 text-purple-800',
    confirmed: 'bg-red-100 text-red-800',
    false_positive: 'bg-gray-100 text-gray-800',
    escalated: 'bg-orange-100 text-orange-800',
};

const reviewStatusNames: Record<string, string> = {
    pending: 'بانتظار المراجعة',
    investigating: 'قيد التحقيق',
    confirmed: 'مؤكد',
    false_positive: 'إنذار كاذب',
    escalated: 'تم التصعيد',
};

export default function TamperLogs({ logs, filters }: Props) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status);
    const [selectedType, setSelectedType] = useState(filters.tamper_type || '');
    const [selectedSeverity, setSelectedSeverity] = useState(filters.severity || '');

    const handleFilter = () => {
        router.get('/ai/security/tamper-logs', {
            status: selectedStatus,
            tamper_type: selectedType || null,
            severity: selectedSeverity || null,
        }, { preserveState: true });
    };

    const handleReview = (tamperId: number, status: string, action?: string) => {
        router.put(`/ai/security/tamper/${tamperId}/review`, { 
            status,
            action_taken: action 
        });
    };

    return (
        <AppLayout>
            <Head title="سجل محاولات التلاعب" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <AlertTriangle className="w-8 h-8 text-orange-500" />
                            سجل محاولات التلاعب
                        </h1>
                        <p className="mt-1 text-gray-600">
                            جميع محاولات التلاعب المكتشفة
                        </p>
                    </div>

                    <Button variant="outline" onClick={() => router.get('/ai/security')}>
                        <ArrowRight className="w-4 h-4 ml-2" />
                        العودة للوحة الأمان
                    </Button>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-wrap items-center gap-4">
                            <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="حالة المراجعة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">الكل</SelectItem>
                                    <SelectItem value="pending">بانتظار المراجعة</SelectItem>
                                    <SelectItem value="investigating">قيد التحقيق</SelectItem>
                                    <SelectItem value="confirmed">مؤكد</SelectItem>
                                    <SelectItem value="false_positive">إنذار كاذب</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedType} onValueChange={setSelectedType}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="نوع التلاعب" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">الكل</SelectItem>
                                    <SelectItem value="gps_spoof">تزوير الموقع</SelectItem>
                                    <SelectItem value="photo_spoof">تزوير الصورة</SelectItem>
                                    <SelectItem value="proxy_vpn">VPN/Proxy</SelectItem>
                                    <SelectItem value="emulator">محاكي</SelectItem>
                                    <SelectItem value="rooted_device">جهاز مكسور</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedSeverity} onValueChange={setSelectedSeverity}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="الخطورة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">الكل</SelectItem>
                                    <SelectItem value="low">منخفضة</SelectItem>
                                    <SelectItem value="medium">متوسطة</SelectItem>
                                    <SelectItem value="high">عالية</SelectItem>
                                    <SelectItem value="critical">حرجة</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button onClick={handleFilter} variant="outline">
                                تطبيق الفلتر
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Logs List */}
                <Card>
                    <CardHeader>
                        <CardTitle>السجلات ({logs.total})</CardTitle>
                        <CardDescription>قائمة بجميع محاولات التلاعب</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {logs.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <AlertTriangle className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد سجلات</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {logs.data.map((log) => {
                                    const Icon = tamperTypeIcons[log.tamper_type] || AlertTriangle;
                                    return (
                                        <div
                                            key={log.id}
                                            className={`p-4 rounded-lg border-r-4 ${
                                                log.severity === 'critical' ? 'border-r-red-500 bg-red-50' :
                                                log.severity === 'high' ? 'border-r-orange-500 bg-orange-50' :
                                                log.severity === 'medium' ? 'border-r-yellow-500 bg-yellow-50' :
                                                'border-r-green-500 bg-green-50'
                                            }`}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex items-start gap-4">
                                                    <div className={`w-12 h-12 rounded-full flex items-center justify-center ${
                                                        log.severity === 'critical' ? 'bg-red-100' :
                                                        log.severity === 'high' ? 'bg-orange-100' :
                                                        log.severity === 'medium' ? 'bg-yellow-100' :
                                                        'bg-green-100'
                                                    }`}>
                                                        <Icon className={`w-6 h-6 ${
                                                            log.severity === 'critical' ? 'text-red-600' :
                                                            log.severity === 'high' ? 'text-orange-600' :
                                                            log.severity === 'medium' ? 'text-yellow-600' :
                                                            'text-green-600'
                                                        }`} />
                                                    </div>

                                                    <div>
                                                        <div className="flex items-center gap-2 mb-1">
                                                            <span className="font-semibold">
                                                                {log.employee 
                                                                    ? `${log.employee.first_name} ${log.employee.last_name}`
                                                                    : 'غير معروف'}
                                                            </span>
                                                            <Badge className={severityColors[log.severity]}>
                                                                {severityNames[log.severity]}
                                                            </Badge>
                                                            <Badge variant="outline">
                                                                {tamperTypeLabels[log.tamper_type] || log.tamper_type}
                                                            </Badge>
                                                        </div>

                                                        <p className="text-sm text-gray-600 mb-2">
                                                            {log.description}
                                                        </p>

                                                        <div className="flex items-center gap-4 text-sm text-gray-500">
                                                            <span className="flex items-center gap-1">
                                                                <Clock className="w-4 h-4" />
                                                                {new Date(log.created_at).toLocaleString('ar-SA')}
                                                            </span>
                                                            <span>
                                                                الثقة: {log.confidence_score}%
                                                            </span>
                                                            {log.ip_address && (
                                                                <span>
                                                                    IP: {log.ip_address}
                                                                </span>
                                                            )}
                                                            {log.branch && (
                                                                <span>
                                                                    الفرع: {log.branch.name}
                                                                </span>
                                                            )}
                                                        </div>

                                                        {log.reviewer && log.reviewed_at && (
                                                            <div className="mt-2 text-sm text-gray-500">
                                                                <span>تمت المراجعة بواسطة: {log.reviewer.first_name} {log.reviewer.last_name}</span>
                                                                {log.action_taken && (
                                                                    <span className="block mt-1">الإجراء: {log.action_taken}</span>
                                                                )}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>

                                                <div className="flex flex-col items-end gap-2">
                                                    <Badge className={reviewStatusColors[log.review_status]}>
                                                        {reviewStatusNames[log.review_status]}
                                                    </Badge>

                                                    {log.review_status === 'pending' && (
                                                        <div className="flex gap-1">
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="text-purple-600"
                                                                onClick={() => handleReview(log.id, 'investigating')}
                                                            >
                                                                تحقيق
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="text-red-600"
                                                                onClick={() => handleReview(log.id, 'confirmed', 'تم تأكيد التلاعب')}
                                                            >
                                                                <CheckCircle className="w-3 h-3" />
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => handleReview(log.id, 'false_positive')}
                                                            >
                                                                <XCircle className="w-3 h-3" />
                                                            </Button>
                                                        </div>
                                                    )}

                                                    {log.review_status === 'investigating' && (
                                                        <div className="flex gap-1">
                                                            <Button
                                                                size="sm"
                                                                variant="destructive"
                                                                onClick={() => handleReview(log.id, 'confirmed', 'تم تأكيد التلاعب والتصعيد')}
                                                            >
                                                                تأكيد
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => handleReview(log.id, 'false_positive', 'إنذار كاذب')}
                                                            >
                                                                إنذار كاذب
                                                            </Button>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}

                        {/* Pagination */}
                        {logs.last_page > 1 && (
                            <div className="flex justify-center gap-2 mt-6">
                                {Array.from({ length: logs.last_page }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === logs.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get('/ai/security/tamper-logs', { ...filters, page })}
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
