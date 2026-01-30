import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Eye, CheckCircle, XCircle, AlertTriangle, User, Clock, ArrowRight } from 'lucide-react';
import { useState } from 'react';

interface LivenessCheck {
    id: number;
    employee_id: number;
    employee: { id: number; first_name: string; last_name: string };
    branch: { id: number; name: string } | null;
    check_type: string;
    check_type_name: string;
    passed: boolean;
    confidence_score: number;
    challenge_type: string | null;
    challenge_result: string | null;
    spoofing_detected: boolean;
    spoofing_type: string | null;
    device_info: object | null;
    ip_address: string;
    created_at: string;
}

interface PaginatedLogs {
    data: LivenessCheck[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    logs: PaginatedLogs;
    filters: {
        status: string;
        date_from: string | null;
        date_to: string | null;
    };
}

const checkTypeLabels: Record<string, string> = {
    face_match: 'مطابقة الوجه',
    blink_detection: 'كشف الرمش',
    head_movement: 'حركة الرأس',
    smile_detection: 'كشف الابتسامة',
    random_gesture: 'إيماءة عشوائية',
};

const spoofingTypeLabels: Record<string, string> = {
    photo_attack: 'هجوم بصورة',
    video_attack: 'هجوم بفيديو',
    mask_attack: 'هجوم بقناع',
    deepfake: 'تزييف عميق',
};

export default function LivenessLogs({ logs, filters }: Props) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status);

    const handleFilter = () => {
        router.get('/ai/security/liveness-logs', {
            status: selectedStatus,
        }, { preserveState: true });
    };

    return (
        <AppLayout>
            <Head title="سجل فحوصات الحيوية" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Eye className="w-8 h-8 text-orange-500" />
                            سجل فحوصات الحيوية
                        </h1>
                        <p className="mt-1 text-gray-600">
                            جميع فحوصات التحقق من الهوية
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
                                    <SelectValue placeholder="الحالة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">الكل</SelectItem>
                                    <SelectItem value="passed">ناجحة</SelectItem>
                                    <SelectItem value="failed">فاشلة</SelectItem>
                                    <SelectItem value="spoofing">محاولات تزوير</SelectItem>
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
                        <CardDescription>قائمة بجميع فحوصات الحيوية</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {logs.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <Eye className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد سجلات</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الموظف</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">النوع</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">النتيجة</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الثقة</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">التزوير</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الفرع</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {logs.data.map((log) => (
                                            <tr key={log.id} className="border-b hover:bg-gray-50">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <User className="w-4 h-4 text-gray-400" />
                                                        <span>
                                                            {log.employee.first_name} {log.employee.last_name}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge variant="outline">
                                                        {checkTypeLabels[log.check_type] || log.check_type}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4">
                                                    {log.passed ? (
                                                        <Badge className="bg-green-100 text-green-800">
                                                            <CheckCircle className="w-3 h-3 ml-1" />
                                                            نجاح
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="bg-red-100 text-red-800">
                                                            <XCircle className="w-3 h-3 ml-1" />
                                                            فشل
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span className={`font-medium ${
                                                        log.confidence_score >= 80 ? 'text-green-600' :
                                                        log.confidence_score >= 60 ? 'text-yellow-600' :
                                                        'text-red-600'
                                                    }`}>
                                                        {log.confidence_score}%
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4">
                                                    {log.spoofing_detected ? (
                                                        <Badge className="bg-red-100 text-red-800">
                                                            <AlertTriangle className="w-3 h-3 ml-1" />
                                                            {spoofingTypeLabels[log.spoofing_type || ''] || 'تزوير'}
                                                        </Badge>
                                                    ) : (
                                                        <span className="text-gray-400">-</span>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 text-gray-600">
                                                    {log.branch?.name || '-'}
                                                </td>
                                                <td className="py-3 px-4 text-gray-500 text-sm">
                                                    <div className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3" />
                                                        {new Date(log.created_at).toLocaleString('ar-SA')}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
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
                                        onClick={() => router.get('/ai/security/liveness-logs', { ...filters, page })}
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
