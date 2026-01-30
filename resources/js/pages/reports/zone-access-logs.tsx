import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MapPin, User, Building2, Clock, ArrowRightLeft, TrendingUp } from 'lucide-react';
import { useState } from 'react';

interface ZoneAccessLog {
    id: number;
    employee: { id: number; first_name: string; last_name: string };
    zone: { id: number; name: string; color: string };
    branch: { id: number; name: string } | null;
    entry_time: string;
    exit_time: string | null;
    duration_minutes: number | null;
    access_method: string;
    access_method_name: string;
    is_authorized: boolean;
    created_at: string;
}

interface PaginatedLogs {
    data: ZoneAccessLog[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Stats {
    total_entries: number;
    unique_employees: number;
    unauthorized_attempts: number;
    average_duration: number;
}

interface Zone {
    id: number;
    name: string;
    color: string;
}

interface Props {
    logs: PaginatedLogs;
    stats: Stats;
    zones: Zone[];
    filters: {
        zone_id: string;
        date_from: string | null;
        date_to: string | null;
    };
}

export default function ZoneAccessLogs({ logs, stats, zones, filters }: Props) {
    const [selectedZone, setSelectedZone] = useState(filters.zone_id || 'all');

    const handleFilter = () => {
        router.get('/reports/zone-access-logs', { zone_id: selectedZone }, { preserveState: true });
    };

    const formatDuration = (minutes: number | null) => {
        if (!minutes) return '-';
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        if (hours > 0) {
            return `${hours}س ${mins}د`;
        }
        return `${mins} دقيقة`;
    };

    return (
        <AppLayout>
            <Head title="سجلات دخول المناطق" />

            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <ArrowRightLeft className="w-8 h-8 text-orange-500" />
                        سجلات دخول المناطق
                    </h1>
                    <p className="mt-1 text-gray-600">
                        تتبع حركة الموظفين بين مناطق العمل
                    </p>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">إجمالي الدخول</p>
                                <p className="text-3xl font-bold text-gray-900">{stats.total_entries}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">موظفين فريدين</p>
                                <p className="text-3xl font-bold text-blue-600">{stats.unique_employees}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">محاولات غير مصرح بها</p>
                                <p className="text-3xl font-bold text-red-600">{stats.unauthorized_attempts}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <p className="text-sm text-gray-500">متوسط المدة</p>
                                <p className="text-3xl font-bold text-green-600">{formatDuration(stats.average_duration)}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-wrap items-center gap-4">
                            <Select value={selectedZone} onValueChange={setSelectedZone}>
                                <SelectTrigger className="w-[200px]">
                                    <SelectValue placeholder="المنطقة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">جميع المناطق</SelectItem>
                                    {zones.map((zone) => (
                                        <SelectItem key={zone.id} value={zone.id.toString()}>
                                            <span className="flex items-center gap-2">
                                                <span
                                                    className="w-3 h-3 rounded-full"
                                                    style={{ backgroundColor: zone.color }}
                                                />
                                                {zone.name}
                                            </span>
                                        </SelectItem>
                                    ))}
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
                    </CardHeader>
                    <CardContent>
                        {logs.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <MapPin className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد سجلات</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {logs.data.map((log) => (
                                    <div
                                        key={log.id}
                                        className={`p-4 rounded-lg border ${
                                            log.is_authorized ? 'bg-gray-50' : 'bg-red-50 border-red-200'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-4">
                                                <div
                                                    className="w-10 h-10 rounded-lg flex items-center justify-center"
                                                    style={{ backgroundColor: log.zone.color + '20' }}
                                                >
                                                    <MapPin
                                                        className="w-5 h-5"
                                                        style={{ color: log.zone.color }}
                                                    />
                                                </div>

                                                <div>
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <span className="font-medium">
                                                            {log.employee.first_name} {log.employee.last_name}
                                                        </span>
                                                        <Badge
                                                            variant="outline"
                                                            style={{
                                                                borderColor: log.zone.color,
                                                                color: log.zone.color,
                                                            }}
                                                        >
                                                            {log.zone.name}
                                                        </Badge>
                                                        {!log.is_authorized && (
                                                            <Badge variant="destructive">غير مصرح</Badge>
                                                        )}
                                                    </div>

                                                    <div className="flex items-center gap-4 text-sm text-gray-500">
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="w-4 h-4" />
                                                            دخول: {new Date(log.entry_time).toLocaleTimeString('ar-SA')}
                                                        </span>
                                                        {log.exit_time && (
                                                            <span>
                                                                خروج: {new Date(log.exit_time).toLocaleTimeString('ar-SA')}
                                                            </span>
                                                        )}
                                                        {log.duration_minutes && (
                                                            <Badge variant="secondary">
                                                                {formatDuration(log.duration_minutes)}
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="text-left text-sm text-gray-500">
                                                <p>{new Date(log.created_at).toLocaleDateString('ar-SA')}</p>
                                                <Badge variant="outline" className="text-xs">
                                                    {log.access_method_name}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                ))}
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
                                        onClick={() => router.get('/reports/zone-access-logs', { ...filters, page })}
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
