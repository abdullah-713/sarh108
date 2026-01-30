import React, { useState, useEffect, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
    MapPin, 
    Wifi, 
    Loader2, 
    CheckCircle, 
    XCircle, 
    RefreshCw,
    AlertTriangle
} from 'lucide-react';

interface LocationVerifierProps {
    targetLat?: number | null;
    targetLng?: number | null;
    targetRadius?: number;
    onLocationVerified?: (location: {
        latitude: number;
        longitude: number;
        accuracy: number;
        isWithinRange: boolean;
        distance: number;
    }) => void;
    showDetails?: boolean;
    autoStart?: boolean;
}

export function LocationVerifier({
    targetLat,
    targetLng,
    targetRadius = 100,
    onLocationVerified,
    showDetails = true,
    autoStart = false,
}: LocationVerifierProps) {
    const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
    const [location, setLocation] = useState<{
        latitude: number;
        longitude: number;
        accuracy: number;
    } | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [distance, setDistance] = useState<number | null>(null);
    const [isWithinRange, setIsWithinRange] = useState<boolean | null>(null);

    // حساب المسافة بين نقطتين (Haversine)
    const calculateDistance = useCallback((lat1: number, lng1: number, lat2: number, lng2: number): number => {
        const R = 6371000; // نصف قطر الأرض بالمتر
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }, []);

    // الحصول على الموقع
    const getLocation = useCallback(() => {
        setStatus('loading');
        setError(null);

        if (!navigator.geolocation) {
            setStatus('error');
            setError('المتصفح لا يدعم تحديد الموقع');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                setLocation({ latitude, longitude, accuracy });
                setStatus('success');

                // حساب المسافة إذا كان الهدف متوفراً
                if (targetLat && targetLng) {
                    const dist = calculateDistance(latitude, longitude, targetLat, targetLng);
                    setDistance(dist);
                    const within = dist <= targetRadius;
                    setIsWithinRange(within);

                    // إرسال النتيجة
                    onLocationVerified?.({
                        latitude,
                        longitude,
                        accuracy,
                        isWithinRange: within,
                        distance: dist,
                    });
                }
            },
            (geoError) => {
                setStatus('error');
                switch (geoError.code) {
                    case geoError.PERMISSION_DENIED:
                        setError('تم رفض إذن تحديد الموقع');
                        break;
                    case geoError.POSITION_UNAVAILABLE:
                        setError('معلومات الموقع غير متوفرة');
                        break;
                    case geoError.TIMEOUT:
                        setError('انتهت مهلة طلب الموقع');
                        break;
                    default:
                        setError('حدث خطأ في تحديد الموقع');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    }, [targetLat, targetLng, targetRadius, calculateDistance, onLocationVerified]);

    // بدء تلقائي
    useEffect(() => {
        if (autoStart) {
            getLocation();
        }
    }, [autoStart, getLocation]);

    // تنسيق المسافة
    const formatDistance = (meters: number): string => {
        if (meters < 1000) {
            return `${Math.round(meters)} متر`;
        }
        return `${(meters / 1000).toFixed(2)} كم`;
    };

    return (
        <div className="space-y-4">
            {/* زر تحديد الموقع */}
            <Button
                onClick={getLocation}
                variant="outline"
                className="w-full"
                disabled={status === 'loading'}
            >
                {status === 'loading' ? (
                    <>
                        <Loader2 className="w-4 h-4 animate-spin ml-2" />
                        جاري تحديد الموقع...
                    </>
                ) : (
                    <>
                        <RefreshCw className="w-4 h-4 ml-2" />
                        تحديد الموقع
                    </>
                )}
            </Button>

            {/* عرض الخطأ */}
            {status === 'error' && (
                <Alert variant="destructive">
                    <AlertTriangle className="w-4 h-4" />
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            {/* عرض النتيجة */}
            {status === 'success' && showDetails && (
                <div className={`p-4 rounded-lg border-2 ${
                    isWithinRange === null 
                        ? 'border-gray-200 bg-gray-50' 
                        : isWithinRange 
                            ? 'border-green-500 bg-green-50' 
                            : 'border-red-500 bg-red-50'
                }`}>
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            {isWithinRange === null ? (
                                <MapPin className="w-5 h-5 text-gray-500" />
                            ) : isWithinRange ? (
                                <CheckCircle className="w-5 h-5 text-green-500" />
                            ) : (
                                <XCircle className="w-5 h-5 text-red-500" />
                            )}
                            <span className={
                                isWithinRange === null 
                                    ? 'text-gray-700' 
                                    : isWithinRange 
                                        ? 'text-green-700' 
                                        : 'text-red-700'
                            }>
                                {isWithinRange === null 
                                    ? 'تم تحديد الموقع' 
                                    : isWithinRange 
                                        ? 'داخل النطاق المسموح' 
                                        : 'خارج النطاق المسموح'}
                            </span>
                        </div>
                        {distance !== null && (
                            <Badge variant={isWithinRange ? 'default' : 'destructive'}>
                                {formatDistance(distance)}
                            </Badge>
                        )}
                    </div>
                    {location?.accuracy && (
                        <div className="text-sm text-gray-500 mt-2">
                            دقة الموقع: {Math.round(location.accuracy)} متر
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

export default LocationVerifier;
