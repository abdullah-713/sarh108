import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Clock, MapPin, AlertCircle, CheckCircle } from 'lucide-react';

interface AttendanceStatus {
    status: string;
    attendance?: any;
    current_break?: any;
}

export default function EmployeeAttendance() {
    const [loading, setLoading] = useState(false);
    const [status, setStatus] = useState<AttendanceStatus | null>(null);
    const [currentTime, setCurrentTime] = useState(new Date());
    const [location, setLocation] = useState<GeolocationCoordinates | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [onBreak, setOnBreak] = useState(false);
    const [breakStartTime, setBreakStartTime] = useState<Date | null>(null);

    // Update time every second
    useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date());
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // Get geolocation
    useEffect(() => {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    setLocation(position.coords);
                    setError(null);
                },
                (error) => {
                    setError(`Location error: ${error.message}`);
                }
            );
        }
    }, []);

    // Fetch current attendance status
    useEffect(() => {
        const fetchStatus = async () => {
            try {
                const response = await axios.get('/api/v1/attendance/current-status', {
                    params: {
                        employee_id: (window as any).employeeId,
                    },
                });
                setStatus(response.data.data);
            } catch (err) {
                console.error('Failed to fetch status:', err);
            }
        };

        fetchStatus();
        const interval = setInterval(fetchStatus, 30000); // Fetch every 30 seconds
        return () => clearInterval(interval);
    }, []);

    const handleCheckIn = async () => {
        if (!location) {
            setError('Location not available');
            return;
        }

        setLoading(true);
        try {
            const response = await axios.post('/api/v1/attendance/check-in', {
                employee_id: (window as any).employeeId,
                latitude: location.latitude,
                longitude: location.longitude,
                location_name: 'Mobile Check-in',
            });

            if (response.data.success) {
                setStatus({
                    status: 'checked_in',
                    attendance: response.data.data.attendance,
                });
                setError(null);
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Check-in failed');
        } finally {
            setLoading(false);
        }
    };

    const handleCheckOut = async () => {
        if (!location) {
            setError('Location not available');
            return;
        }

        setLoading(true);
        try {
            const response = await axios.post('/api/v1/attendance/check-out', {
                employee_id: (window as any).employeeId,
                latitude: location.latitude,
                longitude: location.longitude,
                location_name: 'Mobile Check-out',
            });

            if (response.data.success) {
                setStatus({
                    status: 'checked_out',
                    attendance: response.data.data.attendance,
                });
                setError(null);
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Check-out failed');
        } finally {
            setLoading(false);
        }
    };

    const handleStartBreak = async (breakType: string) => {
        setLoading(true);
        try {
            const response = await axios.post('/api/v1/attendance/break/start', {
                employee_id: (window as any).employeeId,
                break_type: breakType,
            });

            if (response.data.success) {
                setOnBreak(true);
                setBreakStartTime(new Date());
                setError(null);
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to start break');
        } finally {
            setLoading(false);
        }
    };

    const handleEndBreak = async () => {
        if (!status?.current_break) return;

        setLoading(true);
        try {
            const response = await axios.post('/api/v1/attendance/break/end', {
                break_id: status.current_break.id,
            });

            if (response.data.success) {
                setOnBreak(false);
                setBreakStartTime(null);
                setStatus(prev => prev ? { ...prev, current_break: null } : null);
                setError(null);
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to end break');
        } finally {
            setLoading(false);
        }
    };

    const formatTime = (date: Date) => {
        return date.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
    };

    const getStatusColor = () => {
        switch (status?.status) {
            case 'checked_in':
                return 'bg-green-100 border-green-300';
            case 'on_break':
                return 'bg-yellow-100 border-yellow-300';
            case 'checked_out':
                return 'bg-gray-100 border-gray-300';
            default:
                return 'bg-blue-100 border-blue-300';
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4" dir="rtl">
            <div className="max-w-md mx-auto">
                {/* Header */}
                <div className="text-center mb-8 pt-8">
                    <h1 className="text-3xl font-bold text-gray-800 mb-2">تسجيل الحضور</h1>
                    <p className="text-gray-600">Employee Check-in System</p>
                </div>

                {/* Time Display */}
                <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div className="text-center">
                        <p className="text-gray-600 mb-2">الوقت الحالي</p>
                        <p className="text-4xl font-bold text-blue-600 font-mono">
                            {formatTime(currentTime)}
                        </p>
                        <p className="text-sm text-gray-500 mt-2">
                            {currentTime.toLocaleDateString('ar-SA')}
                        </p>
                    </div>
                </div>

                {/* Status Card */}
                {status && (
                    <div className={`rounded-lg border-2 p-6 mb-6 ${getStatusColor()}`}>
                        <div className="flex items-center justify-between mb-4">
                            {status.status === 'checked_in' ? (
                                <>
                                    <CheckCircle className="text-green-600" size={24} />
                                    <span className="text-green-800 font-semibold">مسجل الحضور</span>
                                </>
                            ) : status.status === 'on_break' ? (
                                <>
                                    <AlertCircle className="text-yellow-600" size={24} />
                                    <span className="text-yellow-800 font-semibold">في استراحة</span>
                                </>
                            ) : (
                                <>
                                    <Clock className="text-gray-600" size={24} />
                                    <span className="text-gray-800 font-semibold">منصرف</span>
                                </>
                            )}
                        </div>

                        {status.attendance?.check_in_time && (
                            <div className="text-sm text-gray-700">
                                <p>وقت الحضور: {new Date(status.attendance.check_in_time).toLocaleTimeString('ar-SA')}</p>
                            </div>
                        )}

                        {status.attendance?.check_out_time && (
                            <div className="text-sm text-gray-700">
                                <p>وقت الانصراف: {new Date(status.attendance.check_out_time).toLocaleTimeString('ar-SA')}</p>
                            </div>
                        )}

                        {status.attendance?.total_hours && (
                            <div className="text-sm text-gray-700 mt-2">
                                <p>إجمالي الساعات: {status.attendance.total_hours.toFixed(2)}</p>
                            </div>
                        )}
                    </div>
                )}

                {/* Location Status */}
                {location && (
                    <div className="bg-white rounded-lg p-4 mb-6 flex items-center gap-2">
                        <MapPin className="text-blue-500" size={20} />
                        <div className="text-sm">
                            <p className="text-gray-600">تم تحديد الموقع</p>
                            <p className="text-gray-500 text-xs">
                                دقة: {Math.round(location.accuracy)}م
                            </p>
                        </div>
                    </div>
                )}

                {/* Error Alert */}
                {error && (
                    <div className="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-6">
                        <p className="text-sm">{error}</p>
                    </div>
                )}

                {/* Action Buttons */}
                <div className="space-y-3">
                    {(!status || status.status === 'not_checked_in') && (
                        <button
                            onClick={handleCheckIn}
                            disabled={loading || !location}
                            className="w-full bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded-lg transition"
                        >
                            {loading ? 'جاري...' : 'تسجيل الحضور'}
                        </button>
                    )}

                    {status?.status === 'checked_in' && (
                        <>
                            <button
                                onClick={handleCheckOut}
                                disabled={loading || !location}
                                className="w-full bg-red-500 hover:bg-red-600 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded-lg transition"
                            >
                                {loading ? 'جاري...' : 'تسجيل الانصراف'}
                            </button>

                            {!onBreak && (
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        onClick={() => handleStartBreak('lunch')}
                                        disabled={loading}
                                        className="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded-lg transition text-sm"
                                    >
                                        استراحة غداء
                                    </button>
                                    <button
                                        onClick={() => handleStartBreak('prayer')}
                                        disabled={loading}
                                        className="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded-lg transition text-sm"
                                    >
                                        استراحة صلاة
                                    </button>
                                </div>
                            )}

                            {onBreak && (
                                <button
                                    onClick={handleEndBreak}
                                    disabled={loading}
                                    className="w-full bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded-lg transition"
                                >
                                    {loading ? 'جاري...' : 'إنهاء الاستراحة'}
                                </button>
                            )}
                        </>
                    )}
                </div>

                {/* Info Footer */}
                <div className="mt-8 text-center text-xs text-gray-600">
                    <p>نظام تسجيل الحضور الذكي</p>
                    <p>Smart Attendance Tracking System</p>
                </div>
            </div>
        </div>
    );
}
