import React, { useState, useEffect, useCallback } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
    MapPin, 
    Wifi, 
    Clock, 
    CheckCircle, 
    XCircle, 
    Loader2, 
    RefreshCw,
    AlertTriangle,
    LogIn,
    LogOut
} from 'lucide-react';

interface PageProps {
    employee: {
        id: number;
        name: string;
        employee_id: string;
        branch?: {
            id: number;
            name: string;
            latitude: number | null;
            longitude: number | null;
            geofence_radius: number;
        };
    };
    todayCheckin?: {
        id: number;
        type: string;
        checked_at: string;
        is_verified: boolean;
        verification_method: string;
    };
    todayCheckout?: {
        id: number;
        type: string;
        checked_at: string;
    };
    currentTime: string;
    timeWindow?: {
        name: string;
        checkin_start: string;
        checkin_end: string;
        checkout_start: string;
        checkout_end: string;
        is_checkin_open: boolean;
        is_checkout_open: boolean;
    };
}

interface LocationState {
    status: 'idle' | 'loading' | 'success' | 'error';
    latitude: number | null;
    longitude: number | null;
    accuracy: number | null;
    error: string | null;
}

export default function QuickCheckin() {
    const { employee, todayCheckin, todayCheckout, currentTime, timeWindow } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [location, setLocation] = useState<LocationState>({
        status: 'idle',
        latitude: null,
        longitude: null,
        accuracy: null,
        error: null,
    });
    
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState<string | null>(null);
    const [submitSuccess, setSubmitSuccess] = useState<string | null>(null);
    const [currentTimeState, setCurrentTimeState] = useState(currentTime);
    const [distanceInfo, setDistanceInfo] = useState<{
        distance: number;
        is_within: boolean;
        distance_formatted: string;
    } | null>(null);

    // تحديث الوقت كل ثانية
    useEffect(() => {
        const timer = setInterval(() => {
            const now = new Date();
            setCurrentTimeState(now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // الحصول على الموقع
    const getLocation = useCallback(() => {
        setLocation(prev => ({ ...prev, status: 'loading', error: null }));

        if (!navigator.geolocation) {
            setLocation(prev => ({ ...prev, status: 'error', error: 'المتصفح لا يدعم تحديد الموقع' }));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                setLocation({
                    status: 'success',
                    latitude,
                    longitude,
                    accuracy,
                    error: null,
                });
                
                // حساب المسافة من الفرع
                if (employee.branch?.latitude && employee.branch?.longitude) {
                    const distance = calculateDistance(
                        latitude, longitude,
                        employee.branch.latitude, employee.branch.longitude
                    );
                    const isWithin = distance <= (employee.branch.geofence_radius || 100);
                    setDistanceInfo({
                        distance: Math.round(distance),
                        is_within: isWithin,
                        distance_formatted: distance < 1000 ? `${Math.round(distance)} متر` : `${(distance / 1000).toFixed(2)} كم`,
                    });
                }
            },
            (error) => {
                let errorMessage = 'حدث خطأ في تحديد الموقع';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'تم رفض إذن تحديد الموقع';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'معلومات الموقع غير متوفرة';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'انتهت مهلة طلب الموقع';
                        break;
                }
                setLocation(prev => ({ ...prev, status: 'error', error: errorMessage }));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    }, [employee.branch]);

    // حساب المسافة بين نقطتين (Haversine)
    const calculateDistance = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
        const R = 6371000; // نصف قطر الأرض بالمتر
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    };

    // تسجيل الحضور/الانصراف
    const handleCheckin = (type: 'checkin' | 'checkout') => {
        setIsSubmitting(true);
        setSubmitError(null);
        setSubmitSuccess(null);

        router.post('/attendance/quick-checkin', {
            type,
            latitude: location.latitude,
            longitude: location.longitude,
            accuracy: location.accuracy,
        }, {
            onSuccess: () => {
                setSubmitSuccess(type === 'checkin' ? 'تم تسجيل الحضور بنجاح!' : 'تم تسجيل الانصراف بنجاح!');
                setIsSubmitting(false);
            },
            onError: (errors) => {
                setSubmitError(Object.values(errors).flat().join(', '));
                setIsSubmitting(false);
            },
        });
    };

    // تحديد ما إذا كان يمكن تسجيل الحضور أو الانصراف
    const canCheckin = !todayCheckin && timeWindow?.is_checkin_open;
    const canCheckout = todayCheckin && !todayCheckout && timeWindow?.is_checkout_open;

    return (
        <AppLayout>
            <Head title="تسجيل الحضور السريع" />

            <div className="max-w-2xl mx-auto p-4 space-y-6">
                {/* بطاقة معلومات الموظف */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-xl flex items-center gap-2">
                            <Clock className="w-5 h-5 text-primary" />
                            تسجيل الحضور السريع
                        </CardTitle>
                        <CardDescription>
                            مرحباً {employee.name}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* الوقت الحالي */}
                        <div className="text-center py-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div className="text-4xl font-bold text-primary">{currentTimeState}</div>
                            <div className="text-sm text-gray-500 mt-1">
                                {new Date().toLocaleDateString('ar-SA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                            </div>
                        </div>

                        {/* معلومات الفرع */}
                        {employee.branch && (
                            <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <span className="text-gray-600 dark:text-gray-400">الفرع</span>
                                <Badge variant="outline">{employee.branch.name}</Badge>
                            </div>
                        )}

                        {/* النافذة الزمنية */}
                        {timeWindow && (
                            <div className="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                                <div className="font-medium">{timeWindow.name}</div>
                                <div className="grid grid-cols-2 gap-2 text-sm">
                                    <div className="flex items-center gap-1">
                                        <LogIn className="w-4 h-4 text-green-500" />
                                        <span>الحضور: {timeWindow.checkin_start} - {timeWindow.checkin_end}</span>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <LogOut className="w-4 h-4 text-red-500" />
                                        <span>الانصراف: {timeWindow.checkout_start} - {timeWindow.checkout_end}</span>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* حالة اليوم */}
                        <div className="grid grid-cols-2 gap-4">
                            <div className="p-3 border rounded-lg text-center">
                                <div className="text-sm text-gray-500">تسجيل الحضور</div>
                                {todayCheckin ? (
                                    <div className="flex items-center justify-center gap-1 mt-1">
                                        <CheckCircle className="w-4 h-4 text-green-500" />
                                        <span className="font-medium">{todayCheckin.checked_at}</span>
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-center gap-1 mt-1 text-gray-400">
                                        <XCircle className="w-4 h-4" />
                                        <span>لم يسجل</span>
                                    </div>
                                )}
                            </div>
                            <div className="p-3 border rounded-lg text-center">
                                <div className="text-sm text-gray-500">تسجيل الانصراف</div>
                                {todayCheckout ? (
                                    <div className="flex items-center justify-center gap-1 mt-1">
                                        <CheckCircle className="w-4 h-4 text-green-500" />
                                        <span className="font-medium">{todayCheckout.checked_at}</span>
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-center gap-1 mt-1 text-gray-400">
                                        <XCircle className="w-4 h-4" />
                                        <span>لم يسجل</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* بطاقة الموقع */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg flex items-center gap-2">
                            <MapPin className="w-5 h-5 text-primary" />
                            التحقق من الموقع
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <Button 
                            onClick={getLocation} 
                            variant="outline" 
                            className="w-full"
                            disabled={location.status === 'loading'}
                        >
                            {location.status === 'loading' ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin mr-2" />
                                    جاري تحديد الموقع...
                                </>
                            ) : (
                                <>
                                    <RefreshCw className="w-4 h-4 mr-2" />
                                    تحديد الموقع
                                </>
                            )}
                        </Button>

                        {location.status === 'error' && (
                            <Alert variant="destructive">
                                <AlertTriangle className="w-4 h-4" />
                                <AlertDescription>{location.error}</AlertDescription>
                            </Alert>
                        )}

                        {location.status === 'success' && distanceInfo && (
                            <div className={`p-4 rounded-lg border-2 ${distanceInfo.is_within ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'}`}>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        {distanceInfo.is_within ? (
                                            <CheckCircle className="w-5 h-5 text-green-500" />
                                        ) : (
                                            <XCircle className="w-5 h-5 text-red-500" />
                                        )}
                                        <span className={distanceInfo.is_within ? 'text-green-700' : 'text-red-700'}>
                                            {distanceInfo.is_within ? 'داخل النطاق المسموح' : 'خارج النطاق المسموح'}
                                        </span>
                                    </div>
                                    <Badge variant={distanceInfo.is_within ? 'default' : 'destructive'}>
                                        {distanceInfo.distance_formatted}
                                    </Badge>
                                </div>
                                {location.accuracy && (
                                    <div className="text-sm text-gray-500 mt-2">
                                        دقة الموقع: {Math.round(location.accuracy)} متر
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* رسائل النجاح والخطأ */}
                {submitSuccess && (
                    <Alert className="border-green-500 bg-green-50">
                        <CheckCircle className="w-4 h-4 text-green-500" />
                        <AlertDescription className="text-green-700">{submitSuccess}</AlertDescription>
                    </Alert>
                )}

                {submitError && (
                    <Alert variant="destructive">
                        <AlertTriangle className="w-4 h-4" />
                        <AlertDescription>{submitError}</AlertDescription>
                    </Alert>
                )}

                {/* أزرار التسجيل */}
                <div className="grid grid-cols-2 gap-4">
                    <Button
                        size="lg"
                        className="h-20 text-lg"
                        disabled={!canCheckin || location.status !== 'success' || isSubmitting}
                        onClick={() => handleCheckin('checkin')}
                    >
                        {isSubmitting ? (
                            <Loader2 className="w-6 h-6 animate-spin" />
                        ) : (
                            <>
                                <LogIn className="w-6 h-6 mr-2" />
                                تسجيل الحضور
                            </>
                        )}
                    </Button>
                    <Button
                        size="lg"
                        variant="secondary"
                        className="h-20 text-lg"
                        disabled={!canCheckout || location.status !== 'success' || isSubmitting}
                        onClick={() => handleCheckin('checkout')}
                    >
                        {isSubmitting ? (
                            <Loader2 className="w-6 h-6 animate-spin" />
                        ) : (
                            <>
                                <LogOut className="w-6 h-6 mr-2" />
                                تسجيل الانصراف
                            </>
                        )}
                    </Button>
                </div>

                {/* تنبيهات */}
                {!canCheckin && !todayCheckin && (
                    <Alert>
                        <AlertTriangle className="w-4 h-4" />
                        <AlertDescription>نافذة تسجيل الحضور مغلقة حالياً</AlertDescription>
                    </Alert>
                )}

                {todayCheckin && !canCheckout && !todayCheckout && (
                    <Alert>
                        <AlertTriangle className="w-4 h-4" />
                        <AlertDescription>نافذة تسجيل الانصراف مغلقة حالياً</AlertDescription>
                    </Alert>
                )}

                {todayCheckin && todayCheckout && (
                    <Alert className="border-green-500 bg-green-50">
                        <CheckCircle className="w-4 h-4 text-green-500" />
                        <AlertDescription className="text-green-700">
                            تم تسجيل الحضور والانصراف بنجاح لهذا اليوم!
                        </AlertDescription>
                    </Alert>
                )}
            </div>
        </AppLayout>
    );
}
