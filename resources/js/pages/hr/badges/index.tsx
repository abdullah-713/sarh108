import AppLayout from '@/layouts/app-layout';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Award, Plus, Edit2, Trash2, Users, Star, Clock, Target, Flame, Trophy, UserCheck, Sparkles } from 'lucide-react';
import { useState } from 'react';

interface BadgeData {
    id: number;
    name: string;
    name_ar: string;
    slug: string;
    description: string | null;
    description_ar: string | null;
    icon: string | null;
    color: string | null;
    background_color: string | null;
    tier: string;
    tier_name: string;
    tier_color: string;
    type: string;
    type_name: string;
    required_days: number | null;
    required_streak: number | null;
    required_rate: number | null;
    points: number;
    is_active: boolean;
    is_auto_award: boolean;
    employees_count: number;
}

interface Props {
    badges: BadgeData[];
}

const tierOptions = [
    { value: 'bronze', label: 'برونزي', color: 'bg-amber-600' },
    { value: 'silver', label: 'فضي', color: 'bg-gray-400' },
    { value: 'gold', label: 'ذهبي', color: 'bg-yellow-500' },
    { value: 'platinum', label: 'بلاتيني', color: 'bg-cyan-400' },
    { value: 'diamond', label: 'ماسي', color: 'bg-purple-500' },
];

const typeOptions = [
    { value: 'punctuality', label: 'الالتزام بالوقت', icon: Clock },
    { value: 'attendance', label: 'الحضور', icon: UserCheck },
    { value: 'early_bird', label: 'الحضور المبكر', icon: Star },
    { value: 'streak', label: 'السلسلة المتتالية', icon: Flame },
    { value: 'perfect_month', label: 'الشهر المثالي', icon: Trophy },
    { value: 'mvp', label: 'الموظف المثالي', icon: Award },
    { value: 'team_player', label: 'روح الفريق', icon: Users },
    { value: 'custom', label: 'مخصص', icon: Sparkles },
];

