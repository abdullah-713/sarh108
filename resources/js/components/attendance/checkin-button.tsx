import React, { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Loader2, LogIn, LogOut, CheckCircle, AlertTriangle } from 'lucide-react';
import { router } from '@inertiajs/react';

interface CheckinButtonProps {
    type: 'checkin' | 'checkout';
    disabled?: boolean;
    location?: {
        latitude: number;
        longitude: number;
        accuracy: number;
    } | null;
    onSuccess?: () => void;
    onError?: (error: string) => void;
    size?: 'default' | 'lg' | 'xl';
    className?: string;
}

export function CheckinButton({
    type,
    disabled = false,
    location = null,
    onSuccess,
    onError,
    size = 'default',
    className = '',
}: CheckinButtonProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [status, setStatus] = useState<'idle' | 'success' | 'error'>('idle');

    const sizeClasses = {
        default: 'h-10 px-4',
        lg: 'h-14 px-6 text-lg',
        xl: 'h-20 px-8 text-xl',
    };

    const handleClick = useCallback(() => {
        if (!location) {
            onError?.('يرجى تحديد الموقع أولاً');
            return;
        }

        setIsSubmitting(true);
        setStatus('idle');

        router.post('/attendance/quick-checkin', {
            type,
            latitude: location.latitude,
            longitude: location.longitude,
            accuracy: location.accuracy,
        }, {
            onSuccess: () => {
                setStatus('success');
                setIsSubmitting(false);
                onSuccess?.();
            },
            onError: (errors) => {
                setStatus('error');
                setIsSubmitting(false);
                const errorMessage = Object.values(errors).flat().join(', ');
                onError?.(errorMessage);
            },
        });
    }, [type, location, onSuccess, onError]);

    const isCheckin = type === 'checkin';
    const Icon = isCheckin ? LogIn : LogOut;
    const label = isCheckin ? 'تسجيل الحضور' : 'تسجيل الانصراف';

    return (
        <Button
            size="lg"
            variant={isCheckin ? 'default' : 'secondary'}
            className={`${sizeClasses[size]} ${className} ${
                status === 'success' ? 'bg-green-500 hover:bg-green-600' : ''
            } ${
                status === 'error' ? 'bg-red-500 hover:bg-red-600' : ''
            }`}
            disabled={disabled || isSubmitting}
            onClick={handleClick}
        >
            {isSubmitting ? (
                <Loader2 className="w-6 h-6 animate-spin" />
            ) : status === 'success' ? (
                <>
                    <CheckCircle className="w-6 h-6 ml-2" />
                    تم بنجاح!
                </>
            ) : status === 'error' ? (
                <>
                    <AlertTriangle className="w-6 h-6 ml-2" />
                    حدث خطأ
                </>
            ) : (
                <>
                    <Icon className="w-6 h-6 ml-2" />
                    {label}
                </>
            )}
        </Button>
    );
}

// مكون أزرار الحضور والانصراف معاً
interface CheckinButtonsProps {
    canCheckin: boolean;
    canCheckout: boolean;
    location?: {
        latitude: number;
        longitude: number;
        accuracy: number;
    } | null;
    onSuccess?: (type: 'checkin' | 'checkout') => void;
    onError?: (error: string) => void;
}

export function CheckinButtons({
    canCheckin,
    canCheckout,
    location,
    onSuccess,
    onError,
}: CheckinButtonsProps) {
    return (
        <div className="grid grid-cols-2 gap-4">
            <CheckinButton
                type="checkin"
                disabled={!canCheckin || !location}
                location={location}
                onSuccess={() => onSuccess?.('checkin')}
                onError={onError}
                size="xl"
            />
            <CheckinButton
                type="checkout"
                disabled={!canCheckout || !location}
                location={location}
                onSuccess={() => onSuccess?.('checkout')}
                onError={onError}
                size="xl"
            />
        </div>
    );
}

export default CheckinButton;
