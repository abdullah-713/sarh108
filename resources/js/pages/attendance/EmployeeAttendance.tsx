import React, { useState, useEffect } from 'react';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';
import { Clock, LogIn, LogOut, Coffee, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { BRAND_COLORS } from '@/config/brand-colors';

interface AttendanceRecord {
    id: number;
    check_in_time: string;
    check_out_time: string | null;
    status: 'present' | 'absent' | 'late' | 'early_leave';
    date: string;
}

interface BreakPeriod {
    id: number;
    start_time: string;
    end_time: string | null;
    duration: number;
}

export default function EmployeeAttendance() {
    const [currentTime, setCurrentTime] = useState(new Date());
    const [isCheckedIn, setIsCheckedIn] = useState(false);
    const [todayAttendance, setTodayAttendance] = useState<AttendanceRecord | null>(null);
    const [breaks, setBreaks] = useState<BreakPeriod[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const timer = setInterval(() => setCurrentTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    const handleCheckIn = async () => {
        setLoading(true);
        try {
            const response = await fetch(route('attendance.check-in'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) {
                setIsCheckedIn(true);
                // Reload attendance data
            }
        } catch (error) {
            console.error('Check-in failed:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCheckOut = async () => {
        setLoading(true);
        try {
            const response = await fetch(route('attendance.check-out'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) {
                setIsCheckedIn(false);
            }
        } catch (error) {
            console.error('Check-out failed:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleStartBreak = async () => {
        setLoading(true);
        try {
            const response = await fetch(route('attendance.start-break'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) {
                // Reload breaks data
            }
        } catch (error) {
            console.error('Break start failed:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 to-white dark:from-black-950 dark:to-black-900 p-4 md:p-8">
            <div className="max-w-4xl mx-auto space-y-6">
                {/* Header */}
                <div className="text-center mb-8">
                    <h1 className="text-3xl md:text-4xl font-bold text-black-900 dark:text-white mb-2">
                        الحضور والانصراف
                    </h1>
                    <p className="text-black-600 dark:text-black-300">
                        {format(currentTime, 'EEEE, d MMMM yyyy - HH:mm:ss', { locale: ar })}
                    </p>
                </div>

                {/* Main Clock Card */}
                <Card className="border-2 border-orange-200 dark:border-orange-700 bg-white dark:bg-black-900 shadow-lg">
                    <div className="p-8">
                        <div className="text-center mb-8">
                            <div className="text-6xl font-bold text-orange-600 dark:text-orange-500 mb-2">
                                {format(currentTime, 'HH:mm:ss')}
                            </div>
                            <div className="text-lg text-black-600 dark:text-black-300">
                                {format(currentTime, 'EEEE', { locale: ar })}
                            </div>
                        </div>

                        {/* Attendance Status */}
                        <div className="bg-orange-50 dark:bg-black-800 rounded-lg p-6 mb-6">
                            <div className="flex items-center justify-between mb-4">
                                <span className="text-black-700 dark:text-black-200 font-semibold">الحالة</span>
                                <span
                                    className={`px-4 py-2 rounded-full font-semibold ${
                                        isCheckedIn
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                    }`}
                                >
                                    {isCheckedIn ? 'حاضر' : 'غير حاضر'}
                                </span>
                            </div>

                            {todayAttendance && (
                                <>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="flex items-center gap-2">
                                            <LogIn className="w-5 h-5 text-orange-600" />
                                            <div>
                                                <p className="text-sm text-black-600 dark:text-black-400">وقت الحضور</p>
                                                <p className="font-semibold text-black-900 dark:text-white">
                                                    {format(new Date(todayAttendance.check_in_time), 'HH:mm')}
                                                </p>
                                            </div>
                                        </div>
                                        {todayAttendance.check_out_time && (
                                            <div className="flex items-center gap-2">
                                                <LogOut className="w-5 h-5 text-orange-600" />
                                                <div>
                                                    <p className="text-sm text-black-600 dark:text-black-400">وقت الانصراف</p>
                                                    <p className="font-semibold text-black-900 dark:text-white">
                                                        {format(new Date(todayAttendance.check_out_time), 'HH:mm')}
                                                    </p>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Action Buttons */}
                        <div className="flex flex-col md:flex-row gap-4 justify-center">
                            {!isCheckedIn ? (
                                <Button
                                    onClick={handleCheckIn}
                                    disabled={loading}
                                    className="bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 text-white px-8 py-3 rounded-lg font-semibold flex items-center justify-center gap-2"
                                >
                                    <LogIn className="w-5 h-5" />
                                    حضور
                                </Button>
                            ) : (
                                <>
                                    <Button
                                        onClick={handleStartBreak}
                                        disabled={loading}
                                        className="bg-gradient-to-r from-black-600 to-black-700 hover:from-black-700 hover:to-black-800 text-white px-8 py-3 rounded-lg font-semibold flex items-center justify-center gap-2"
                                    >
                                        <Coffee className="w-5 h-5" />
                                        بدء فترة الراحة
                                    </Button>
                                    <Button
                                        onClick={handleCheckOut}
                                        disabled={loading}
                                        className="bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 text-white px-8 py-3 rounded-lg font-semibold flex items-center justify-center gap-2"
                                    >
                                        <LogOut className="w-5 h-5" />
                                        انصراف
                                    </Button>
                                </>
                            )}
                        </div>
                    </div>
                </Card>

                {/* Today's Summary */}
                <Card className="border border-orange-200 dark:border-orange-700 bg-white dark:bg-black-900">
                    <div className="p-6">
                        <h2 className="text-xl font-bold text-black-900 dark:text-white mb-4 flex items-center gap-2">
                            <Clock className="w-5 h-5 text-orange-600" />
                            ملخص اليوم
                        </h2>

                        {breaks.length > 0 && (
                            <div className="space-y-2">
                                <h3 className="font-semibold text-black-800 dark:text-black-200">فترات الراحة</h3>
                                {breaks.map((breakPeriod) => (
                                    <div
                                        key={breakPeriod.id}
                                        className="bg-orange-50 dark:bg-black-800 p-3 rounded flex justify-between items-center"
                                    >
                                        <span className="text-black-700 dark:text-black-200">
                                            {format(new Date(breakPeriod.start_time), 'HH:mm')} -{' '}
                                            {breakPeriod.end_time ? format(new Date(breakPeriod.end_time), 'HH:mm') : 'جارية'}
                                        </span>
                                        <span className="text-sm text-orange-600 dark:text-orange-400 font-semibold">
                                            {breakPeriod.duration} دقيقة
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </Card>
            </div>
        </div>
    );
}
