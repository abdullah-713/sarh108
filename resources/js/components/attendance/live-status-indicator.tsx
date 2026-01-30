import React, { useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { 
    CheckCircle, 
    XCircle, 
    Clock,
    Loader2
} from 'lucide-react';

interface LiveStatusIndicatorProps {
    status: 'present' | 'late' | 'absent' | 'on_leave' | 'holiday' | 'checking';
    checkinTime?: string;
    lateMinutes?: number;
    size?: 'sm' | 'md' | 'lg';
    showLabel?: boolean;
    pulse?: boolean;
}

const statusConfig = {
    present: {
        color: 'bg-green-500',
        textColor: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-500',
        label: 'حاضر',
        icon: CheckCircle,
    },
    late: {
        color: 'bg-orange-500',
        textColor: 'text-orange-600',
        bgColor: 'bg-orange-50',
        borderColor: 'border-orange-500',
        label: 'متأخر',
        icon: Clock,
    },
    absent: {
        color: 'bg-red-500',
        textColor: 'text-red-600',
        bgColor: 'bg-red-50',
        borderColor: 'border-red-500',
        label: 'غائب',
        icon: XCircle,
    },
    on_leave: {
        color: 'bg-blue-500',
        textColor: 'text-blue-600',
        bgColor: 'bg-blue-50',
        borderColor: 'border-blue-500',
        label: 'إجازة',
        icon: Clock,
    },
    holiday: {
        color: 'bg-purple-500',
        textColor: 'text-purple-600',
        bgColor: 'bg-purple-50',
        borderColor: 'border-purple-500',
        label: 'عطلة',
        icon: CheckCircle,
    },
    checking: {
        color: 'bg-gray-400',
        textColor: 'text-gray-600',
        bgColor: 'bg-gray-50',
        borderColor: 'border-gray-400',
        label: 'جاري التحقق',
        icon: Loader2,
    },
};

const sizeConfig = {
    sm: {
        dot: 'w-2 h-2',
        icon: 'w-3 h-3',
        text: 'text-xs',
        badge: 'text-xs px-2 py-0.5',
    },
    md: {
        dot: 'w-3 h-3',
        icon: 'w-4 h-4',
        text: 'text-sm',
        badge: 'text-sm px-2.5 py-1',
    },
    lg: {
        dot: 'w-4 h-4',
        icon: 'w-5 h-5',
        text: 'text-base',
        badge: 'text-base px-3 py-1.5',
    },
};

export function LiveStatusIndicator({
    status,
    checkinTime,
    lateMinutes,
    size = 'md',
    showLabel = true,
    pulse = true,
}: LiveStatusIndicatorProps) {
    const config = statusConfig[status];
    const sizes = sizeConfig[size];
    const Icon = config.icon;

    return (
        <div className="inline-flex items-center gap-2">
            {/* نقطة الحالة */}
            <span className="relative flex">
                <span className={`${sizes.dot} rounded-full ${config.color}`}></span>
                {pulse && status === 'present' && (
                    <span className={`absolute ${sizes.dot} rounded-full ${config.color} animate-ping`}></span>
                )}
            </span>

            {/* الشارة */}
            {showLabel && (
                <Badge 
                    variant="outline" 
                    className={`${sizes.badge} ${config.bgColor} ${config.borderColor} ${config.textColor} border`}
                >
                    <Icon className={`${sizes.icon} ml-1 ${status === 'checking' ? 'animate-spin' : ''}`} />
                    <span>{config.label}</span>
                    {lateMinutes && lateMinutes > 0 && (
                        <span className="mr-1">({lateMinutes} د)</span>
                    )}
                </Badge>
            )}

            {/* وقت الحضور */}
            {checkinTime && (
                <span className={`${sizes.text} text-gray-500`}>
                    {checkinTime}
                </span>
            )}
        </div>
    );
}

// مكون عرض الحالة الحية للموظف
interface EmployeeLiveStatusProps {
    employeeId: number;
    employeeName: string;
    initialStatus?: 'present' | 'late' | 'absent' | 'on_leave' | 'holiday';
    initialCheckinTime?: string;
    refreshInterval?: number;
}

export function EmployeeLiveStatus({
    employeeId,
    employeeName,
    initialStatus = 'absent',
    initialCheckinTime,
    refreshInterval = 30000,
}: EmployeeLiveStatusProps) {
    const [status, setStatus] = useState(initialStatus);
    const [checkinTime, setCheckinTime] = useState(initialCheckinTime);
    const [isLoading, setIsLoading] = useState(false);

    // تحديث الحالة بشكل دوري
    useEffect(() => {
        const fetchStatus = async () => {
            setIsLoading(true);
            try {
                const response = await fetch(`/api/attendance/employee-status/${employeeId}`);
                if (response.ok) {
                    const data = await response.json();
                    setStatus(data.status);
                    setCheckinTime(data.checkin_time);
                }
            } catch (error) {
                console.error('Error fetching status:', error);
            } finally {
                setIsLoading(false);
            }
        };

        // لا نقوم بالتحديث التلقائي إذا كان refreshInterval = 0
        if (refreshInterval > 0) {
            const interval = setInterval(fetchStatus, refreshInterval);
            return () => clearInterval(interval);
        }
    }, [employeeId, refreshInterval]);

    return (
        <div className="flex items-center gap-3">
            <div className="flex-1">
                <div className="font-medium">{employeeName}</div>
            </div>
            <LiveStatusIndicator
                status={isLoading ? 'checking' : status}
                checkinTime={checkinTime}
            />
        </div>
    );
}

export default LiveStatusIndicator;
