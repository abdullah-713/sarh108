import React, { useState } from 'react';
import { Head, usePage, router, Link } from '@inertiajs/react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { 
    Wifi, 
    Plus, 
    Pencil, 
    Trash2, 
    Building2,
    CheckCircle,
    XCircle,
    Loader2
} from 'lucide-react';

interface WifiNetwork {
    id: number;
    name: string;
    ssid: string;
    bssid: string | null;
    branch_id: number;
    branch?: { id: number; name: string };
    is_active: boolean;
    priority: number;
    created_at: string;
}

interface Branch {
    id: number;
    name: string;
}

interface PageProps {
    wifiNetworks: WifiNetwork[];
    branches: Branch[];
}

export default function WifiNetworksIndex() {
    const { wifiNetworks, branches } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [editingNetwork, setEditingNetwork] = useState<WifiNetwork | null>(null);
    const [deletingNetwork, setDeletingNetwork] = useState<WifiNetwork | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    const [formData, setFormData] = useState({
        name: '',
        ssid: '',
        bssid: '',
        branch_id: '',
        is_active: true,
        priority: 1,
    });

    // فتح نافذة الإضافة
    const handleAdd = () => {
        setEditingNetwork(null);
        setFormData({
            name: '',
            ssid: '',
            bssid: '',
            branch_id: branches[0]?.id.toString() || '',
            is_active: true,
            priority: 1,
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة التعديل
    const handleEdit = (network: WifiNetwork) => {
        setEditingNetwork(network);
        setFormData({
            name: network.name,
            ssid: network.ssid,
            bssid: network.bssid || '',
            branch_id: network.branch_id.toString(),
            is_active: network.is_active,
            priority: network.priority,
        });
        setIsDialogOpen(true);
    };

    // فتح نافذة الحذف
    const handleDeleteClick = (network: WifiNetwork) => {
        setDeletingNetwork(network);
        setIsDeleteDialogOpen(true);
    };

    // حفظ البيانات
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const url = editingNetwork 
            ? `/hr/wifi-networks/${editingNetwork.id}` 
            : '/hr/wifi-networks';
        
        const method = editingNetwork ? 'put' : 'post';

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

    // حذف الشبكة
    const handleDelete = () => {
        if (!deletingNetwork) return;
        setIsSubmitting(true);

        router.delete(`/hr/wifi-networks/${deletingNetwork.id}`, {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setDeletingNetwork(null);
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            },
        });
    };

    return (
        <AppLayout>
            <Head title="شبكات Wi-Fi" />

            <div className="max-w-6xl mx-auto p-4 space-y-6">
                <Card>
                    <CardHeader>
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-xl flex items-center gap-2">
                                    <Wifi className="w-5 h-5 text-primary" />
                                    إدارة شبكات Wi-Fi
                                </CardTitle>
                                <CardDescription>
                                    شبكات Wi-Fi المعتمدة للتحقق من موقع الموظفين
                                </CardDescription>
                            </div>
                            <Button onClick={handleAdd}>
                                <Plus className="w-4 h-4 ml-2" />
                                إضافة شبكة
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>اسم الشبكة</TableHead>
                                        <TableHead>SSID</TableHead>
                                        <TableHead>BSSID</TableHead>
                                        <TableHead>الفرع</TableHead>
                                        <TableHead>الأولوية</TableHead>
                                        <TableHead>الحالة</TableHead>
                                        <TableHead className="w-24">الإجراءات</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {wifiNetworks.map((network) => (
                                        <TableRow key={network.id}>
                                            <TableCell className="font-medium">{network.name}</TableCell>
                                            <TableCell>
                                                <code className="bg-gray-100 px-2 py-1 rounded text-sm">
                                                    {network.ssid}
                                                </code>
                                            </TableCell>
                                            <TableCell>
                                                {network.bssid ? (
                                                    <code className="bg-gray-100 px-2 py-1 rounded text-sm">
                                                        {network.bssid}
                                                    </code>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    <Building2 className="w-3 h-3 ml-1" />
                                                    {network.branch?.name}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">{network.priority}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                {network.is_active ? (
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
                                                        onClick={() => handleEdit(network)}
                                                    >
                                                        <Pencil className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => handleDeleteClick(network)}
                                                    >
                                                        <Trash2 className="w-4 h-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {wifiNetworks.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-8 text-gray-500">
                                                لا توجد شبكات Wi-Fi مسجلة
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
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editingNetwork ? 'تعديل شبكة Wi-Fi' : 'إضافة شبكة Wi-Fi جديدة'}
                        </DialogTitle>
                        <DialogDescription>
                            أدخل بيانات شبكة Wi-Fi للتحقق من موقع الموظفين
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">اسم الشبكة</Label>
                            <Input
                                id="name"
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                placeholder="مثال: شبكة المكتب الرئيسي"
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="ssid">SSID</Label>
                            <Input
                                id="ssid"
                                value={formData.ssid}
                                onChange={(e) => setFormData({ ...formData, ssid: e.target.value })}
                                placeholder="اسم الشبكة كما يظهر في الجهاز"
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="bssid">BSSID (اختياري)</Label>
                            <Input
                                id="bssid"
                                value={formData.bssid}
                                onChange={(e) => setFormData({ ...formData, bssid: e.target.value })}
                                placeholder="AA:BB:CC:DD:EE:FF"
                            />
                            <p className="text-xs text-gray-500">
                                معرف الشبكة الفريد للتحقق الدقيق
                            </p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="branch_id">الفرع</Label>
                            <Select
                                value={formData.branch_id}
                                onValueChange={(value) => setFormData({ ...formData, branch_id: value })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="اختر الفرع" />
                                </SelectTrigger>
                                <SelectContent>
                                    {branches.map((branch) => (
                                        <SelectItem key={branch.id} value={branch.id.toString()}>
                                            {branch.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="priority">الأولوية</Label>
                            <Input
                                id="priority"
                                type="number"
                                min="1"
                                max="10"
                                value={formData.priority}
                                onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) || 1 })}
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
                                {editingNetwork ? 'تحديث' : 'إضافة'}
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
                            هل أنت متأكد من حذف شبكة "{deletingNetwork?.name}"؟
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