export default function BadgesIndex({ badges }: Props) {
    const [isOpen, setIsOpen] = useState(false);
    const [editingBadge, setEditingBadge] = useState<BadgeData | null>(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        name_ar: '',
        slug: '',
        description: '',
        description_ar: '',
        icon: 'award',
        color: '#ff8531',
        background_color: '#fff7ed',
        tier: 'bronze',
        type: 'custom',
        required_days: 0,
        required_streak: 0,
        required_rate: 0,
        points: 10,
        is_active: true,
        is_auto_award: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingBadge) {
            put(`/hr/badges/${editingBadge.id}`, {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingBadge(null);
                },
            });
        } else {
            post('/hr/badges', {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                },
            });
        }
    };

    const handleEdit = (badge: BadgeData) => {
        setEditingBadge(badge);
        setData({
            name: badge.name,
            name_ar: badge.name_ar,
            slug: badge.slug,
            description: badge.description || '',
            description_ar: badge.description_ar || '',
            icon: badge.icon || 'award',
            color: badge.color || '#ff8531',
            background_color: badge.background_color || '#fff7ed',
            tier: badge.tier,
            type: badge.type,
            required_days: badge.required_days || 0,
            required_streak: badge.required_streak || 0,
            required_rate: badge.required_rate || 0,
            points: badge.points,
            is_active: badge.is_active,
            is_auto_award: badge.is_auto_award,
        });
        setIsOpen(true);
    };

    const handleDelete = (badge: BadgeData) => {
        if (confirm(`هل أنت متأكد من حذف شارة "${badge.name_ar}"؟`)) {
            router.delete(`/hr/badges/${badge.id}`);
        }
    };

    const handleCreateDefaults = () => {
        router.post('/hr/badges/create-defaults');
    };

    const getBadgeIcon = (type: string) => {
        const option = typeOptions.find(o => o.value === type);
        const Icon = option?.icon || Award;
        return <Icon className="w-6 h-6" />;
    };

    return (
        <AppLayout>
            <Head title="إدارة الشارات" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Award className="w-8 h-8 text-orange-500" />
                            إدارة الشارات
                        </h1>
                        <p className="mt-1 text-gray-600">
                            إنشاء وإدارة شارات الإنجاز للموظفين
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button variant="outline" onClick={handleCreateDefaults}>
                            <Sparkles className="w-4 h-4 ml-2" />
                            إنشاء الافتراضية
                        </Button>

                        <Dialog open={isOpen} onOpenChange={(open) => {
                            setIsOpen(open);
                            if (!open) {
                                reset();
                                setEditingBadge(null);
                            }
                        }}>
                            <DialogTrigger asChild>
                                <Button className="bg-orange-500 hover:bg-orange-600">
                                    <Plus className="w-4 h-4 ml-2" />
                                    شارة جديدة
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                                <DialogHeader>
                                    <DialogTitle>
                                        {editingBadge ? 'تعديل الشارة' : 'إنشاء شارة جديدة'}
                                    </DialogTitle>
                                    <DialogDescription>
                                        أدخل تفاصيل الشارة الجديدة
                                    </DialogDescription>
                                </DialogHeader>

                                <form onSubmit={handleSubmit} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label>الاسم (English)</Label>
                                            <Input
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="Badge Name"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label>الاسم (عربي)</Label>
                                            <Input
                                                value={data.name_ar}
                                                onChange={(e) => setData('name_ar', e.target.value)}
                                                placeholder="اسم الشارة"
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>المعرف (slug)</Label>
                                        <Input
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            placeholder="badge-slug"
                                            disabled={!!editingBadge}
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label>الوصف (English)</Label>
                                            <Textarea
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                placeholder="Badge description"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label>الوصف (عربي)</Label>
                                            <Textarea
                                                value={data.description_ar}
                                                onChange={(e) => setData('description_ar', e.target.value)}
                                                placeholder="وصف الشارة"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label>المستوى</Label>
                                            <Select value={data.tier} onValueChange={(v) => setData('tier', v)}>
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {tierOptions.map((tier) => (
                                                        <SelectItem key={tier.value} value={tier.value}>
                                                            <div className="flex items-center gap-2">
                                                                <span className={`w-3 h-3 rounded-full ${tier.color}`} />
                                                                {tier.label}
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label>النوع</Label>
                                            <Select value={data.type} onValueChange={(v) => setData('type', v)}>
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {typeOptions.map((type) => (
                                                        <SelectItem key={type.value} value={type.value}>
                                                            {type.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-3 gap-4">
                                        <div className="space-y-2">
                                            <Label>الأيام المطلوبة</Label>
                                            <Input
                                                type="number"
                                                value={data.required_days}
                                                onChange={(e) => setData('required_days', parseInt(e.target.value) || 0)}
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label>السلسلة المطلوبة</Label>
                                            <Input
                                                type="number"
                                                value={data.required_streak}
                                                onChange={(e) => setData('required_streak', parseInt(e.target.value) || 0)}
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label>النسبة المطلوبة %</Label>
                                            <Input
                                                type="number"
                                                step="0.1"
                                                value={data.required_rate}
                                                onChange={(e) => setData('required_rate', parseFloat(e.target.value) || 0)}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label>النقاط</Label>
                                            <Input
                                                type="number"
                                                value={data.points}
                                                onChange={(e) => setData('points', parseInt(e.target.value) || 0)}
                                            />
                                        </div>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="space-y-2">
                                                <Label>اللون</Label>
                                                <Input
                                                    type="color"
                                                    value={data.color}
                                                    onChange={(e) => setData('color', e.target.value)}
                                                />
                                            </div>
                                            <div className="space-y-2">
                                                <Label>لون الخلفية</Label>
                                                <Input
                                                    type="color"
                                                    value={data.background_color}
                                                    onChange={(e) => setData('background_color', e.target.value)}
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-6">
                                        <div className="flex items-center gap-2">
                                            <Switch
                                                checked={data.is_active}
                                                onCheckedChange={(checked) => setData('is_active', checked)}
                                            />
                                            <Label>نشطة</Label>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Switch
                                                checked={data.is_auto_award}
                                                onCheckedChange={(checked) => setData('is_auto_award', checked)}
                                            />
                                            <Label>منح تلقائي</Label>
                                        </div>
                                    </div>

                                    <DialogFooter>
                                        <Button type="button" variant="outline" onClick={() => setIsOpen(false)}>
                                            إلغاء
                                        </Button>
                                        <Button type="submit" disabled={processing} className="bg-orange-500 hover:bg-orange-600">
                                            {editingBadge ? 'تحديث' : 'إنشاء'}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Badges Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {badges.length === 0 ? (
                        <Card className="col-span-full">
                            <CardContent className="py-12 text-center">
                                <Award className="w-12 h-12 mx-auto text-gray-300 mb-4" />
                                <p className="text-gray-500">لا توجد شارات بعد</p>
                                <Button
                                    className="mt-4 bg-orange-500 hover:bg-orange-600"
                                    onClick={handleCreateDefaults}
                                >
                                    إنشاء الشارات الافتراضية
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        badges.map((badge) => (
                            <Card key={badge.id} className={`relative overflow-hidden ${!badge.is_active ? 'opacity-60' : ''}`}>
                                <div 
                                    className="absolute top-0 right-0 left-0 h-1"
                                    style={{ backgroundColor: badge.tier_color }}
                                />
                                <CardHeader className="pb-2">
                                    <div className="flex items-start justify-between">
                                        <div 
                                            className="w-14 h-14 rounded-xl flex items-center justify-center"
                                            style={{ 
                                                backgroundColor: badge.background_color || '#fff7ed',
                                                color: badge.color || '#ff8531'
                                            }}
                                        >
                                            {getBadgeIcon(badge.type)}
                                        </div>
                                        <div className="flex gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => handleEdit(badge)}
                                            >
                                                <Edit2 className="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => handleDelete(badge)}
                                                className="text-red-500 hover:text-red-600"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <CardTitle className="text-lg mt-2">{badge.name_ar}</CardTitle>
                                    <CardDescription>{badge.description_ar || badge.description}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2 mb-4">
                                        <Badge 
                                            style={{ backgroundColor: badge.tier_color }}
                                            className="text-white"
                                        >
                                            {badge.tier_name}
                                        </Badge>
                                        <Badge variant="outline">{badge.type_name}</Badge>
                                        {badge.is_auto_award && (
                                            <Badge variant="secondary">تلقائي</Badge>
                                        )}
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-2 text-sm">
                                        {badge.required_days > 0 && (
                                            <div className="text-gray-600">
                                                <Target className="w-4 h-4 inline ml-1" />
                                                {badge.required_days} يوم
                                            </div>
                                        )}
                                        {badge.required_streak > 0 && (
                                            <div className="text-gray-600">
                                                <Flame className="w-4 h-4 inline ml-1" />
                                                {badge.required_streak} سلسلة
                                            </div>
                                        )}
                                        {badge.required_rate > 0 && (
                                            <div className="text-gray-600">
                                                <Star className="w-4 h-4 inline ml-1" />
                                                {badge.required_rate}%
                                            </div>
                                        )}
                                        <div className="text-orange-600 font-medium">
                                            <Trophy className="w-4 h-4 inline ml-1" />
                                            {badge.points} نقطة
                                        </div>
                                    </div>

                                    <div className="mt-4 pt-4 border-t flex items-center justify-between">
                                        <div className="flex items-center text-gray-500">
                                            <Users className="w-4 h-4 ml-1" />
                                            <span>{badge.employees_count} موظف</span>
                                        </div>
                                        {!badge.is_active && (
                                            <Badge variant="destructive">معطلة</Badge>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
