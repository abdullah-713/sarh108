import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    TrendingDown, 
    Plus, 
    Pencil, 
    Trash2, 
    CheckCircle,
    XCircle,
    Loader2,
    AlertTriangle,
    Clock
} from 'lucide-react';

interface DeductionTier {
    id: number;
    name: string;
    min_late_minutes: number;
    max_late_minutes: number;
    deduction_points: number;
    deduction_percentage: number;
    description: string | null;
    is_active: boolean;
}

interface PageProps {
    deductionTiers: DeductionTier[];
}

export default function DeductionTiersIndex() {
    const { deductionTiers } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [editingTier, setEditingTier] = useState<DeductionTier | null>(null);
    const [deletingTier, setDeletingTier] = useState<DeductionTier | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    const [formData, setFormData] = useState({
        name: '',
        min_late_minutes: 0,
        max_late_minutes: 15,
        deduction_points: 0,
        deduction_percentage: 0,
        description: '',
        is_active: true,
    });

    // فتح نافذة الإضافة
    const handleAdd = () => {
        setEditingTier(null);
        setFormData({
            name: '',
            min_late_minutes: 0,
            max_late_minutes: 15,
            deduction_points: 0,
            deduction_percentage: 0,
            description: '',
            is_active: true,
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة التعديل
    const handleEdit = (tier: DeductionTier) => {
        setEditingTier(tier);
        setFormData({
            name: tier.name,
            min_late_minutes: tier.min_late_minutes,
            max_late_minutes: tier.max_late_minutes,
            deduction_points: tier.deduction_points,
            deduction_percentage: tier.deduction_percentage,
            description: tier.description || '',
            is_active: tier.is_active,
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة الحذف
    const handleDeleteClick = (tier: DeductionTier) => {
        setDeletingTier(tier);
        setIsDeleteDialogOpen(true);
    };

    // حفظ البيانات
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const url = editingTier 
            ? `/hr/deduction-tiers/${editingTier.id}` 
            : '/hr/deduction-tiers';
        
        const method = editingTier ? 'put' : 'post';

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

    // حذف المستوى
    const handleDelete = () => {
        if (!deletingTier) return;
        setIsSubmitting(true);

        router.delete(`/hr/deduction-tiers/${deletingTier.id}`, {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setDeletingTier(null);
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            },
        });
    };

    // الحصول على لون الشدة
    const getSeverityColor = (points: number) => {
        if (points === 0) return 'bg-green-500';
        if (points <= 15) return 'bg-yellow-500';
        if (points <= 30) return 'bg-orange-500';
        return 'bg-red-500';
    };

    return (
        <AppLayout>
            <Head title="مستويات الخصم" />

            <div className="max-w-6xl mx-auto p-4 space-y-6">
                {/* توضيح النظام */}
                <Card className="border-orange-200 bg-orange-50">
                    <CardContent className="p-4">
                        <div className="flex items-start gap-3">
                            <AlertTriangle className="w-5 h-5 text-orange-500 mt-0.5" />
                            <div>
                                <h4 className="font-medium text-orange-800">نظام الخصم التصاعدي</h4>
                                <p className="text-sm text-orange-700 mt-1">
                                    يتم تطبيق الخصم تلقائياً بناءً على مدة التأخير. كلما زاد التأخير، زادت نقاط الخصم.
                                    يمكنك تخصيص المستويات حسب سياسة شركتك.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-xl flex items-center gap-2">
                                    <TrendingDown className="w-5 h-5 text-primary" />
                                    إدارة مستويات الخصم
                                </CardTitle>
                                <CardDescription>
                                    تحديد نظام الخصم التصاعدي للتأخير
                                </CardDescription>
                            </div>
                            <Button onClick={handleAdd}>
                                <Plus className="w-4 h-4 ml-2" />
                                إضافة مستوى
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>المستوى</TableHead>
                                        <TableHead>نطاق التأخير</TableHead>
                                        <TableHead>نقاط الخصم</TableHead>
                                        <TableHead>نسبة الخصم</TableHead>
                                        <TableHead>الوصف</TableHead>
                                        <TableHead>الحالة</TableHead>
                                        <TableHead className="w-24">الإجراءات</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {deductionTiers.map((tier) => (
                                        <TableRow key={tier.id}>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div className={`w-3 h-3 rounded-full ${getSeverityColor(tier.deduction_points)}`}></div>
                                                    <span className="font-medium">{tier.name}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 text-sm">
                                                    <Clock className="w-4 h-4 text-gray-400" />
                                                    {tier.min_late_minutes} - {tier.max_late_minutes} دقيقة
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getSeverityColor(tier.deduction_points)}>
                                                    {tier.deduction_points} نقطة
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {tier.deduction_percentage > 0 ? (
                                                    <span className="text-red-600">{tier.deduction_percentage}%</span>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-sm text-gray-500 max-w-32 truncate block">
                                                    {tier.description || '-'}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {tier.is_active ? (
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
                                                        onClick={() => handleEdit(tier)}
                                                    >
                                                        <Pencil className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => handleDeleteClick(tier)}
                                                    >
                                                        <Trash2 className="w-4 h-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {deductionTiers.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-8 text-gray-500">
                                                لا توجد مستويات خصم مسجلة
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* عرض بصري للمستويات */}
                {deductionTiers.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">المخطط البصري للخصومات</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {deductionTiers.sort((a, b) => a.min_late_minutes - b.min_late_minutes).map((tier) => (
                                    <div key={tier.id} className="flex items-center gap-4">
                                        <div className="w-32 text-sm text-gray-500">
                                            {tier.min_late_minutes}-{tier.max_late_minutes} دق
                                        </div>
                                        <div className="flex-1 h-8 bg-gray-100 rounded-lg overflow-hidden relative">
                                            <div 
                                                className={`h-full ${getSeverityColor(tier.deduction_points)} transition-all`}
                                                style={{ width: `${Math.min(tier.deduction_points * 2, 100)}%` }}
                                            />
                                            <span className="absolute inset-0 flex items-center justify-center text-sm font-medium">
                                                {tier.name} ({tier.deduction_points} نقطة)
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* نافذة الإضافة/التعديل */}
            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editingTier ? 'تعديل مستوى الخصم' : 'إضافة مستوى خصم جديد'}
                        </DialogTitle>
                        <DialogDescription>
                            حدد نطاق التأخير ومقدار الخصم المقابل
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">اسم المستوى</Label>
                            <Input
                                id="name"
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                placeholder="مثال: تأخير بسيط"
                                required
                            />
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="min_late_minutes">من (دقيقة)</Label>
                                <Input
                                    id="min_late_minutes"
                                    type="number"
                                    min="0"
                                    value={formData.min_late_minutes}
                                    onChange={(e) => setFormData({ ...formData, min_late_minutes: parseInt(e.target.value) || 0 })}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="max_late_minutes">إلى (دقيقة)</Label>
                                <Input
                                    id="max_late_minutes"
                                    type="number"
                                    min="0"
                                    value={formData.max_late_minutes}
                                    onChange={(e) => setFormData({ ...formData, max_late_minutes: parseInt(e.target.value) || 0 })}
                                    required
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="deduction_points">نقاط الخصم</Label>
                                <Input
                                    id="deduction_points"
                                    type="number"
                                    min="0"
                                    value={formData.deduction_points}
                                    onChange={(e) => setFormData({ ...formData, deduction_points: parseInt(e.target.value) || 0 })}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="deduction_percentage">نسبة الخصم (%)</Label>
                                <Input
                                    id="deduction_percentage"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.5"
                                    value={formData.deduction_percentage}
                                    onChange={(e) => setFormData({ ...formData, deduction_percentage: parseFloat(e.target.value) || 0 })}
                                />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">الوصف (اختياري)</Label>
                            <Textarea
                                id="description"
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                placeholder="وصف المستوى..."
                                rows={2}
                            />
                        </div>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="is_active">نشط</Label>
                            <Switch
                                id="is_active"
                                checked={formData.is_active}
                                onCheckedChange={(checked) => setFormData({ ...formData, is_active: checked })}
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                                إلغاء
                            </Button>
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting && <Loader2 className="w-4 h-4 animate-spin ml-2" />}
                                {editingTier ? 'تحديث' : 'إضافة'}
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
                            هل أنت متأكد من حذف مستوى الخصم "{deletingTier?.name}"؟
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
