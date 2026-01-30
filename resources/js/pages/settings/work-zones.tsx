import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { MapPin, Plus, Pencil, Trash2, Eye, Building2, Users, Clock } from 'lucide-react';
import { useState } from 'react';

interface WorkZone {
    id: number;
    name: string;
    name_ar: string | null;
    description: string | null;
    zone_type: string;
    zone_type_name: string;
    branch: { id: number; name: string };
    center_latitude: number | null;
    center_longitude: number | null;
    radius_meters: number;
    is_active: boolean;
    color: string;
    requires_authentication: boolean;
    access_logs_count: number;
}

interface PaginatedZones {
    data: WorkZone[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    zones: PaginatedZones;
    zoneTypes: Record<string, string>;
}

export default function WorkZones({ zones, zoneTypes }: Props) {
    const [isCreating, setIsCreating] = useState(false);
    const [editingZone, setEditingZone] = useState<WorkZone | null>(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        branch_id: '',
        name: '',
        name_ar: '',
        description: '',
        zone_type: 'indoor',
        center_latitude: '',
        center_longitude: '',
        radius_meters: 50,
        floor_number: '',
        building: '',
        requires_authentication: false,
        track_time_in_zone: true,
        is_active: true,
        color: '#ff8531',
    });

    const handleCreate = () => {
        post('/settings/work-zones', {
            onSuccess: () => {
                setIsCreating(false);
                reset();
            },
        });
    };

    const handleUpdate = () => {
        if (!editingZone) return;
        put(`/settings/work-zones/${editingZone.id}`, {
            onSuccess: () => {
                setEditingZone(null);
                reset();
            },
        });
    };

    const handleDelete = (zoneId: number) => {
        if (confirm('هل أنت متأكد من حذف هذه المنطقة؟')) {
            router.delete(`/settings/work-zones/${zoneId}`);
        }
    };

    return (
        <AppLayout>
            <Head title="مناطق العمل" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <MapPin className="w-8 h-8 text-orange-500" />
                            مناطق العمل
                        </h1>
                        <p className="mt-1 text-gray-600">
                            تحديد وإدارة مناطق العمل داخل الفروع
                        </p>
                    </div>

                    <Dialog open={isCreating} onOpenChange={setIsCreating}>
                        <DialogTrigger asChild>
                            <Button className="bg-orange-500 hover:bg-orange-600">
                                <Plus className="w-4 h-4 ml-2" />
                                إضافة منطقة
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-lg">
                            <DialogHeader>
                                <DialogTitle>إضافة منطقة عمل جديدة</DialogTitle>
                                <DialogDescription>
                                    أدخل تفاصيل المنطقة الجديدة
                                </DialogDescription>
                            </DialogHeader>
                            <div className="space-y-4 py-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>اسم المنطقة</Label>
                                        <Input
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="مثال: القاعة الرئيسية"
                                        />
                                    </div>
                                    <div>
                                        <Label>الاسم بالعربية</Label>
                                        <Input
                                            value={data.name_ar}
                                            onChange={(e) => setData('name_ar', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div>
                                    <Label>نوع المنطقة</Label>
                                    <Select value={data.zone_type} onValueChange={(v) => setData('zone_type', v)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(zoneTypes).map(([value, label]) => (
                                                <SelectItem key={value} value={value}>{label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>خط العرض</Label>
                                        <Input
                                            type="number"
                                            step="any"
                                            value={data.center_latitude}
                                            onChange={(e) => setData('center_latitude', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>خط الطول</Label>
                                        <Input
                                            type="number"
                                            step="any"
                                            value={data.center_longitude}
                                            onChange={(e) => setData('center_longitude', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>نصف القطر (متر)</Label>
                                        <Input
                                            type="number"
                                            value={data.radius_meters}
                                            onChange={(e) => setData('radius_meters', parseInt(e.target.value))}
                                        />
                                    </div>
                                    <div>
                                        <Label>اللون</Label>
                                        <Input
                                            type="color"
                                            value={data.color}
                                            onChange={(e) => setData('color', e.target.value)}
                                        />
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button variant="outline" onClick={() => setIsCreating(false)}>
                                    إلغاء
                                </Button>
                                <Button onClick={handleCreate} disabled={processing}>
                                    إضافة
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* Zones Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {zones.data.map((zone) => (
                        <Card key={zone.id} className={`relative ${!zone.is_active && 'opacity-60'}`}>
                            <div
                                className="absolute top-0 left-0 right-0 h-1 rounded-t-lg"
                                style={{ backgroundColor: zone.color }}
                            />
                            <CardHeader className="pb-2">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <CardTitle className="text-lg">{zone.name_ar || zone.name}</CardTitle>
                                        <CardDescription className="flex items-center gap-1">
                                            <Building2 className="w-3 h-3" />
                                            {zone.branch.name}
                                        </CardDescription>
                                    </div>
                                    <Badge variant={zone.is_active ? 'default' : 'secondary'}>
                                        {zone.is_active ? 'نشطة' : 'معطلة'}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2 text-sm">
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">النوع:</span>
                                        <Badge variant="outline">{zone.zone_type_name}</Badge>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">نصف القطر:</span>
                                        <span>{zone.radius_meters} متر</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">سجلات الدخول:</span>
                                        <span className="flex items-center gap-1">
                                            <Users className="w-3 h-3" />
                                            {zone.access_logs_count}
                                        </span>
                                    </div>
                                    {zone.requires_authentication && (
                                        <Badge className="bg-orange-100 text-orange-800">
                                            يتطلب مصادقة
                                        </Badge>
                                    )}
                                </div>

                                <div className="flex justify-end gap-2 mt-4 pt-4 border-t">
                                    <Button size="sm" variant="ghost">
                                        <Eye className="w-4 h-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        onClick={() => setEditingZone(zone)}
                                    >
                                        <Pencil className="w-4 h-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        className="text-red-500"
                                        onClick={() => handleDelete(zone.id)}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {zones.data.length === 0 && (
                    <Card>
                        <CardContent className="py-12 text-center text-gray-500">
                            <MapPin className="w-12 h-12 mx-auto mb-4 opacity-30" />
                            <p>لا توجد مناطق عمل</p>
                            <p className="text-sm">ابدأ بإضافة منطقة جديدة</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
