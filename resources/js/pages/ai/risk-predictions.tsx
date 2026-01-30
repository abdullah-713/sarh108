import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertTriangle, Brain, TrendingUp, Calendar, User, Building2, Clock, CheckCircle, XCircle, Play, RefreshCw, Eye, FileText } from 'lucide-react';
import { useState } from 'react';

interface Prediction {
    id: number;
    employee_id: number;
    employee: { id: number; first_name: string; last_name: string };
    branch: { id: number; name: string } | null;
    risk_type: string;
    risk_type_name: string;
    severity: string;
    severity_name: string;
    severity_color: string;
    confidence_score: number;
    risk_score: number;
    predicted_date: string;
    prediction_reason: string;
    recommended_action: string;
    status: string;
    created_at: string;
}

interface PaginatedPredictions {
    data: Prediction[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Stats {
    total: number;
    high_risk: number;
    by_type: Record<string, number>;
}

interface Props {
    predictions: PaginatedPredictions;
    stats: Stats;
    filters: {
        status: string;
        risk_type: string | null;
        severity: string | null;
    };
}

const riskTypeLabels: Record<string, string> = {
    absence: 'غياب متوقع',
    late: 'تأخير متوقع',
    resignation: 'استقالة محتملة',
    burnout: 'إرهاق',
    pattern_break: 'كسر النمط',
};

const severityLabels: Record<string, { name: string; color: string }> = {
    low: { name: 'منخفضة', color: 'bg-green-100 text-green-800' },
    medium: { name: 'متوسطة', color: 'bg-yellow-100 text-yellow-800' },
    high: { name: 'عالية', color: 'bg-orange-100 text-orange-800' },
    critical: { name: 'حرجة', color: 'bg-red-100 text-red-800' },
};

const statusLabels: Record<string, { name: string; color: string }> = {
    pending: { name: 'بانتظار المراجعة', color: 'bg-blue-100 text-blue-800' },
    reviewed: { name: 'تمت المراجعة', color: 'bg-purple-100 text-purple-800' },
    acted: { name: 'تم اتخاذ إجراء', color: 'bg-green-100 text-green-800' },
    resolved: { name: 'تم الحل', color: 'bg-gray-100 text-gray-800' },
    dismissed: { name: 'تم التجاهل', color: 'bg-gray-100 text-gray-500' },
    occurred: { name: 'حدث فعلاً', color: 'bg-red-100 text-red-800' },
    false_alarm: { name: 'إنذار كاذب', color: 'bg-yellow-100 text-yellow-600' },
};

export default function RiskPredictions({ predictions, stats, filters }: Props) {
    const [isRunning, setIsRunning] = useState(false);
    const [selectedStatus, setSelectedStatus] = useState(filters.status);
    const [selectedType, setSelectedType] = useState(filters.risk_type || '');
    const [selectedSeverity, setSelectedSeverity] = useState(filters.severity || '');

    const handleRunAnalysis = async () => {
        setIsRunning(true);
        try {
            await fetch('/ai/risk-predictions/run', { method: 'POST' });
            router.reload();
        } catch (error) {
            console.error(error);
        } finally {
            setIsRunning(false);
        }
    };

    const handleFilter = () => {
        router.get('/ai/risk-predictions', {
            status: selectedStatus,
            risk_type: selectedType || null,
            severity: selectedSeverity || null,
        }, { preserveState: true });
    };

    const handleUpdateStatus = (predictionId: number, status: string) => {
        router.put(`/ai/risk-predictions/${predictionId}/status`, { status });
    };

    return (
        <AppLayout>
            <Head title="توقعات المخاطر" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Brain className="w-8 h-8 text-orange-500" />
                            توقعات المخاطر
                        </h1>
                        <p className="mt-1 text-gray-600">
                            التنبؤ الذكي بمخاطر الحضور والغياب
                        </p>
                    </div>

                    <Button
                        onClick={handleRunAnalysis}
                        disabled={isRunning}
                        className="bg-orange-500 hover:bg-orange-600"
                    >
                        {isRunning ? (
                            <RefreshCw className="w-4 h-4 ml-2 animate-spin" />
                        ) : (
                            <Play className="w-4 h-4 ml-2" />
                        )}
                        تشغيل التحليل
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">إجمالي التوقعات</p>
                                    <p className="text-3xl font-bold text-gray-900">{stats.total}</p>
                                </div>
                                <Brain className="w-10 h-10 text-orange-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">مخاطر عالية</p>
                                    <p className="text-3xl font-bold text-red-600">{stats.high_risk}</p>
                                </div>
                                <AlertTriangle className="w-10 h-10 text-red-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">غياب متوقع</p>
                                    <p className="text-3xl font-bold text-orange-600">{stats.by_type?.absence || 0}</p>
                                </div>
                                <Calendar className="w-10 h-10 text-orange-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">تأخير متوقع</p>
                                    <p className="text-3xl font-bold text-yellow-600">{stats.by_type?.late || 0}</p>
                                </div>
                                <Clock className="w-10 h-10 text-yellow-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>
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
                                    <SelectItem value="pending">بانتظار المراجعة</SelectItem>
                                    <SelectItem value="reviewed">تمت المراجعة</SelectItem>
                                    <SelectItem value="acted">تم اتخاذ إجراء</SelectItem>
                                    <SelectItem value="resolved">تم الحل</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedType} onValueChange={setSelectedType}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder="نوع المخاطرة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">الكل</SelectItem>
                                    <SelectItem value="absence">غياب</SelectItem>
                                    <SelectItem value="late">تأخير</SelectItem>
                                    <SelectItem value="burnout">إرهاق</SelectItem>
                                    <SelectItem value="resignation">استقالة</SelectItem>
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

                {/* Predictions List */}
                <Card>
                    <CardHeader>
                        <CardTitle>التوقعات ({predictions.total})</CardTitle>
                        <CardDescription>قائمة بجميع توقعات المخاطر</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {predictions.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <Brain className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد توقعات</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {predictions.data.map((prediction) => (
                                    <div
                                        key={prediction.id}
                                        className={`p-4 rounded-lg border-r-4 ${
                                            prediction.severity === 'critical' ? 'border-r-red-500 bg-red-50' :
                                            prediction.severity === 'high' ? 'border-r-orange-500 bg-orange-50' :
                                            prediction.severity === 'medium' ? 'border-r-yellow-500 bg-yellow-50' :
                                            'border-r-green-500 bg-green-50'
                                        }`}
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-4">
                                                <div className={`w-12 h-12 rounded-full flex items-center justify-center ${
                                                    prediction.severity === 'critical' ? 'bg-red-100' :
                                                    prediction.severity === 'high' ? 'bg-orange-100' :
                                                    prediction.severity === 'medium' ? 'bg-yellow-100' :
                                                    'bg-green-100'
                                                }`}>
                                                    <AlertTriangle className={`w-6 h-6 ${
                                                        prediction.severity === 'critical' ? 'text-red-600' :
                                                        prediction.severity === 'high' ? 'text-orange-600' :
                                                        prediction.severity === 'medium' ? 'text-yellow-600' :
                                                        'text-green-600'
                                                    }`} />
                                                </div>

                                                <div>
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <span className="font-semibold">
                                                            {prediction.employee.first_name} {prediction.employee.last_name}
                                                        </span>
                                                        <Badge className={severityLabels[prediction.severity]?.color}>
                                                            {severityLabels[prediction.severity]?.name}
                                                        </Badge>
                                                        <Badge variant="outline">
                                                            {riskTypeLabels[prediction.risk_type]}
                                                        </Badge>
                                                    </div>

                                                    <p className="text-sm text-gray-600 mb-2">
                                                        {prediction.prediction_reason}
                                                    </p>

                                                    <div className="flex items-center gap-4 text-sm text-gray-500">
                                                        <span className="flex items-center gap-1">
                                                            <Calendar className="w-4 h-4" />
                                                            التاريخ المتوقع: {new Date(prediction.predicted_date).toLocaleDateString('ar-SA')}
                                                        </span>
                                                        <span className="flex items-center gap-1">
                                                            <TrendingUp className="w-4 h-4" />
                                                            درجة المخاطرة: {prediction.risk_score?.toFixed(0)}%
                                                        </span>
                                                        {prediction.branch && (
                                                            <span className="flex items-center gap-1">
                                                                <Building2 className="w-4 h-4" />
                                                                {prediction.branch.name}
                                                            </span>
                                                        )}
                                                    </div>

                                                    <div className="mt-2 p-2 bg-white/50 rounded text-sm">
                                                        <strong>التوصية:</strong> {prediction.recommended_action}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex flex-col gap-2">
                                                <Badge className={statusLabels[prediction.status]?.color}>
                                                    {statusLabels[prediction.status]?.name}
                                                </Badge>

                                                {prediction.status === 'pending' && (
                                                    <div className="flex gap-1">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => handleUpdateStatus(prediction.id, 'reviewed')}
                                                        >
                                                            <Eye className="w-3 h-3" />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="text-green-600"
                                                            onClick={() => handleUpdateStatus(prediction.id, 'acted')}
                                                        >
                                                            <CheckCircle className="w-3 h-3" />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="text-gray-500"
                                                            onClick={() => handleUpdateStatus(prediction.id, 'dismissed')}
                                                        >
                                                            <XCircle className="w-3 h-3" />
                                                        </Button>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Pagination */}
                        {predictions.last_page > 1 && (
                            <div className="flex justify-center gap-2 mt-6">
                                {Array.from({ length: predictions.last_page }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === predictions.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get('/ai/risk-predictions', { ...filters, page })}
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
