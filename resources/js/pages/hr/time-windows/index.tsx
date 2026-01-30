import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Switch } from '@/components/ui/switch';
import { 
    Clock, 
    Plus, 
    Pencil, 
    Trash2, 
    CheckCircle,
    XCircle,
    Loader2,
    Timer
} from 'lucide-react';

interface TimeWindow {
    id: number;
    name: string;
    checkin_start: string;
    checkin_end: string;
    checkout_start: string;
    checkout_end: string;
    late_grace_minutes: number;
    early_leave_grace_minutes: number;
    is_active: boolean;
    is_default: boolean;
    days_of_week: number[];
}

interface PageProps {
    timeWindows: TimeWindow[];
}

const daysOfWeek = [
    { value: 0, label: 'الأحد' },
    { value: 1, label: 'الاثنين' },
    { value: 2, label: 'الثلاثاء' },
    { value: 3, label: 'الأربعاء' },
    { value: 4, label: 'الخميس' },
    { value: 5, label: 'الجمعة' },
    { value: 6, label: 'السبت' },
];

export default function TimeWindowsIndex() {
    const { timeWindows } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [editingWindow, setEditingWindow] = useState<TimeWindow | null>(null);
    const [deletingWindow, setDeletingWindow] = useState<TimeWindow | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    const [formData, setFormData] = useState({
        name: '',
        checkin_start: '08:00',
        checkin_end: '10:00',
        checkout_start: '16:00',
        checkout_end: '18:00',
        late_grace_minutes: 15,
        early_leave_grace_minutes: 15,
        is_active: true,
        is_default: false,
        days_of_week: [0, 1, 2, 3, 4] as number[],
    });

    // فتح نافذة الإضافة
    const handleAdd = () => {
        setEditingWindow(null);
        setFormData({
            name: '',
            checkin_start: '08:00',
            checkin_end: '10:00',
            checkout_start: '16:00',
            checkout_end: '18:00',
            late_grace_minutes: 15,
            early_leave_grace_minutes: 15,
            is_active: true,
            is_default: false,
            days_of_week: [0, 1, 2, 3, 4],
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة التعديل
    const handleEdit = (window: TimeWindow) => {
        setEditingWindow(window);
        setFormData({
            name: window.name,
            checkin_start: window.checkin_start,
            checkin_end: window.checkin_end,
            checkout_start: window.checkout_start,
            checkout_end: window.checkout_end,
            late_grace_minutes: window.late_grace_minutes,
            early_leave_grace_minutes: window.early_leave_grace_minutes,
            is_active: window.is_active,
            is_default: window.is_default,
            days_of_week: window.days_of_week,
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة الحذف
    const handleDeleteClick = (window: TimeWindow) => {
        setDeletingWindow(window);
        setIsDeleteDialogOpen(true);
    };

    // تبديل يوم
    const toggleDay = (day: number) => {
        const newDays = formData.days_of_week.includes(day)
            ? formData.days_of_week.filter(d => d !== day)
            : [...formData.days_of_week, day].sort();
        setFormData({ ...formData, days_of_week: newDays });
    };

    // حفظ البيانات
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const url = editingWindow 
            ? `/hr/time-windows/${editingWindow.id}` 
            : '/hr/time-windows';
        
        const method = editingWindow ? 'put' : 'post';

        router[method](url, formData, {
            onSuccess: () => {
                setIsDialogOpen(false);
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            },
        });
    };

    // حذف النافذة الزمنية
    const handleDelete = () => {
        if (!deletingWindow) return;
        setIsSubmitting(true);

        router.delete(`/hr/time-windows/${deletingWindow.id}`, {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setDeletingWindow(null);
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            },
        });
    };

    // الحصول على أسماء الأيام
    const getDayNames = (days: number[]) => {
        return days.map(d => daysOfWeek.find(day => day.value === d)?.label).join('، ');
    };

    return (
        <AppLayout>
            <Head title="النوافذ الزمنية" />

            <div className="max-w-6xl mx-auto p-4 space-y-6">
                <Card>
                    <CardHeader>
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-xl flex items-center gap-2">
                                    <Clock className="w-5 h-5 text-primary" />
                                    إدارة النوافذ الزمنية
                                </CardTitle>
                                <CardDescription>
                                    تحديد أوقات الحضور والانصراف المسموحة
                                </CardDescription>
                            </div>
                            <Button onClick={handleAdd}>
                                <Plus className="w-4 h-4 ml-2" />
                                إضافة نافذة زمنية
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>الاسم</TableHead>
                                        <TableHead>فترة الحضور</TableHead>
                                        <TableHead>فترة الانصراف</TableHead>
                                        <TableHead>فترة السماح</TableHead>
                                        <TableHead>الأيام</TableHead>
                                        <TableHead>الحالة</TableHead>
                                        <TableHead className="w-24">الإجراءات</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {timeWindows.map((window) => (
                                        <TableRow key={window.id}>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-medium">{window.name}</span>
                                                    {window.is_default && (
                                                        <Badge variant="outline" className="text-xs">افتراضي</Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 text-green-600">
                                                    <Timer className="w-4 h-4" />
                                                    {window.checkin_start} - {window.checkin_end}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 text-red-600">
                                                    <Timer className="w-4 h-4" />
                                                    {window.checkout_start} - {window.checkout_end}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    <div>تأخير: {window.late_grace_minutes} د</div>
                                                    <div>مبكر: {window.early_leave_grace_minutes} د</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm max-w-32 truncate" title={getDayNames(window.days_of_week)}>
                                                    {getDayNames(window.days_of_week)}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {window.is_active ? (
                                                    <Badge className="bg-green-500">
                                                        <CheckCircle className="w-3 h-3 ml-1" />
                                                        نشط
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="secondary">
                                                        <XCircle className="w-3 h-3 ml-1" />
                                                        معطل
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => handleEdit(window)}
                                                    >
                                                        <Pencil className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => handleDeleteClick(window)}
                                                        disabled={window.is_default}
                                                    >
                                                        <Trash2 className="w-4 h-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {timeWindows.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-8 text-gray-500">
                                                لا توجد نوافذ زمنية مسجلة
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* نافذة الإضافة/التعديل */}
            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>
                            {editingWindow ? 'تعديل النافذة الزمنية' : 'إضافة نافذة زمنية جديدة'}
                        </DialogTitle>
                        <DialogDescription>
                            حدد أوقات الحضور والانصراف المسموحة
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">اسم النافذة الزمنية</Label>
                            <Input
                                id="name"
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                placeholder="مثال: الدوام الصباحي"
                                required
                            />
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="checkin_start">بداية الحضور</Label>
                                <Input
                                    id="checkin_start"
                                    type="time"
                                    value={formData.checkin_start}
                                    onChange={(e) => setFormData({ ...formData, checkin_start: e.target.value })}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="checkin_end">نهاية الحضور</Label>
                                <Input
                                    id="checkin_end"
                                    type="time"
                                    value={formData.checkin_end}
                                    onChange={(e) => setFormData({ ...formData, checkin_end: e.target.value })}
                                    required
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="checkout_start">بداية الانصراف</Label>
                                <Input
                                    id="checkout_start"
                                    type="time"
                                    value={formData.checkout_start}
                                    onChange={(e) => setFormData({ ...formData, checkout_start: e.target.value })}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="checkout_end">نهاية الانصراف</Label>
                                <Input
                                    id="checkout_end"
                                    type="time"
                                    value={formData.checkout_end}
                                    onChange={(e) => setFormData({ ...formData, checkout_end: e.target.value })}
                                    required
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="late_grace_minutes">فترة سماح التأخير (دقائق)</Label>
                                <Input
                                    id="late_grace_minutes"
                                    type="number"
                                    min="0"
                                    max="60"
                                    value={formData.late_grace_minutes}
                                    onChange={(e) => setFormData({ ...formData, late_grace_minutes: parseInt(e.target.value) || 0 })}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="early_leave_grace_minutes">فترة سماح المغادرة المبكرة (دقائق)</Label>
                                <Input
                                    id="early_leave_grace_minutes"
                                    type="number"
                                    min="0"
                                    max="60"
                                    value={formData.early_leave_grace_minutes}
                                    onChange={(e) => setFormData({ ...formData, early_leave_grace_minutes: parseInt(e.target.value) || 0 })}
                                />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label>أيام العمل</Label>
                            <div className="flex flex-wrap gap-2">
                                {daysOfWeek.map((day) => (
                                    <Button
                                        key={day.value}
                                        type="button"
                                        variant={formData.days_of_week.includes(day.value) ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => toggleDay(day.value)}
                                    >
                                        {day.label}
                                    </Button>
                                ))}
                            </div>
                        </div>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="is_active">نشط</Label>
                            <Switch
                                id="is_active"
                                checked={formData.is_active}
                                onCheckedChange={(checked) => setFormData({ ...formData, is_active: checked })}
                            />
                        </div>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="is_default">جعله الافتراضي</Label>
                            <Switch
                                id="is_default"
                                checked={formData.is_default}
                                onCheckedChange={(checked) => setFormData({ ...formData, is_default: checked })}
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                                إلغاء
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting && <Loader2 className="w-4 h-4 animate-spin ml-2" />}
                                {editingWindow ? 'تحديث' : 'إضافة'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* نافذة تأكيد الحذف */}
            <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>تأكيد الحذف</DialogTitle>
                        <DialogDescription>
                            هل أنت متأكد من حذف النافذة الزمنية "{deletingWindow?.name}"؟
                            لا يمكن التراجع عن هذا الإجراء.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsDeleteDialogOpen(false)}>
                            إلغاء
                        </Button>
                        <Button variant="destructive" onClick={handleDelete} disabled={isSubmitting}>
                            {isSubmitting && <Loader2 className="w-4 h-4 animate-spin ml-2" />}
                            حذف
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
