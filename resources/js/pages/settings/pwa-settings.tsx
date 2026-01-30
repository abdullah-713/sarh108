import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Smartphone, Bell, Wifi, Palette, Save } from 'lucide-react';

interface PwaConfig {
    id: number;
    pwa_enabled: boolean;
    app_name: string;
    app_short_name: string;
    app_description: string | null;
    theme_color: string;
    background_color: string;
    display_mode: string;
    orientation: string;
    enable_push_notifications: boolean;
    enable_offline_mode: boolean;
}

interface Props {
    config: PwaConfig;
}

export default function PwaSettings({ config }: Props) {
    const { data, setData, put, processing } = useForm({
        pwa_enabled: config.pwa_enabled,
        app_name: config.app_name,
        app_short_name: config.app_short_name,
        app_description: config.app_description || '',
        theme_color: config.theme_color,
        background_color: config.background_color,
        display_mode: config.display_mode,
        orientation: config.orientation,
        enable_push_notifications: config.enable_push_notifications,
        enable_offline_mode: config.enable_offline_mode,
    });

    const handleSubmit = () => {
        put('/settings/pwa');
    };

    return (
        <AppLayout>
            <Head title="إعدادات التطبيق" />

            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <Smartphone className="w-8 h-8 text-orange-500" />
                        إعدادات التطبيق (PWA)
                    </h1>
                    <p className="mt-1 text-gray-600">
                        تخصيص تطبيق الويب التقدمي
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Basic Settings */}
                    <Card>
                        <CardHeader>
                            <CardTitle>الإعدادات الأساسية</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="pwa_enabled">تفعيل PWA</Label>
                                <Switch
                                    id="pwa_enabled"
                                    checked={data.pwa_enabled}
                                    onCheckedChange={(checked) => setData('pwa_enabled', checked)}
                                />
                            </div>

                            <div>
                                <Label>اسم التطبيق</Label>
                                <Input
                                    value={data.app_name}
                                    onChange={(e) => setData('app_name', e.target.value)}
                                />
                            </div>

                            <div>
                                <Label>الاسم المختصر</Label>
                                <Input
                                    value={data.app_short_name}
                                    onChange={(e) => setData('app_short_name', e.target.value)}
                                />
                            </div>

                            <div>
                                <Label>وصف التطبيق</Label>
                                <Textarea
                                    value={data.app_description}
                                    onChange={(e) => setData('app_description', e.target.value)}
                                    placeholder="وصف قصير للتطبيق..."
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Appearance */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Palette className="w-5 h-5" />
                                المظهر
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label>لون السمة</Label>
                                <div className="flex items-center gap-2">
                                    <Input
                                        type="color"
                                        value={data.theme_color}
                                        onChange={(e) => setData('theme_color', e.target.value)}
                                        className="w-20 h-10"
                                    />
                                    <Input
                                        value={data.theme_color}
                                        onChange={(e) => setData('theme_color', e.target.value)}
                                        className="flex-1"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>لون الخلفية</Label>
                                <div className="flex items-center gap-2">
                                    <Input
                                        type="color"
                                        value={data.background_color}
                                        onChange={(e) => setData('background_color', e.target.value)}
                                        className="w-20 h-10"
                                    />
                                    <Input
                                        value={data.background_color}
                                        onChange={(e) => setData('background_color', e.target.value)}
                                        className="flex-1"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>وضع العرض</Label>
                                <Select value={data.display_mode} onValueChange={(v) => setData('display_mode', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="standalone">مستقل</SelectItem>
                                        <SelectItem value="fullscreen">ملء الشاشة</SelectItem>
                                        <SelectItem value="minimal-ui">واجهة مصغرة</SelectItem>
                                        <SelectItem value="browser">متصفح</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label>الاتجاه</Label>
                                <Select value={data.orientation} onValueChange={(v) => setData('orientation', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="any">أي اتجاه</SelectItem>
                                        <SelectItem value="portrait">عمودي</SelectItem>
                                        <SelectItem value="landscape">أفقي</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notifications */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Bell className="w-5 h-5" />
                                الإشعارات
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>إشعارات Push</Label>
                                    <p className="text-sm text-gray-500">إرسال إشعارات للمستخدمين</p>
                                </div>
                                <Switch
                                    checked={data.enable_push_notifications}
                                    onCheckedChange={(checked) => setData('enable_push_notifications', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Offline */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Wifi className="w-5 h-5" />
                                العمل دون اتصال
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <Label>وضع غير متصل</Label>
                                    <p className="text-sm text-gray-500">السماح بالعمل دون إنترنت</p>
                                </div>
                                <Switch
                                    checked={data.enable_offline_mode}
                                    onCheckedChange={(checked) => setData('enable_offline_mode', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Preview */}
                <Card>
                    <CardHeader>
                        <CardTitle>معاينة</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex justify-center">
                            <div className="w-72 border-8 border-gray-800 rounded-3xl overflow-hidden shadow-xl">
                                <div
                                    className="h-8"
                                    style={{ backgroundColor: data.theme_color }}
                                />
                                <div
                                    className="h-96 flex flex-col items-center justify-center"
                                    style={{ backgroundColor: data.background_color }}
                                >
                                    <div
                                        className="w-20 h-20 rounded-2xl flex items-center justify-center mb-4"
                                        style={{ backgroundColor: data.theme_color }}
                                    >
                                        <Smartphone className="w-10 h-10 text-white" />
                                    </div>
                                    <p className="font-bold text-lg">{data.app_name}</p>
                                    <p className="text-sm text-gray-500">{data.app_short_name}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

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
