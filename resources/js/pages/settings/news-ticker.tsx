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
import { Megaphone, Plus, Edit2, Trash2, Eye, MousePointer, Bell, Trophy, Award, Flame, AlertTriangle, PartyPopper, Clock, Globe } from 'lucide-react';
import { useState } from 'react';

interface NewsItem {
    id: number;
    title: string;
    content: string | null;
    type: string;
    priority: string;
    icon: string | null;
    color: string | null;
    background_color: string | null;
    branch_id: number | null;
    is_global: boolean;
    starts_at: string | null;
    ends_at: string | null;
    is_active: boolean;
    views_count: number;
    clicks_count: number;
    action_url: string | null;
    action_text: string | null;
    created_at: string;
}

interface PaginatedNews {
    data: NewsItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    newsItems: PaginatedNews;
    branches: { id: number; name: string }[];
}

const typeOptions = [
    { value: 'announcement', label: 'إعلان', icon: Megaphone, color: 'bg-blue-500' },
    { value: 'achievement', label: 'إنجاز', icon: Trophy, color: 'bg-green-500' },
    { value: 'reminder', label: 'تذكير', icon: Clock, color: 'bg-yellow-500' },
    { value: 'warning', label: 'تحذير', icon: AlertTriangle, color: 'bg-red-500' },
    { value: 'celebration', label: 'احتفال', icon: PartyPopper, color: 'bg-purple-500' },
    { value: 'mvp', label: 'MVP', icon: Trophy, color: 'bg-orange-500' },
    { value: 'badge', label: 'شارة', icon: Award, color: 'bg-indigo-500' },
    { value: 'streak', label: 'سلسلة', icon: Flame, color: 'bg-orange-600' },
    { value: 'custom', label: 'مخصص', icon: Bell, color: 'bg-gray-500' },
];

const priorityOptions = [
    { value: 'low', label: 'منخفضة', color: 'bg-gray-400' },
    { value: 'normal', label: 'عادية', color: 'bg-blue-400' },
    { value: 'high', label: 'عالية', color: 'bg-orange-400' },
    { value: 'urgent', label: 'عاجلة', color: 'bg-red-500' },
];

