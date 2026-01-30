import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { DoorOpen, ArrowRight, User, Building2, Clock, Calendar, MapPin, QrCode, CheckCircle, XCircle, Printer } from 'lucide-react';

interface ExitPermit {
    id: number;
    employee: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        phone: string | null;
    };
    branch: { id: number; name: string } | null;
    department: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    permit_type: string;
    permit_type_name: string;
    permit_date: string;
    exit_time: string;
    expected_return_time: string;
    actual_return_time: string | null;
    reason: string;
    destination: string | null;
    contact_number: string | null;
    notes: string | null;
    status: string;
    status_name: string;
    status_color: string;
    approval_note: string | null;
    rejection_reason: string | null;
    total_minutes_out: number | null;
    qr_code: string;
    created_at: string;
    approved_at: string | null;
}

interface Props {
    permit: ExitPermit;
}

export default function ShowExitPermit({ permit }: Props) {
    const handlePrint = () => {
        window.print();
    };

    return (
        <AppLayout>
            <Head title={`تصريح خروج #${permit.id}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            onClick={() => router.get('/hr/exit-permits')}
                        >
                            <ArrowRight className="w-5 h-5" />
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                                <DoorOpen className="w-8 h-8 text-orange-500" />
                                تصريح خروج #{permit.id}
                            </h1>
                            <p className="mt-1 text-gray-600">
                                تفاصيل التصريح الكاملة
                            </p>
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <Button variant="outline" onClick={handlePrint}>
                            <Printer className="w-4 h-4 ml-2" />
                            طباعة
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>معلومات التصريح</CardTitle>
                                    <Badge className={permit.status_color} variant="outline">
                                        {permit.status_name}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm text-gray-500">نوع التصريح</p>
                                        <p className="font-medium">{permit.permit_type_name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500">تاريخ التصريح</p>
                                        <p className="font-medium flex items-center gap-1">
                                            <Calendar className="w-4 h-4 text-gray-400" />
                                            {new Date(permit.permit_date).toLocaleDateString('ar-SA')}
                                        </p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <p className="text-sm text-gray-500">وقت الخروج</p>
                                        <p className="font-medium flex items-center gap-1">
                                            <Clock className="w-4 h-4 text-gray-400" />
                                            {permit.exit_time}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500">وقت العودة المتوقع</p>
                                        <p className="font-medium">{permit.expected_return_time}</p>
                                    </div>
                                    {permit.actual_return_time && (
                                        <div>
                                            <p className="text-sm text-gray-500">وقت العودة الفعلي</p>
                                            <p className="font-medium text-green-600">{permit.actual_return_time}</p>
                                        </div>
                                    )}
                                </div>

                                {permit.total_minutes_out && (
                                    <div className="p-3 bg-blue-50 rounded-lg">
                                        <p className="text-blue-800">
                                            إجمالي وقت الخروج: <strong>{permit.total_minutes_out} دقيقة</strong>
                                        </p>
                                    </div>
                                )}

                                <div className="pt-4 border-t">
                                    <p className="text-sm text-gray-500 mb-2">سبب الخروج</p>
                                    <p className="bg-gray-50 p-3 rounded-lg">{permit.reason}</p>
                                </div>

                                {permit.destination && (
                                    <div>
                                        <p className="text-sm text-gray-500 mb-2 flex items-center gap-1">
                                            <MapPin className="w-4 h-4" />
                                            الوجهة
                                        </p>
                                        <p className="bg-gray-50 p-3 rounded-lg">{permit.destination}</p>
                                    </div>
                                )}

                                {permit.notes && (
                                    <div>
                                        <p className="text-sm text-gray-500 mb-2">ملاحظات</p>
                                        <p className="bg-gray-50 p-3 rounded-lg">{permit.notes}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Approval Info */}
                        {(permit.status === 'approved' || permit.status === 'rejected') && (
                            <Card className={permit.status === 'approved' ? 'border-green-200' : 'border-red-200'}>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        {permit.status === 'approved' ? (
                                            <>
                                                <CheckCircle className="w-5 h-5 text-green-600" />
                                                تمت الموافقة
                                            </>
                                        ) : (
                                            <>
                                                <XCircle className="w-5 h-5 text-red-600" />
                                                تم الرفض
                                            </>
                                        )}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {permit.approver && (
                                        <div className="mb-3">
                                            <p className="text-sm text-gray-500">بواسطة</p>
                                            <p className="font-medium">{permit.approver.name}</p>
                                        </div>
                                    )}
                                    {permit.approved_at && (
                                        <div className="mb-3">
                                            <p className="text-sm text-gray-500">تاريخ القرار</p>
                                            <p>{new Date(permit.approved_at).toLocaleString('ar-SA')}</p>
                                        </div>
                                    )}
                                    {permit.approval_note && (
                                        <div>
                                            <p className="text-sm text-gray-500 mb-1">ملاحظة الموافقة</p>
                                            <p className="bg-green-50 p-3 rounded-lg text-green-800">{permit.approval_note}</p>
                                        </div>
                                    )}
                                    {permit.rejection_reason && (
                                        <div>
                                            <p className="text-sm text-gray-500 mb-1">سبب الرفض</p>
                                            <p className="bg-red-50 p-3 rounded-lg text-red-800">{permit.rejection_reason}</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Employee Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="w-5 h-5" />
                                    بيانات الموظف
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <p className="text-sm text-gray-500">الاسم</p>
                                    <p className="font-medium">
                                        {permit.employee.first_name} {permit.employee.last_name}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">البريد الإلكتروني</p>
                                    <p>{permit.employee.email}</p>
                                </div>
                                {permit.employee.phone && (
                                    <div>
                                        <p className="text-sm text-gray-500">الهاتف</p>
                                        <p>{permit.employee.phone}</p>
                                    </div>
                                )}
                                {permit.branch && (
                                    <div>
                                        <p className="text-sm text-gray-500">الفرع</p>
                                        <p className="flex items-center gap-1">
                                            <Building2 className="w-4 h-4 text-gray-400" />
                                            {permit.branch.name}
                                        </p>
                                    </div>
                                )}
                                {permit.department && (
                                    <div>
                                        <p className="text-sm text-gray-500">القسم</p>
                                        <p>{permit.department.name}</p>
                                    </div>
                                )}
                                {permit.contact_number && (
                                    <div>
                                        <p className="text-sm text-gray-500">رقم الاتصال أثناء الخروج</p>
                                        <p className="font-medium text-orange-600">{permit.contact_number}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* QR Code */}
                        {permit.status === 'approved' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <QrCode className="w-5 h-5" />
                                        رمز التحقق
                                    </CardTitle>
                                    <CardDescription>
                                        يستخدم للتحقق من التصريح
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="bg-white p-4 rounded-lg border flex justify-center">
                                        <div className="w-40 h-40 bg-gray-100 rounded flex items-center justify-center">
                                            <QrCode className="w-20 h-20 text-gray-400" />
                                        </div>
                                    </div>
                                    <p className="text-center text-sm text-gray-500 mt-2">
                                        {permit.qr_code}
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
