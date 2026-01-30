import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Trophy, Crown, Medal, Award, Star, Flame, Target, Clock, Users, TrendingUp, Zap } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';

interface MVPRanking {
    id: number;
    employee_id: number;
    employee_name: string;
    department_name: string;
    branch_name: string;
    avatar_url: string | null;
    mvp_score: number;
    attendance_rate: number;
    on_time_rate: number;
    early_rate: number;
    current_streak: number;
    total_days: number;
    rank: number;
}

interface TopStreak {
    id: number;
    employee_name: string;
    current_streak: number;
    longest_streak: number;
    department_name: string;
}

interface BadgeLeader {
    id: number;
    employee_name: string;
    badge_count: number;
    total_points: number;
    department_name: string;
}

interface Props {
    rankings: MVPRanking[];
    topStreaks: TopStreak[];
    badgeLeaderboard: BadgeLeader[];
    period: string;
    periodLabel: string;
}

export default function MVPLeaderboard({ rankings, topStreaks, badgeLeaderboard, period, periodLabel }: Props) {
    const [selectedPeriod, setSelectedPeriod] = useState(period);

    const handlePeriodChange = (value: string) => {
        setSelectedPeriod(value);
        router.get('/hr/mvp', { period: value }, { preserveState: true });
    };

    const getRankDisplay = (rank: number) => {
        switch (rank) {
            case 1:
                return (
                    <div className="flex items-center justify-center w-10 h-10 bg-yellow-100 rounded-full">
                        <Crown className="w-6 h-6 text-yellow-500" />
                    </div>
                );
            case 2:
                return (
                    <div className="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full">
                        <Medal className="w-6 h-6 text-gray-400" />
                    </div>
                );
            case 3:
                return (
                    <div className="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-full">
                        <Award className="w-6 h-6 text-orange-600" />
                    </div>
                );
            default:
                return (
                    <div className="flex items-center justify-center w-10 h-10 bg-gray-50 rounded-full font-bold text-gray-600">
                        {rank}
                    </div>
                );
        }
    };

    const getScoreColor = (score: number) => {
        if (score >= 90) return 'from-green-500 to-emerald-600';
        if (score >= 75) return 'from-blue-500 to-indigo-600';
        if (score >= 60) return 'from-yellow-500 to-orange-600';
        return 'from-red-500 to-rose-600';
    };

    return (
        <AppLayout>
            <Head title="لوحة المتميزين MVP" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Trophy className="w-8 h-8 text-orange-500" />
                            لوحة المتميزين MVP
                        </h1>
                        <p className="mt-1 text-gray-600">
                            ترتيب الموظفين الأكثر تميزاً في الحضور والالتزام
                        </p>
                    </div>

                    <Select value={selectedPeriod} onValueChange={handlePeriodChange}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="الفترة" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="week">هذا الأسبوع</SelectItem>
                            <SelectItem value="month">هذا الشهر</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {/* Top 3 Showcase */}
                {rankings.length >= 3 && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {/* Second Place */}
                        <Card className="relative overflow-hidden border-gray-200 md:mt-8 order-2 md:order-1">
                            <div className="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-gray-300 to-gray-400" />
                            <CardContent className="pt-8 text-center">
                                <div className="relative w-20 h-20 mx-auto mb-4">
                                    <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                                        {rankings[1]?.avatar_url ? (
                                            <img src={rankings[1].avatar_url} alt="" className="w-full h-full object-cover" />
                                        ) : (
                                            <Users className="w-10 h-10 text-gray-400" />
                                        )}
                                    </div>
                                    <div className="absolute -bottom-1 -right-1 w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        2
                                    </div>
                                </div>
                                <h3 className="font-bold text-lg">{rankings[1]?.employee_name}</h3>
                                <p className="text-sm text-gray-500">{rankings[1]?.department_name}</p>
                                <div className="mt-3">
                                    <span className="text-3xl font-bold text-gray-600">
                                        {rankings[1]?.mvp_score?.toFixed(1)}
                                    </span>
                                    <span className="text-sm text-gray-400 mr-1">نقطة</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* First Place */}
                        <Card className="relative overflow-hidden border-orange-200 shadow-xl transform md:scale-110 order-1 md:order-2 z-10">
                            <div className="absolute top-0 right-0 left-0 h-3 bg-gradient-to-r from-yellow-400 via-orange-500 to-yellow-400" />
                            <CardContent className="pt-8 text-center">
                                <div className="absolute top-6 right-6">
                                    <Crown className="w-8 h-8 text-yellow-500 animate-pulse" />
                                </div>
                                <div className="relative w-24 h-24 mx-auto mb-4">
                                    <div className="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center overflow-hidden ring-4 ring-orange-300">
                                        {rankings[0]?.avatar_url ? (
                                            <img src={rankings[0].avatar_url} alt="" className="w-full h-full object-cover" />
                                        ) : (
                                            <Users className="w-12 h-12 text-orange-400" />
                                        )}
                                    </div>
                                    <div className="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold">
                                        1
                                    </div>
                                </div>
                                <Badge className="bg-gradient-to-r from-yellow-400 to-orange-500 text-white mb-2">
                                    ⭐ MVP {periodLabel}
                                </Badge>
                                <h3 className="font-bold text-xl">{rankings[0]?.employee_name}</h3>
                                <p className="text-sm text-gray-500">{rankings[0]?.department_name}</p>
                                <div className="mt-3">
                                    <span className="text-4xl font-bold bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
                                        {rankings[0]?.mvp_score?.toFixed(1)}
                                    </span>
                                    <span className="text-sm text-gray-400 mr-1">نقطة</span>
                                </div>
                                {rankings[0]?.current_streak > 1 && (
                                    <div className="mt-2 flex items-center justify-center text-orange-600">
                                        <Flame className="w-5 h-5 ml-1 animate-bounce" />
                                        <span>سلسلة {rankings[0].current_streak} يوم</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Third Place */}
                        <Card className="relative overflow-hidden border-orange-100 md:mt-8 order-3">
                            <div className="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-orange-400 to-orange-600" />
                            <CardContent className="pt-8 text-center">
                                <div className="relative w-20 h-20 mx-auto mb-4">
                                    <div className="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center overflow-hidden">
                                        {rankings[2]?.avatar_url ? (
                                            <img src={rankings[2].avatar_url} alt="" className="w-full h-full object-cover" />
                                        ) : (
                                            <Users className="w-10 h-10 text-orange-400" />
                                        )}
                                    </div>
                                    <div className="absolute -bottom-1 -right-1 w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        3
                                    </div>
                                </div>
                                <h3 className="font-bold text-lg">{rankings[2]?.employee_name}</h3>
                                <p className="text-sm text-gray-500">{rankings[2]?.department_name}</p>
                                <div className="mt-3">
                                    <span className="text-3xl font-bold text-orange-600">
                                        {rankings[2]?.mvp_score?.toFixed(1)}
                                    </span>
                                    <span className="text-sm text-gray-400 mr-1">نقطة</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Main Rankings Table */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* MVP Rankings */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="w-5 h-5 text-orange-500" />
                                ترتيب المتميزين
                            </CardTitle>
                            <CardDescription>الموظفون الأعلى تقييماً {periodLabel}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {rankings.length === 0 ? (
                                <div className="text-center py-12 text-gray-500">
                                    <Trophy className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                    <p>لا توجد بيانات للعرض</p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {rankings.slice(3).map((employee) => (
                                        <div
                                            key={employee.id}
                                            className="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            {getRankDisplay(employee.rank)}
                                            
                                            <div className="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                                                {employee.avatar_url ? (
                                                    <img src={employee.avatar_url} alt="" className="w-full h-full object-cover" />
                                                ) : (
                                                    <Users className="w-5 h-5 text-gray-400" />
                                                )}
                                            </div>

                                            <div className="flex-1">
                                                <p className="font-medium">{employee.employee_name}</p>
                                                <p className="text-sm text-gray-500">{employee.department_name}</p>
                                            </div>

                                            <div className="text-left">
                                                <div className={`px-3 py-1 rounded-full bg-gradient-to-r ${getScoreColor(employee.mvp_score)} text-white font-bold`}>
                                                    {employee.mvp_score?.toFixed(1)}
                                                </div>
                                            </div>

                                            <div className="hidden md:flex items-center gap-4 text-sm text-gray-500">
                                                <span title="نسبة الحضور">
                                                    <Clock className="w-4 h-4 inline ml-1" />
                                                    {employee.attendance_rate?.toFixed(0)}%
                                                </span>
                                                <span title="السلسلة">
                                                    <Flame className="w-4 h-4 inline ml-1" />
                                                    {employee.current_streak}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Side Panels */}
                    <div className="space-y-6">
                        {/* Top Streaks */}
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Flame className="w-5 h-5 text-orange-500" />
                                    أطول السلاسل
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {topStreaks.length === 0 ? (
                                        <p className="text-center text-gray-500 py-4">لا توجد سلاسل نشطة</p>
                                    ) : (
                                        topStreaks.map((streak, index) => (
                                            <div key={streak.id} className="flex items-center gap-3">
                                                <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                                                    index === 0 ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600'
                                                }`}>
                                                    {index + 1}
                                                </span>
                                                <div className="flex-1">
                                                    <p className="font-medium text-sm">{streak.employee_name}</p>
                                                    <p className="text-xs text-gray-500">{streak.department_name}</p>
                                                </div>
                                                <div className="text-left">
                                                    <Badge variant="outline" className="border-orange-300 text-orange-600">
                                                        🔥 {streak.current_streak} يوم
                                                    </Badge>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Badge Leaders */}
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Star className="w-5 h-5 text-yellow-500" />
                                    أصحاب الشارات
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {badgeLeaderboard.length === 0 ? (
                                        <p className="text-center text-gray-500 py-4">لا توجد شارات بعد</p>
                                    ) : (
                                        badgeLeaderboard.map((leader, index) => (
                                            <div key={leader.id} className="flex items-center gap-3">
                                                <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                                                    index === 0 ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600'
                                                }`}>
                                                    {index + 1}
                                                </span>
                                                <div className="flex-1">
                                                    <p className="font-medium text-sm">{leader.employee_name}</p>
                                                    <p className="text-xs text-gray-500">{leader.department_name}</p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Badge variant="secondary">
                                                        ⭐ {leader.badge_count}
                                                    </Badge>
                                                    <span className="text-xs text-gray-500">
                                                        {leader.total_points} نقطة
                                                    </span>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Stats */}
                        <Card className="bg-gradient-to-br from-orange-50 to-yellow-50">
                            <CardContent className="pt-6">
                                <div className="text-center">
                                    <Zap className="w-10 h-10 mx-auto text-orange-500 mb-2" />
                                    <p className="text-sm text-gray-600">متوسط نقاط الفريق</p>
                                    <p className="text-3xl font-bold text-orange-600 mt-1">
                                        {rankings.length > 0
                                            ? (rankings.reduce((sum, r) => sum + r.mvp_score, 0) / rankings.length).toFixed(1)
                                            : 0}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
