import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DoorOpen, ArrowRight, Clock, Calendar, MapPin } from 'lucide-react';

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    department: { id: number; name: string } | null;
}

interface Props {
    employees: Employee[];
    permitTypes: Record<string, string>;
}

export default function CreateExitPermit({ employees, permitTypes }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        employee_id: '',
        permit_type: 'personal',
        permit_date: new Date().toISOString().slice(0, 10),
        exit_time: '',
        expected_return_time: '',
        reason: '',
        destination: '',
        contact_number: '',
        notes: '',
    });

    const handleSubmit = () => {
        post('/hr/exit-permits');
    };

    return (
        <AppLayout>
            <Head title="طلب تصريح خروج" />

            <div className="space-y-6">
                {/* Header */}
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
                            طلب تصريح خروج جديد
                        </h1>
                        <p className="mt-1 text-gray-600">
                            إنشاء تصريح خروج خلال ساعات العمل
                        </p>
                    </div>
                </div>

                <div className="max-w-2xl">
                    <Card>
                        <CardHeader>
                            <CardTitle>بيانات التصريح</CardTitle>
                            <CardDescription>
                                أدخل تفاصيل طلب الخروج
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Employee Selection */}
                            <div>
                                <Label>الموظف</Label>
                                <Select value={data.employee_id} onValueChange={(v) => setData('employee_id', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="اختر الموظف" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {employees.map((employee) => (
                                            <SelectItem key={employee.id} value={employee.id.toString()}>
                                                {employee.first_name} {employee.last_name}
                                                {employee.department && ` - ${employee.department.name}`}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.employee_id && (
                                    <p className="text-red-500 text-sm mt-1">{errors.employee_id}</p>
                                )}
                            </div>

                            {/* Permit Type */}
                            <div>
                                <Label>نوع التصريح</Label>
                                <Select value={data.permit_type} onValueChange={(v) => setData('permit_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(permitTypes).map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Date and Time */}
                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <Label className="flex items-center gap-1">
                                        <Calendar className="w-4 h-4" />
                                        التاريخ
                                    </Label>
                                    <Input
                                        type="date"
                                        value={data.permit_date}
                                        onChange={(e) => setData('permit_date', e.target.value)}
                                    />
                                    {errors.permit_date && (
                                        <p className="text-red-500 text-sm mt-1">{errors.permit_date}</p>
                                    )}
                                </div>

                                <div>
                                    <Label className="flex items-center gap-1">
                                        <Clock className="w-4 h-4" />
                                        وقت الخروج
                                    </Label>
                                    <Input
                                        type="time"
                                        value={data.exit_time}
                                        onChange={(e) => setData('exit_time', e.target.value)}
                                    />
                                    {errors.exit_time && (
                                        <p className="text-red-500 text-sm mt-1">{errors.exit_time}</p>
                                    )}
                                </div>

                                <div>
                                    <Label className="flex items-center gap-1">
                                        <Clock className="w-4 h-4" />
                                        وقت العودة المتوقع
                                    </Label>
                                    <Input
                                        type="time"
                                        value={data.expected_return_time}
                                        onChange={(e) => setData('expected_return_time', e.target.value)}
                                    />
                                    {errors.expected_return_time && (
                                        <p className="text-red-500 text-sm mt-1">{errors.expected_return_time}</p>
                                    )}
                                </div>
                            </div>

                            {/* Reason */}
                            <div>
                                <Label>سبب الخروج</Label>
                                <Textarea
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    placeholder="اذكر سبب الخروج..."
                                    rows={3}
                                />
                                {errors.reason && (
                                    <p className="text-red-500 text-sm mt-1">{errors.reason}</p>
                                )}
                            </div>

                            {/* Destination */}
                            <div>
                                <Label className="flex items-center gap-1">
                                    <MapPin className="w-4 h-4" />
                                    الوجهة
                                </Label>
                                <Input
                                    value={data.destination}
                                    onChange={(e) => setData('destination', e.target.value)}
                                    placeholder="أين ستذهب؟ (اختياري)"
                                />
                            </div>

                            {/* Contact Number */}
                            <div>
                                <Label>رقم الاتصال أثناء الخروج</Label>
                                <Input
                                    value={data.contact_number}
                                    onChange={(e) => setData('contact_number', e.target.value)}
                                    placeholder="رقم الجوال (اختياري)"
                                />
                            </div>

                            {/* Notes */}
                            <div>
                                <Label>ملاحظات إضافية</Label>
                                <Textarea
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="أي ملاحظات أخرى..."
                                    rows={2}
                                />
                            </div>

                            {/* Submit */}
                            <div className="flex justify-end gap-3 pt-4 border-t">
                                <Button
                                    variant="outline"
                                    onClick={() => router.get('/hr/exit-permits')}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    onClick={handleSubmit}
                                    disabled={processing}
                                    className="bg-orange-500 hover:bg-orange-600"
                                >
                                    <DoorOpen className="w-4 h-4 ml-2" />
                                    تقديم الطلب
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
