import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { DoorOpen, Save, Clock, AlertTriangle } from 'lucide-react';

interface Settings {
    id: number;
    max_permits_per_day: number;
    max_permits_per_week: number;
    max_permits_per_month: number;
    max_duration_minutes: number;
    min_duration_minutes: number;
    require_approval: boolean;
    auto_approve_managers: boolean;
    allow_same_day_request: boolean;
    advance_request_hours: number;
    notify_hr_on_request: boolean;
    notify_hr_on_approval: boolean;
    notify_manager_on_request: boolean;
    deduct_from_salary: boolean;
    deduction_rate: number;
}

interface Props {
    settings: Settings;
}

export default function ExitPermitSettings({ settings }: Props) {
    const { data, setData, put, processing } = useForm({
        max_permits_per_day: settings.max_permits_per_day,
        max_permits_per_week: settings.max_permits_per_week,
        max_permits_per_month: settings.max_permits_per_month,
        max_duration_minutes: settings.max_duration_minutes,
        min_duration_minutes: settings.min_duration_minutes,
        require_approval: settings.require_approval,
        auto_approve_managers: settings.auto_approve_managers,
        allow_same_day_request: settings.allow_same_day_request,
        advance_request_hours: settings.advance_request_hours,
        notify_hr_on_request: settings.notify_hr_on_request,
        notify_hr_on_approval: settings.notify_hr_on_approval,
        notify_manager_on_request: settings.notify_manager_on_request,
        deduct_from_salary: settings.deduct_from_salary,
        deduction_rate: settings.deduction_rate,
    });

    const handleSubmit = () => {
        put('/settings/exit-permits');
    };

    return (
        <AppLayout>
            <Head title="إعدادات تصاريح الخروج" />

            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <DoorOpen className="w-8 h-8 text-orange-500" />
                        إعدادات تصاريح الخروج
                    </h1>
                    <p className="mt-1 text-gray-600">
                        التحكم في قواعد وحدود تصاريح الخروج
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Limits */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertTriangle className="w-5 h-5 text-orange-500" />
                                الحدود
                            </CardTitle>
                            <CardDescription>تحديد عدد التصاريح المسموحة</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <Label>الحد اليومي</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={data.max_permits_per_day}
                                        onChange={(e) => setData('max_permits_per_day', parseInt(e.target.value))}
                                    />
                                </div>
                                <div>
                                    <Label>الحد الأسبوعي</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={data.max_permits_per_week}
                                        onChange={(e) => setData('max_permits_per_week', parseInt(e.target.value))}
                                    />
                                </div>
                                <div>
                                    <Label>الحد الشهري</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={data.max_permits_per_month}
                                        onChange={(e) => setData('max_permits_per_month', parseInt(e.target.value))}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Duration */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="w-5 h-5 text-blue-500" />
                                المدة
                            </CardTitle>
                            <CardDescription>تحديد مدة التصاريح</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label>الحد الأدنى (دقيقة)</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={data.min_duration_minutes}
                                        onChange={(e) => setData('min_duration_minutes', parseInt(e.target.value))}
                                    />
                                </div>
                                <div>
                                    <Label>الحد الأقصى (دقيقة)</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={data.max_duration_minutes}
                                        onChange={(e) => setData('max_duration_minutes', parseInt(e.target.value))}
                                    />
                                </div>
                            </div>
                            <div>
                                <Label>ساعات الطلب المسبق</Label>
                                <Input
                                    type="number"
                                    min="0"
                                    value={data.advance_request_hours}
                                    onChange={(e) => setData('advance_request_hours', parseInt(e.target.value))}
                                />
                                <p className="text-sm text-gray-500 mt-1">
                                    كم ساعة مقدماً يجب تقديم الطلب
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Approval Settings */}
                    <Card>
                        <CardHeader>
                            <CardTitle>إعدادات الموافقة</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>تتطلب موافقة</Label>
                                    <p className="text-sm text-gray-500">هل يجب الموافقة على التصاريح</p>
                                </div>
                                <Switch
                                    checked={data.require_approval}
                                    onCheckedChange={(checked) => setData('require_approval', checked)}
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>موافقة تلقائية للمدراء</Label>
                                    <p className="text-sm text-gray-500">الموافقة التلقائية على طلبات المدراء</p>
                                </div>
                                <Switch
                                    checked={data.auto_approve_managers}
                                    onCheckedChange={(checked) => setData('auto_approve_managers', checked)}
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>السماح بطلب نفس اليوم</Label>
                                    <p className="text-sm text-gray-500">السماح بطلب تصريح لنفس يوم العمل</p>
                                </div>
                                <Switch
                                    checked={data.allow_same_day_request}
                                    onCheckedChange={(checked) => setData('allow_same_day_request', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notifications */}
                    <Card>
                        <CardHeader>
                            <CardTitle>الإشعارات</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>إشعار HR عند الطلب</Label>
                                </div>
                                <Switch
                                    checked={data.notify_hr_on_request}
                                    onCheckedChange={(checked) => setData('notify_hr_on_request', checked)}
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>إشعار HR عند الموافقة</Label>
                                </div>
                                <Switch
                                    checked={data.notify_hr_on_approval}
                                    onCheckedChange={(checked) => setData('notify_hr_on_approval', checked)}
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>إشعار المدير عند الطلب</Label>
                                </div>
                                <Switch
                                    checked={data.notify_manager_on_request}
                                    onCheckedChange={(checked) => setData('notify_manager_on_request', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Deductions */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>الخصومات</CardTitle>
                            <CardDescription>إعدادات الخصم من الراتب</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>الخصم من الراتب</Label>
                                    <p className="text-sm text-gray-500">خصم وقت الخروج من الراتب</p>
                                </div>
                                <Switch
                                    checked={data.deduct_from_salary}
                                    onCheckedChange={(checked) => setData('deduct_from_salary', checked)}
                                />
                            </div>

                            {data.deduct_from_salary && (
                                <div>
                                    <Label>نسبة الخصم (%)</Label>
                                    <Input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        value={data.deduction_rate}
                                        onChange={(e) => setData('deduction_rate', parseFloat(e.target.value))}
                                        className="max-w-xs"
                                    />
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Save Button */}
                <div className="flex justify-end">
                    <Button
                        onClick={handleSubmit}
                        disabled={processing}
                        className="bg-orange-500 hover:bg-orange-600"
                    >
                        <Save className="w-4 h-4 ml-2" />
                        حفظ الإعدادات
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
