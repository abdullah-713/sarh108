import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FileText, AlertTriangle, User, Clock, Monitor, Eye, Download, CheckCircle, Search } from 'lucide-react';
import { useState } from 'react';

interface AuditLog {
    id: number;
    user: { id: number; name: string } | null;
    employee: { id: number; first_name: string; last_name: string } | null;
    action: string;
    action_name: string;
    severity: string;
    severity_name: string;
    severity_color: string;
    description: string | null;
    ip_address: string | null;
    device_type: string | null;
    browser: string | null;
    is_suspicious: boolean;
    requires_review: boolean;
    reviewed: boolean;
    created_at: string;
}

interface PaginatedLogs {
    data: AuditLog[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Stats {
    total_actions: number;
    by_action: Record<string, number>;
    by_severity: Record<string, number>;
    suspicious_count: number;
    unique_users: number;
}

interface Props {
    logs: PaginatedLogs;
    stats: Stats;
    actions: Record<string, string>;
    severities: Record<string, { name: string; color: string }>;
    filters: {
        action: string;
        severity: string;
        suspicious_only: boolean;
        requires_review: boolean;
    };
}

export default function AuditLogs({ logs, stats, actions, severities, filters }: Props) {
    const [selectedAction, setSelectedAction] = useState(filters.action || 'all');
    const [selectedSeverity, setSelectedSeverity] = useState(filters.severity || 'all');
    const [suspiciousOnly, setSuspiciousOnly] = useState(filters.suspicious_only);

    const handleFilter = () => {
        router.get('/security/audit-logs', {
            action: selectedAction,
            severity: selectedSeverity,
            suspicious_only: suspiciousOnly,
        }, { preserveState: true });
    };

    const handleMarkReviewed = (logId: number) => {
        router.put(`/security/audit-logs/${logId}/reviewed`);
    };

    return (
        <AppLayout>
            <Head title="سجلات التدقيق" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <FileText className="w-8 h-8 text-orange-500" />
                            سجلات التدقيق
                        </h1>
                        <p className="mt-1 text-gray-600">
                            تتبع جميع الإجراءات في النظام
                        </p>
                    </div>

                    <Button
                        variant="outline"
                        onClick={() => router.get('/security/audit-logs/export')}
                    >
                        <Download className="w-4 h-4 ml-2" />
                        تصدير
                    </Button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">إجمالي الإجراءات اليوم</p>
                                <p className="text-3xl font-bold text-gray-900">{stats.total_actions}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">إجراءات مشبوهة</p>
                                <p className="text-3xl font-bold text-red-600">{stats.suspicious_count}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">مستخدمين نشطين</p>
                                <p className="text-3xl font-bold text-blue-600">{stats.unique_users}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">إجراءات حرجة</p>
                                <p className="text-3xl font-bold text-orange-600">
                                    {stats.by_severity?.critical || 0}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-wrap items-center gap-4">
                            <Select value={selectedAction} onValueChange={setSelectedAction}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="الإجراء" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">الكل</SelectItem>
                                    {Object.entries(actions).map(([key, label]) => (
                                        <SelectItem key={key} value={key}>{label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedSeverity} onValueChange={setSelectedSeverity}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="الخطورة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">الكل</SelectItem>
                                    {Object.entries(severities).map(([key, { name }]) => (
                                        <SelectItem key={key} value={key}>{name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={suspiciousOnly}
                                    onChange={(e) => setSuspiciousOnly(e.target.checked)}
                                    className="rounded"
                                />
                                <span className="text-sm">المشبوهة فقط</span>
                            </label>

                            <Button onClick={handleFilter} variant="outline">
                                <Search className="w-4 h-4 ml-2" />
                                بحث
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Logs List */}
                <Card>
                    <CardHeader>
                        <CardTitle>السجلات ({logs.total})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {logs.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <FileText className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد سجلات</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الوقت</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">المستخدم</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الإجراء</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الخطورة</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الوصف</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">الجهاز</th>
                                            <th className="text-right py-3 px-4 font-medium text-gray-600">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {logs.data.map((log) => (
                                            <tr
                                                key={log.id}
                                                className={`border-b hover:bg-gray-50 ${log.is_suspicious ? 'bg-red-50' : ''}`}
                                            >
                                                <td className="py-3 px-4 text-sm text-gray-500">
                                                    <div className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3" />
                                                        {new Date(log.created_at).toLocaleString('ar-SA')}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <User className="w-4 h-4 text-gray-400" />
                                                        <span>{log.user?.name || '-'}</span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge variant="outline">{log.action_name}</Badge>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge className={log.severity_color}>
                                                        {log.severity_name}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-gray-600 max-w-xs truncate">
                                                    {log.description || '-'}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-1 text-sm text-gray-500">
                                                        <Monitor className="w-3 h-3" />
                                                        {log.device_type || '-'}
                                                    </div>
                                                    {log.ip_address && (
                                                        <span className="text-xs text-gray-400">{log.ip_address}</span>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex gap-1">
                                                        {log.is_suspicious && (
                                                            <Badge variant="destructive" className="text-xs">
                                                                <AlertTriangle className="w-3 h-3 ml-1" />
                                                                مشبوه
                                                            </Badge>
                                                        )}
                                                        {log.requires_review && !log.reviewed && (
                                                            <Button
                                                                size="sm"
                                                                variant="ghost"
                                                                onClick={() => handleMarkReviewed(log.id)}
                                                            >
                                                                <CheckCircle className="w-4 h-4" />
                                                            </Button>
                                                        )}
                                                        <Button
                                                            size="sm"
                                                            variant="ghost"
                                                            onClick={() => router.get(`/security/audit-logs/${log.id}`)}
                                                        >
                                                            <Eye className="w-4 h-4" />
                                                        </Button>
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
                                {Array.from({ length: Math.min(logs.last_page, 10) }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === logs.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get('/security/audit-logs', { ...filters, page })}
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
