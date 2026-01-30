import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Trophy, TrendingUp, TrendingDown, Minus, Crown, Medal, Award, Star, Building2, RefreshCw, Calendar } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';

interface BranchRanking {
    id: number;
    branch_id: number;
    branch_name: string;
    performance_score: number;
    rank: number;
    previous_rank: number | null;
    rank_change: number;
    attendance_rate: number;
    on_time_rate: number;
    early_arrival_rate: number;
    late_rate: number;
    absence_rate: number;
    total_employees: number;
    present_count: number;
    streak_days: number;
}

interface Props {
    rankings: BranchRanking[];
    branches: { id: number; name: string }[];
    selectedDate: string;
    period: string;
}

export default function BranchRanking({ rankings, branches, selectedDate, period }: Props) {
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [selectedPeriod, setSelectedPeriod] = useState(period);
    const [date, setDate] = useState(selectedDate);

    const getRankIcon = (rank: number) => {
        switch (rank) {
            case 1:
                return <Crown className="w-6 h-6 text-yellow-500" />;
            case 2:
                return <Medal className="w-6 h-6 text-gray-400" />;
            case 3:
                return <Award className="w-6 h-6 text-orange-600" />;
            default:
                return <span className="w-6 h-6 text-center font-bold text-gray-500">{rank}</span>;
        }
    };

    const getRankChangeIcon = (change: number) => {
        if (change > 0) return <TrendingUp className="w-4 h-4 text-green-500" />;
        if (change < 0) return <TrendingDown className="w-4 h-4 text-red-500" />;
        return <Minus className="w-4 h-4 text-gray-400" />;
    };

    const getRankChangeText = (change: number) => {
        if (change > 0) return `↑ ${change}`;
        if (change < 0) return `↓ ${Math.abs(change)}`;
        return '-';
    };

    const getScoreColor = (score: number) => {
        if (score >= 90) return 'text-green-600 bg-green-50';
        if (score >= 75) return 'text-blue-600 bg-blue-50';
        if (score >= 60) return 'text-yellow-600 bg-yellow-50';
        return 'text-red-600 bg-red-50';
    };

    const handleRefresh = () => {
        setIsRefreshing(true);
        router.post('/reports/branch-ranking/recalculate', { date }, {
            onFinish: () => setIsRefreshing(false),
        });
    };

    const handlePeriodChange = (value: string) => {
        setSelectedPeriod(value);
        router.get('/reports/branch-ranking', { period: value, date }, { preserveState: true });
    };

    return (
        <AppLayout>
            <Head title="ترتيب الفروع" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Trophy className="w-8 h-8 text-orange-500" />
                            ترتيب الفروع
                        </h1>
                        <p className="mt-1 text-gray-600">
                            تصنيف الفروع حسب الأداء والالتزام بالحضور
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Select value={selectedPeriod} onValueChange={handlePeriodChange}>
                            <SelectTrigger className="w-[150px]">
                                <Calendar className="w-4 h-4 ml-2" />
                                <SelectValue placeholder="الفترة" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="today">اليوم</SelectItem>
                                <SelectItem value="week">هذا الأسبوع</SelectItem>
                                <SelectItem value="month">هذا الشهر</SelectItem>
                            </SelectContent>
                        </Select>

                        <Button
                            variant="outline"
                            onClick={handleRefresh}
                            disabled={isRefreshing}
                        >
                            <RefreshCw className={`w-4 h-4 ml-2 ${isRefreshing ? 'animate-spin' : ''}`} />
                            تحديث
                        </Button>
                    </div>
                </div>

                {/* Top 3 Podium */}
                {rankings.length >= 3 && (
                    <div className="grid grid-cols-3 gap-4 mb-8">
                        {/* Second Place */}
                        <Card className="relative overflow-hidden border-gray-200 mt-8">
                            <div className="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-gray-300 to-gray-400" />
                            <CardContent className="pt-6 text-center">
                                <div className="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <Medal className="w-10 h-10 text-gray-400" />
                                </div>
                                <h3 className="font-bold text-lg">{rankings[1]?.branch_name}</h3>
                                <p className="text-4xl font-bold text-gray-600 mt-2">
                                    {rankings[1]?.performance_score?.toFixed(1)}
                                </p>
                                <Badge variant="secondary" className="mt-2">المركز الثاني</Badge>
                            </CardContent>
                        </Card>

                        {/* First Place */}
                        <Card className="relative overflow-hidden border-orange-200 shadow-lg transform scale-105">
                            <div className="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-yellow-400 to-orange-500" />
                            <CardContent className="pt-6 text-center">
                                <div className="w-20 h-20 mx-auto bg-yellow-50 rounded-full flex items-center justify-center mb-4 ring-4 ring-yellow-200">
                                    <Crown className="w-12 h-12 text-yellow-500" />
                                </div>
                                <h3 className="font-bold text-xl">{rankings[0]?.branch_name}</h3>
                                <p className="text-5xl font-bold text-orange-500 mt-2">
                                    {rankings[0]?.performance_score?.toFixed(1)}
                                </p>
                                <Badge className="mt-2 bg-orange-500">البطل 🏆</Badge>
                                {rankings[0]?.streak_days > 1 && (
                                    <p className="text-sm text-orange-600 mt-2">
                                        🔥 سلسلة {rankings[0].streak_days} يوم
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Third Place */}
                        <Card className="relative overflow-hidden border-orange-100 mt-8">
                            <div className="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-orange-400 to-orange-600" />
                            <CardContent className="pt-6 text-center">
                                <div className="w-16 h-16 mx-auto bg-orange-50 rounded-full flex items-center justify-center mb-4">
                                    <Award className="w-10 h-10 text-orange-600" />
                                </div>
                                <h3 className="font-bold text-lg">{rankings[2]?.branch_name}</h3>
                                <p className="text-4xl font-bold text-orange-600 mt-2">
                                    {rankings[2]?.performance_score?.toFixed(1)}
                                </p>
                                <Badge variant="outline" className="mt-2 border-orange-300 text-orange-600">المركز الثالث</Badge>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Full Rankings Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Building2 className="w-5 h-5" />
                            جميع الفروع
                        </CardTitle>
                        <CardDescription>
                            ترتيب جميع الفروع حسب نقاط الأداء
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {rankings.length === 0 ? (
                            <div className="text-center py-12 text-gray-500">
                                <Trophy className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                <p>لا توجد بيانات للعرض</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b text-right">
                                            <th className="p-3 font-semibold text-gray-600">الترتيب</th>
                                            <th className="p-3 font-semibold text-gray-600">الفرع</th>
                                            <th className="p-3 font-semibold text-gray-600">النقاط</th>
                                            <th className="p-3 font-semibold text-gray-600">التغيير</th>
                                            <th className="p-3 font-semibold text-gray-600">الحضور</th>
                                            <th className="p-3 font-semibold text-gray-600">في الوقت</th>
                                            <th className="p-3 font-semibold text-gray-600">التأخير</th>
                                            <th className="p-3 font-semibold text-gray-600">الموظفين</th>
                                            <th className="p-3 font-semibold text-gray-600">السلسلة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rankings.map((branch, index) => (
                                            <tr
                                                key={branch.id}
                                                className={`border-b hover:bg-gray-50 transition-colors ${
                                                    index < 3 ? 'bg-orange-50/30' : ''
                                                }`}
                                            >
                                                <td className="p-3">
                                                    <div className="flex items-center gap-2">
                                                        {getRankIcon(branch.rank)}
                                                    </div>
                                                </td>
                                                <td className="p-3 font-medium">{branch.branch_name}</td>
                                                <td className="p-3">
                                                    <span className={`px-3 py-1 rounded-full font-bold ${getScoreColor(branch.performance_score)}`}>
                                                        {branch.performance_score?.toFixed(1)}
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    <div className="flex items-center gap-1">
                                                        {getRankChangeIcon(branch.rank_change)}
                                                        <span className={
                                                            branch.rank_change > 0 
                                                                ? 'text-green-600' 
                                                                : branch.rank_change < 0 
                                                                    ? 'text-red-600' 
                                                                    : 'text-gray-400'
                                                        }>
                                                            {getRankChangeText(branch.rank_change)}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-green-600 font-medium">
                                                        {branch.attendance_rate?.toFixed(1)}%
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-blue-600 font-medium">
                                                        {branch.on_time_rate?.toFixed(1)}%
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-red-600 font-medium">
                                                        {branch.late_rate?.toFixed(1)}%
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-gray-600">
                                                        {branch.present_count}/{branch.total_employees}
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    {branch.streak_days > 0 ? (
                                                        <Badge variant="outline" className="border-orange-300 text-orange-600">
                                                            🔥 {branch.streak_days} يوم
                                                        </Badge>
                                                    ) : (
                                                        <span className="text-gray-400">-</span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <Star className="w-8 h-8 mx-auto text-yellow-500 mb-2" />
                                <p className="text-2xl font-bold">
                                    {rankings.length > 0 ? rankings[0]?.branch_name : '-'}
                                </p>
                                <p className="text-sm text-gray-500">أفضل فرع</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <TrendingUp className="w-8 h-8 mx-auto text-green-500 mb-2" />
                                <p className="text-2xl font-bold">
                                    {rankings.filter(r => r.rank_change > 0).length}
                                </p>
                                <p className="text-sm text-gray-500">فروع متحسنة</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <Trophy className="w-8 h-8 mx-auto text-orange-500 mb-2" />
                                <p className="text-2xl font-bold">
                                    {rankings.length > 0 
                                        ? (rankings.reduce((sum, r) => sum + r.performance_score, 0) / rankings.length).toFixed(1) 
                                        : 0}
                                </p>
                                <p className="text-sm text-gray-500">متوسط النقاط</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <Building2 className="w-8 h-8 mx-auto text-blue-500 mb-2" />
                                <p className="text-2xl font-bold">{rankings.length}</p>
                                <p className="text-sm text-gray-500">إجمالي الفروع</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