export default function NewsTickerPage({ newsItems, branches }: Props) {
    const [isOpen, setIsOpen] = useState(false);
    const [editingItem, setEditingItem] = useState<NewsItem | null>(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        title: '',
        content: '',
        type: 'announcement',
        priority: 'normal',
        icon: '',
        color: '#ff8531',
        background_color: '#fff7ed',
        branch_id: '',
        is_global: true,
        starts_at: '',
        ends_at: '',
        is_active: true,
        action_url: '',
        action_text: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const submitData = {
            ...data,
            branch_id: data.branch_id || null,
        };

        if (editingItem) {
            put(`/settings/news-ticker/${editingItem.id}`, {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingItem(null);
                },
            });
        } else {
            post('/settings/news-ticker', {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                },
            });
        }
    };

    const handleEdit = (item: NewsItem) => {
        setEditingItem(item);
        setData({
            title: item.title,
            content: item.content || '',
            type: item.type,
            priority: item.priority,
            icon: item.icon || '',
            color: item.color || '#ff8531',
            background_color: item.background_color || '#fff7ed',
            branch_id: item.branch_id?.toString() || '',
            is_global: item.is_global,
            starts_at: item.starts_at?.slice(0, 16) || '',
            ends_at: item.ends_at?.slice(0, 16) || '',
            is_active: item.is_active,
            action_url: item.action_url || '',
            action_text: item.action_text || '',
        });
        setIsOpen(true);
    };

    const handleDelete = (item: NewsItem) => {
        if (confirm(`هل أنت متأكد من حذف "${item.title}"؟`)) {
            router.delete(`/settings/news-ticker/${item.id}`);
        }
    };

    const getTypeConfig = (type: string) => {
        return typeOptions.find(t => t.value === type) || typeOptions[0];
    };

    const getPriorityConfig = (priority: string) => {
        return priorityOptions.find(p => p.value === priority) || priorityOptions[1];
    };

    return (
        <AppLayout>
            <Head title="شريط الأخبار" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Megaphone className="w-8 h-8 text-orange-500" />
                            شريط الأخبار
                        </h1>
                        <p className="mt-1 text-gray-600">
                            إدارة الإعلانات والإشعارات للموظفين
                        </p>
                    </div>

                    <Dialog open={isOpen} onOpenChange={(open) => {
                        setIsOpen(open);
                        if (!open) {
                            reset();
                            setEditingItem(null);
                        }
                    }}>
                        <DialogTrigger asChild>
                            <Button className="bg-orange-500 hover:bg-orange-600">
                                <Plus className="w-4 h-4 ml-2" />
                                إضافة خبر
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle>
                                    {editingItem ? 'تعديل الخبر' : 'إضافة خبر جديد'}
                                </DialogTitle>
                                <DialogDescription>
                                    أدخل تفاصيل الخبر أو الإعلان
                                </DialogDescription>
                            </DialogHeader>

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>العنوان</Label>
                                    <Input
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="عنوان الخبر"
                                        required
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label>المحتوى (اختياري)</Label>
                                    <Textarea
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        placeholder="تفاصيل إضافية..."
                                        rows={3}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>النوع</Label>
                                        <Select value={data.type} onValueChange={(v) => setData('type', v)}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {typeOptions.map((type) => (
                                                    <SelectItem key={type.value} value={type.value}>
                                                        <div className="flex items-center gap-2">
                                                            <span className={`w-2 h-2 rounded-full ${type.color}`} />
                                                            {type.label}
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>الأولوية</Label>
                                        <Select value={data.priority} onValueChange={(v) => setData('priority', v)}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {priorityOptions.map((priority) => (
                                                    <SelectItem key={priority.value} value={priority.value}>
                                                        <div className="flex items-center gap-2">
                                                            <span className={`w-2 h-2 rounded-full ${priority.color}`} />
                                                            {priority.label}
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>الفرع (اختياري)</Label>
                                        <Select value={data.branch_id} onValueChange={(v) => setData('branch_id', v)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="كل الفروع" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="">كل الفروع</SelectItem>
                                                {branches.map((branch) => (
                                                    <SelectItem key={branch.id} value={branch.id.toString()}>
                                                        {branch.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="flex items-center gap-4 pt-7">
                                        <Switch
                                            checked={data.is_global}
                                            onCheckedChange={(checked) => setData('is_global', checked)}
                                        />
                                        <Label>عام لكل الفروع</Label>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>تاريخ البدء</Label>
                                        <Input
                                            type="datetime-local"
                                            value={data.starts_at}
                                            onChange={(e) => setData('starts_at', e.target.value)}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>تاريخ الانتهاء</Label>
                                        <Input
                                            type="datetime-local"
                                            value={data.ends_at}
                                            onChange={(e) => setData('ends_at', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>رابط الإجراء (اختياري)</Label>
                                        <Input
                                            type="url"
                                            value={data.action_url}
                                            onChange={(e) => setData('action_url', e.target.value)}
                                            placeholder="https://..."
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>نص الزر (اختياري)</Label>
                                        <Input
                                            value={data.action_text}
                                            onChange={(e) => setData('action_text', e.target.value)}
                                            placeholder="عرض التفاصيل"
                                        />
                                    </div>
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

                                <div className="flex items-center gap-2">
                                    <Switch
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked)}
                                    />
                                    <Label>نشط</Label>
                                </div>

                                <DialogFooter>
                                    <Button type="button" variant="outline" onClick={() => setIsOpen(false)}>
                                        إلغاء
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-orange-500 hover:bg-orange-600">
                                        {editingItem ? 'تحديث' : 'إضافة'}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                {/* News List */}
                <Card>
                    <CardHeader>
                        <CardTitle>الأخبار والإعلانات</CardTitle>
                        <CardDescription>إجمالي {newsItems.total} عنصر</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {newsItems.data.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <Megaphone className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد أخبار أو إعلانات</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {newsItems.data.map((item) => {
                                    const typeConfig = getTypeConfig(item.type);
                                    const priorityConfig = getPriorityConfig(item.priority);
                                    const TypeIcon = typeConfig.icon;

                                    return (
                                        <div
                                            key={item.id}
                                            className={`flex items-start gap-4 p-4 rounded-lg border ${
                                                !item.is_active ? 'bg-gray-50 opacity-60' : 'bg-white hover:bg-gray-50'
                                            } transition-colors`}
                                        >
                                            <div
                                                className={`w-12 h-12 rounded-xl flex items-center justify-center ${typeConfig.color} text-white`}
                                            >
                                                <TypeIcon className="w-6 h-6" />
                                            </div>

                                            <div className="flex-1">
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <h3 className="font-semibold text-gray-900">{item.title}</h3>
                                                        {item.content && (
                                                            <p className="text-sm text-gray-600 mt-1 line-clamp-2">
                                                                {item.content}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-1">
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() => handleEdit(item)}
                                                        >
                                                            <Edit2 className="w-4 h-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() => handleDelete(item)}
                                                            className="text-red-500 hover:text-red-600"
                                                        >
                                                            <Trash2 className="w-4 h-4" />
                                                        </Button>
                                                    </div>
                                                </div>

                                                <div className="flex flex-wrap items-center gap-2 mt-3">
                                                    <Badge variant="outline" className={`${typeConfig.color} text-white border-0`}>
                                                        {typeConfig.label}
                                                    </Badge>
                                                    <Badge variant="outline" className={`${priorityConfig.color} text-white border-0`}>
                                                        {priorityConfig.label}
                                                    </Badge>
                                                    {item.is_global && (
                                                        <Badge variant="secondary">
                                                            <Globe className="w-3 h-3 ml-1" />
                                                            عام
                                                        </Badge>
                                                    )}
                                                    {!item.is_active && (
                                                        <Badge variant="destructive">معطل</Badge>
                                                    )}
                                                </div>

                                                <div className="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                                    <span className="flex items-center gap-1">
                                                        <Eye className="w-4 h-4" />
                                                        {item.views_count} مشاهدة
                                                    </span>
                                                    <span className="flex items-center gap-1">
                                                        <MousePointer className="w-4 h-4" />
                                                        {item.clicks_count} نقرة
                                                    </span>
                                                    {item.starts_at && (
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="w-4 h-4" />
                                                            من: {new Date(item.starts_at).toLocaleDateString('ar-SA')}
                                                        </span>
                                                    )}
                                                    {item.ends_at && (
                                                        <span>
                                                            إلى: {new Date(item.ends_at).toLocaleDateString('ar-SA')}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}

                        {/* Pagination */}
                        {newsItems.last_page > 1 && (
                            <div className="flex justify-center gap-2 mt-6">
                                {Array.from({ length: newsItems.last_page }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === newsItems.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get('/settings/news-ticker', { page })}
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
