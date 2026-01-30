import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DoorOpen, Plus, Clock, User, Building2, CheckCircle, XCircle, QrCode, Calendar } from 'lucide-react';
import { useState } from 'react';

interface ExitPermit {
    id: number;
    employee: { id: number; first_name: string; last_name: string };
    branch: { id: number; name: string } | null;
    department: { id: number; name: string } | null;
    permit_type: string;
    permit_type_name: string;
    permit_date: string;
    exit_time: string;
    expected_return_time: string;
    actual_return_time: string | null;
    reason: string;
    destination: string | null;
    status: string;
    status_name: string;
    status_color: string;
    total_minutes_out: number | null;
    qr_code: string;
}

interface PaginatedPermits {
    data: ExitPermit[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Stats {
    pending: number;
    today: number;
    approved_today: number;
}

interface Props {
    permits: PaginatedPermits;
    stats: Stats;
    permitTypes: Record<string, string>;
    statuses: Record<string, { name: string; color: string }>;
    filters: {
        status: string;
        date_from: string | null;
        date_to: string | null;
    };
}

export default function ExitPermits({ permits, stats, permitTypes, statuses, filters }: Props) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status || 'all');
    const [showApproveDialog, setShowApproveDialog] = useState<number | null>(null);
    const [showRejectDialog, setShowRejectDialog] = useState<number | null>(null);

    const approveForm = useForm({ approval_note: '' });
    const rejectForm = useForm({ rejection_reason: '' });

    const handleFilter = () => {
        router.get('/hr/exit-permits', { status: selectedStatus }, { preserveState: true });
    };

    const handleApprove = (permitId: number) => {
        approveForm.post(`/hr/exit-permits/${permitId}/approve`, {
            onSuccess: () => {
                setShowApproveDialog(null);
                approveForm.reset();
            },
        });
    };

    const handleReject = (permitId: number) => {
        rejectForm.post(`/hr/exit-permits/${permitId}/reject`, {
            onSuccess: () => {
                setShowRejectDialog(null);
                rejectForm.reset();
            },
        });
    };

    return (
        <AppLayout>
            <Head title="تصاريح الخروج" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <DoorOpen className="w-8 h-8 text-orange-500" />
                            تصاريح الخروج
                        </h1>
                        <p className="mt-1 text-gray-600">
                            إدارة تصاريح الخروج خلال ساعات العمل
                        </p>
                    </div>

                    <Button
                        className="bg-orange-500 hover:bg-orange-600"
                        onClick={() => router.get('/hr/exit-permits/create')}
                    >
                        <Plus className="w-4 h-4 ml-2" />
                        تصريح جديد
                    </Button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">بانتظار الموافقة</p>
                                    <p className="text-3xl font-bold text-yellow-600">{stats.pending}</p>
                                </div>
                                <Clock className="w-10 h-10 text-yellow-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">تصاريح اليوم</p>
                                    <p className="text-3xl font-bold text-blue-600">{stats.today}</p>
                                </div>
                                <Calendar className="w-10 h-10 text-blue-500 opacity-50" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-500">تمت الموافقة اليوم</p>
                                    <p className="text-3xl font-bold text-green-600">{stats.approved_today}</p>
                                </div>
                                <CheckCircle className="w-10 h-10 text-green-500 opacity-50" />
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
                                    <SelectItem value="all">الكل</SelectItem>
                                    <SelectItem value="pending">بانتظار الموافقة</SelectItem>
                                    <SelectItem value="approved">موافق عليه</SelectItem>
                                    <SelectItem value="rejected">مرفوض</SelectItem>
                                    <SelectItem value="used">مستخدم</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button onClick={handleFilter} variant="outline">
                                تطبيق الفلتر
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Permits List */}
                <Card>
                    <CardHeader>
                        <CardTitle>التصاريح ({permits.total})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {permits.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <DoorOpen className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد تصاريح</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {permits.data.map((permit) => (
                                    <div
                                        key={permit.id}
                                        className="p-4 rounded-lg border bg-gray-50 hover:bg-gray-100"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-4">
                                                <div className="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                                                    <DoorOpen className="w-6 h-6 text-orange-600" />
                                                </div>

                                                <div>
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <span className="font-semibold">
                                                            {permit.employee.first_name} {permit.employee.last_name}
                                                        </span>
                                                        <Badge className={permit.status_color}>
                                                            {permit.status_name}
                                                        </Badge>
                                                        <Badge variant="outline">
                                                            {permit.permit_type_name}
                                                        </Badge>
                                                    </div>

                                                    <p className="text-sm text-gray-600 mb-2">
                                                        {permit.reason}
                                                    </p>

                                                    <div className="flex items-center gap-4 text-sm text-gray-500">
                                                        <span className="flex items-center gap-1">
                                                            <Calendar className="w-4 h-4" />
                                                            {new Date(permit.permit_date).toLocaleDateString('ar-SA')}
                                                        </span>
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="w-4 h-4" />
                                                            {permit.exit_time} - {permit.expected_return_time}
                                                        </span>
                                                        {permit.branch && (
                                                            <span className="flex items-center gap-1">
                                                                <Building2 className="w-4 h-4" />
                                                                {permit.branch.name}
                                                            </span>
                                                        )}
                                                    </div>

                                                    {permit.actual_return_time && (
                                                        <p className="text-sm text-green-600 mt-2">
                                                            وقت العودة: {permit.actual_return_time}
                                                            {permit.total_minutes_out && ` (${permit.total_minutes_out} دقيقة)`}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="flex flex-col gap-2">
                                                {permit.status === 'pending' && (
                                                    <div className="flex gap-2">
                                                        <Button
                                                            size="sm"
                                                            className="bg-green-600 hover:bg-green-700"
                                                            onClick={() => setShowApproveDialog(permit.id)}
                                                        >
                                                            <CheckCircle className="w-4 h-4" />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => setShowRejectDialog(permit.id)}
                                                        >
                                                            <XCircle className="w-4 h-4" />
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
                        {permits.last_page > 1 && (
                            <div className="flex justify-center gap-2 mt-6">
                                {Array.from({ length: permits.last_page }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === permits.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get('/hr/exit-permits', { ...filters, page })}
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Approve Dialog */}
            <Dialog open={showApproveDialog !== null} onOpenChange={() => setShowApproveDialog(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>الموافقة على التصريح</DialogTitle>
                    </DialogHeader>
                    <div className="py-4">
                        <Label>ملاحظات (اختياري)</Label>
                        <Textarea
                            value={approveForm.data.approval_note}
                            onChange={(e) => approveForm.setData('approval_note', e.target.value)}
                            placeholder="أدخل أي ملاحظات..."
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowApproveDialog(null)}>
                            إلغاء
                        </Button>
                        <Button
                            className="bg-green-600 hover:bg-green-700"
                            onClick={() => showApproveDialog && handleApprove(showApproveDialog)}
                            disabled={approveForm.processing}
                        >
                            موافقة
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Reject Dialog */}
            <Dialog open={showRejectDialog !== null} onOpenChange={() => setShowRejectDialog(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>رفض التصريح</DialogTitle>
                    </DialogHeader>
                    <div className="py-4">
                        <Label>سبب الرفض</Label>
                        <Textarea
                            value={rejectForm.data.rejection_reason}
                            onChange={(e) => rejectForm.setData('rejection_reason', e.target.value)}
                            placeholder="أدخل سبب الرفض..."
                            required
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowRejectDialog(null)}>
                            إلغاء
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={() => showRejectDialog && handleReject(showRejectDialog)}
                            disabled={rejectForm.processing || !rejectForm.data.rejection_reason}
                        >
                            رفض
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
