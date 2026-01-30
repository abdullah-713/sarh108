import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Lock, AlertTriangle, Play, Square, Clock, Building2, Users, Shield, X } from 'lucide-react';
import { useState } from 'react';

interface LockdownEvent {
    id: number;
    branch: { id: number; name: string } | null;
    initiator: { id: number; name: string };
    ender: { id: number; name: string } | null;
    title: string;
    title_ar: string | null;
    description: string | null;
    lockdown_type: string;
    lockdown_type_name: string;
    status: string;
    status_name: string;
    status_color: string;
    start_time: string;
    end_time: string | null;
    actual_end_time: string | null;
    notification_message: string | null;
    created_at: string;
}

interface PaginatedEvents {
    data: LockdownEvent[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    events: PaginatedEvents;
    activeLockdown: LockdownEvent | null;
    lockdownTypes: Record<string, string>;
    statuses: Record<string, { name: string; color: string }>;
}

export default function Lockdown({ events, activeLockdown, lockdownTypes, statuses }: Props) {
    const [isCreating, setIsCreating] = useState(false);
    const [endingLockdown, setEndingLockdown] = useState<number | null>(null);

    const { data, setData, post, processing, reset, errors } = useForm({
        branch_id: '',
        title: '',
        title_ar: '',
        description: '',
        lockdown_type: 'full',
        start_time: new Date().toISOString().slice(0, 16),
        end_time: '',
        allow_emergency_checkin: false,
        allow_emergency_checkout: true,
        notification_message: '',
        notification_message_ar: '',
        notify_employees: true,
        notify_managers: true,
    });

    const endForm = useForm({ end_reason: '' });

    const handleCreate = () => {
        post('/security/lockdown', {
            onSuccess: () => {
                setIsCreating(false);
                reset();
            },
        });
    };

    const handleEnd = (lockdownId: number) => {
        endForm.put(`/security/lockdown/${lockdownId}/end`, {
            onSuccess: () => {
                setEndingLockdown(null);
                endForm.reset();
            },
        });
    };

    return (
        <AppLayout>
            <Head title="وضع الإغلاق" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Lock className="w-8 h-8 text-orange-500" />
                            وضع الإغلاق
                        </h1>
                        <p className="mt-1 text-gray-600">
                            التحكم الطارئ في الحضور والانصراف
                        </p>
                    </div>

                    <Dialog open={isCreating} onOpenChange={setIsCreating}>
                        <DialogTrigger asChild>
                            <Button
                                className="bg-red-600 hover:bg-red-700"
                                disabled={activeLockdown !== null}
                            >
                                <AlertTriangle className="w-4 h-4 ml-2" />
                                تفعيل الإغلاق
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-lg">
                            <DialogHeader>
                                <DialogTitle className="text-red-600 flex items-center gap-2">
                                    <AlertTriangle className="w-5 h-5" />
                                    تفعيل وضع الإغلاق
                                </DialogTitle>
                                <DialogDescription>
                                    سيمنع هذا الموظفين من تسجيل الحضور/الانصراف
                                </DialogDescription>
                            </DialogHeader>
                            <div className="space-y-4 py-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>العنوان</Label>
                                        <Input
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder="مثال: صيانة طارئة"
                                        />
                                    </div>
                                    <div>
                                        <Label>العنوان بالعربية</Label>
                                        <Input
                                            value={data.title_ar}
                                            onChange={(e) => setData('title_ar', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div>
                                    <Label>نوع الإغلاق</Label>
                                    <Select value={data.lockdown_type} onValueChange={(v) => setData('lockdown_type', v)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(lockdownTypes).map(([value, label]) => (
                                                <SelectItem key={value} value={value}>{label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>وقت البدء</Label>
                                        <Input
                                            type="datetime-local"
                                            value={data.start_time}
                                            onChange={(e) => setData('start_time', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>وقت الانتهاء (اختياري)</Label>
                                        <Input
                                            type="datetime-local"
                                            value={data.end_time}
                                            onChange={(e) => setData('end_time', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div>
                                    <Label>رسالة الإشعار</Label>
                                    <Textarea
                                        value={data.notification_message_ar}
                                        onChange={(e) => setData('notification_message_ar', e.target.value)}
                                        placeholder="الرسالة التي ستظهر للموظفين..."
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button variant="outline" onClick={() => setIsCreating(false)}>
                                    إلغاء
                                </Button>
                                <Button
                                    className="bg-red-600 hover:bg-red-700"
                                    onClick={handleCreate}
                                    disabled={processing}
                                >
                                    تفعيل الإغلاق
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* Active Lockdown Alert */}
                {activeLockdown && (
                    <Card className="border-red-500 bg-red-50">
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-4">
                                    <div className="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center animate-pulse">
                                        <Lock className="w-8 h-8 text-red-600" />
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold text-red-600">
                                            إغلاق نشط: {activeLockdown.title_ar || activeLockdown.title}
                                        </h3>
                                        <p className="text-red-600">
                                            {activeLockdown.lockdown_type_name}
                                        </p>
                                        <p className="text-sm text-red-500 mt-1">
                                            بدأ في: {new Date(activeLockdown.start_time).toLocaleString('ar-SA')}
                                        </p>
                                    </div>
                                </div>

                                <Button
                                    variant="outline"
                                    className="border-red-500 text-red-600 hover:bg-red-100"
                                    onClick={() => setEndingLockdown(activeLockdown.id)}
                                >
                                    <Square className="w-4 h-4 ml-2" />
                                    إنهاء الإغلاق
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Events List */}
                <Card>
                    <CardHeader>
                        <CardTitle>سجل الإغلاقات</CardTitle>
                        <CardDescription>جميع أحداث الإغلاق السابقة والحالية</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {events.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <Shield className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد أحداث إغلاق</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {events.data.map((event) => (
                                    <div
                                        key={event.id}
                                        className={`p-4 rounded-lg border ${
                                            event.status === 'active' ? 'border-red-300 bg-red-50' :
                                            event.status === 'scheduled' ? 'border-blue-300 bg-blue-50' :
                                            'border-gray-200 bg-gray-50'
                                        }`}
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-4">
                                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                                    event.status === 'active' ? 'bg-red-100' :
                                                    event.status === 'scheduled' ? 'bg-blue-100' :
                                                    'bg-gray-100'
                                                }`}>
                                                    <Lock className={`w-5 h-5 ${
                                                        event.status === 'active' ? 'text-red-600' :
                                                        event.status === 'scheduled' ? 'text-blue-600' :
                                                        'text-gray-600'
                                                    }`} />
                                                </div>

                                                <div>
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <span className="font-semibold">
                                                            {event.title_ar || event.title}
                                                        </span>
                                                        <Badge className={event.status_color}>
                                                            {event.status_name}
                                                        </Badge>
                                                        <Badge variant="outline">
                                                            {event.lockdown_type_name}
                                                        </Badge>
                                                    </div>

                                                    <div className="flex items-center gap-4 text-sm text-gray-500 mt-2">
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="w-4 h-4" />
                                                            البدء: {new Date(event.start_time).toLocaleString('ar-SA')}
                                                        </span>
                                                        {event.actual_end_time && (
                                                            <span>
                                                                الانتهاء: {new Date(event.actual_end_time).toLocaleString('ar-SA')}
                                                            </span>
                                                        )}
                                                        {event.branch && (
                                                            <span className="flex items-center gap-1">
                                                                <Building2 className="w-4 h-4" />
                                                                {event.branch.name}
                                                            </span>
                                                        )}
                                                    </div>

                                                    <p className="text-sm text-gray-600 mt-2">
                                                        بواسطة: {event.initiator.name}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => router.get(`/security/lockdown/${event.id}`)}
                                                >
                                                    التفاصيل
                                                </Button>

                                                {event.status === 'active' && (
                                                    <Button
                                                        size="sm"
                                                        variant="destructive"
                                                        onClick={() => setEndingLockdown(event.id)}
                                                    >
                                                        <Square className="w-4 h-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* End Lockdown Dialog */}
            <Dialog open={endingLockdown !== null} onOpenChange={() => setEndingLockdown(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>إنهاء الإغلاق</DialogTitle>
                    </DialogHeader>
                    <div className="py-4">
                        <Label>سبب الإنهاء (اختياري)</Label>
                        <Textarea
                            value={endForm.data.end_reason}
                            onChange={(e) => endForm.setData('end_reason', e.target.value)}
                            placeholder="أدخل سبب إنهاء الإغلاق..."
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setEndingLockdown(null)}>
                            إلغاء
                        </Button>
                        <Button
                            className="bg-green-600 hover:bg-green-700"
                            onClick={() => endingLockdown && handleEnd(endingLockdown)}
                            disabled={endForm.processing}
                        >
                            إنهاء الإغلاق
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
