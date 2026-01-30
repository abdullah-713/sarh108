import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Heart, TrendingUp, TrendingDown, AlertTriangle, Smile, Frown, Meh, Users, Building2, Play, RefreshCw, Calendar, UserCheck } from 'lucide-react';
import { useState } from 'react';

interface Summary {
    total_analyses: number;
    by_sentiment: Record<string, number>;
    average_score: number;
    concerning_count: number;
    requires_followup: number;
    overall_sentiment: string;
}

interface Analysis {
    id: number;
    employee_id: number;
    employee: { id: number; first_name: string; last_name: string };
    branch: { id: number; name: string } | null;
    sentiment: string;
    sentiment_name: string;
    sentiment_color: string;
    sentiment_emoji: string;
    sentiment_score: number;
    primary_emotion: string;
    primary_emotion_name: string;
    is_concerning: boolean;
    concerns_summary: string | null;
    requires_followup: boolean;
    followup_status: string | null;
    analysis_date: string;
}

interface BranchSentiment {
    branch_id: number;
    branch_name: string;
    average_score: number;
    count: number;
    concerning_count: number;
}

interface DepartmentSentiment {
    department_id: number;
    department_name: string;
    average_score: number;
    count: number;
}

interface TrendPoint {
    date: string;
    average_score: number;
    count: number;
    concerning_count: number;
}

interface Props {
    summary: Summary;
    concerningAnalyses: Analysis[];
    requiresFollowup: Analysis[];
    byBranch: BranchSentiment[];
    byDepartment: DepartmentSentiment[];
    trend: TrendPoint[];
}

const sentimentEmojis: Record<string, string> = {
    very_positive: '😊',
    positive: '🙂',
    neutral: '😐',
    negative: '😕',
    very_negative: '😞',
};

const sentimentColors: Record<string, string> = {
    very_positive: 'bg-green-100 text-green-800',
    positive: 'bg-emerald-100 text-emerald-800',
    neutral: 'bg-gray-100 text-gray-800',
    negative: 'bg-orange-100 text-orange-800',
    very_negative: 'bg-red-100 text-red-800',
};

const sentimentNames: Record<string, string> = {
    very_positive: 'إيجابي جداً',
    positive: 'إيجابي',
    neutral: 'محايد',
    negative: 'سلبي',
    very_negative: 'سلبي جداً',
};

export default function SentimentAnalysis({ summary, concerningAnalyses, requiresFollowup, byBranch, byDepartment, trend }: Props) {
    const [isRunning, setIsRunning] = useState(false);

    const handleRunAnalysis = async () => {
        setIsRunning(true);
        try {
            await fetch('/ai/sentiment/run', { method: 'POST' });
            router.reload();
        } catch (error) {
            console.error(error);
        } finally {
            setIsRunning(false);
        }
    };

    const getScoreColor = (score: number) => {
        if (score >= 70) return 'text-green-600';
        if (score >= 50) return 'text-yellow-600';
        if (score >= 30) return 'text-orange-600';
        return 'text-red-600';
    };

    const getOverallIcon = () => {
        if (summary.average_score >= 70) return <Smile className="w-12 h-12 text-green-500" />;
        if (summary.average_score >= 50) return <Meh className="w-12 h-12 text-yellow-500" />;
        return <Frown className="w-12 h-12 text-red-500" />;
    };

    return (
        <AppLayout>
            <Head title="تحليل المشاعر" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <Heart className="w-8 h-8 text-orange-500" />
                            تحليل المشاعر
                        </h1>
                        <p className="mt-1 text-gray-600">
                            تحليل رضا الموظفين ومؤشرات المشاعر
                        </p>
                    </div>

                    <Button
                        onClick={handleRunAnalysis}
                        disabled={isRunning}
                        className="bg-orange-500 hover:bg-orange-600"
                    >
                        {isRunning ? (
                            <RefreshCw className="w-4 h-4 ml-2 animate-spin" />
                        ) : (
                            <Play className="w-4 h-4 ml-2" />
                        )}
                        تشغيل التحليل
                    </Button>
                </div>

                {/* Overall Stats */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card className="md:col-span-2 bg-gradient-to-br from-orange-50 to-yellow-50">
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-600">المشاعر العامة</p>
                                    <p className="text-4xl font-bold mt-2">
                                        {sentimentEmojis[summary.overall_sentiment] || '😐'}
                                    </p>
                                    <p className={`text-2xl font-bold mt-1 ${getScoreColor(summary.average_score)}`}>
                                        {summary.average_score}%
                                    </p>
                                    <p className="text-sm text-gray-500">
                                        {sentimentNames[summary.overall_sentiment]}
                                    </p>
                                </div>
                                {getOverallIcon()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <Users className="w-8 h-8 mx-auto text-blue-500 mb-2" />
                                <p className="text-sm text-gray-500">إجمالي التحليلات</p>
                                <p className="text-2xl font-bold">{summary.total_analyses}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <AlertTriangle className="w-8 h-8 mx-auto text-orange-500 mb-2" />
                                <p className="text-sm text-gray-500">حالات مقلقة</p>
                                <p className="text-2xl font-bold text-orange-600">{summary.concerning_count}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <UserCheck className="w-8 h-8 mx-auto text-purple-500 mb-2" />
                                <p className="text-sm text-gray-500">تحتاج متابعة</p>
                                <p className="text-2xl font-bold text-purple-600">{summary.requires_followup}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Sentiment Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>توزيع المشاعر</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center gap-2 flex-wrap">
                            {Object.entries(summary.by_sentiment).map(([sentiment, count]) => (
                                <div key={sentiment} className="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50">
                                    <span className="text-2xl">{sentimentEmojis[sentiment]}</span>
                                    <div>
                                        <p className="text-sm font-medium">{sentimentNames[sentiment]}</p>
                                        <p className="text-lg font-bold">{count}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Concerning Analyses */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertTriangle className="w-5 h-5 text-orange-500" />
                                الحالات المقلقة
                            </CardTitle>
                            <CardDescription>الموظفون الذين يحتاجون اهتماماً</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {concerningAnalyses.length === 0 ? (
                                <div className="text-center py-8 text-gray-500">
                                    <Smile className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                    <p>لا توجد حالات مقلقة 🎉</p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {concerningAnalyses.map((analysis) => (
                                        <div
                                            key={analysis.id}
                                            className="flex items-start gap-3 p-3 rounded-lg bg-red-50 border border-red-100"
                                        >
                                            <span className="text-2xl">{analysis.sentiment_emoji}</span>
                                            <div className="flex-1">
                                                <div className="flex items-center gap-2">
                                                    <span className="font-medium">
                                                        {analysis.employee.first_name} {analysis.employee.last_name}
                                                    </span>
                                                    <Badge className={sentimentColors[analysis.sentiment]}>
                                                        {analysis.sentiment_score}%
                                                    </Badge>
                                                </div>
                                                {analysis.concerns_summary && (
                                                    <p className="text-sm text-gray-600 mt-1">
                                                        {analysis.concerns_summary}
                                                    </p>
                                                )}
                                                <p className="text-xs text-gray-400 mt-1">
                                                    {new Date(analysis.analysis_date).toLocaleDateString('ar-SA')}
                                                    {analysis.branch && ` • ${analysis.branch.name}`}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Requires Followup */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="w-5 h-5 text-purple-500" />
                                تحتاج متابعة
                            </CardTitle>
                            <CardDescription>الحالات المطلوب متابعتها</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {requiresFollowup.length === 0 ? (
                                <div className="text-center py-8 text-gray-500">
                                    <UserCheck className="w-12 h-12 mx-auto mb-4 opacity-30" />
                                    <p>لا توجد حالات تحتاج متابعة</p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {requiresFollowup.map((analysis) => (
                                        <div
                                            key={analysis.id}
                                            className="flex items-center justify-between p-3 rounded-lg bg-purple-50 border border-purple-100"
                                        >
                                            <div className="flex items-center gap-3">
                                                <span className="text-xl">{analysis.sentiment_emoji}</span>
                                                <div>
                                                    <span className="font-medium">
                                                        {analysis.employee.first_name} {analysis.employee.last_name}
                                                    </span>
                                                    <p className="text-xs text-gray-500">
                                                        {analysis.primary_emotion_name}
                                                    </p>
                                                </div>
                                            </div>
                                            <Badge variant="outline" className="border-purple-300 text-purple-600">
                                                {analysis.followup_status === 'pending' ? 'بانتظار' : 'جاري'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* By Branch & Department */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* By Branch */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building2 className="w-5 h-5 text-blue-500" />
                                حسب الفرع
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {byBranch.length === 0 ? (
                                <p className="text-center text-gray-500 py-4">لا توجد بيانات</p>
                            ) : (
                                <div className="space-y-3">
                                    {byBranch.map((branch) => (
                                        <div key={branch.branch_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                                            <div>
                                                <p className="font-medium">{branch.branch_name}</p>
                                                <p className="text-xs text-gray-500">{branch.count} تحليل</p>
                                            </div>
                                            <div className="text-left">
                                                <p className={`text-xl font-bold ${getScoreColor(branch.average_score)}`}>
                                                    {branch.average_score}%
                                                </p>
                                                {branch.concerning_count > 0 && (
                                                    <p className="text-xs text-red-500">{branch.concerning_count} مقلق</p>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* By Department */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="w-5 h-5 text-green-500" />
                                حسب القسم
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {byDepartment.length === 0 ? (
                                <p className="text-center text-gray-500 py-4">لا توجد بيانات</p>
                            ) : (
                                <div className="space-y-3">
                                    {byDepartment.map((dept) => (
                                        <div key={dept.department_id} className="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                                            <div>
                                                <p className="font-medium">{dept.department_name}</p>
                                                <p className="text-xs text-gray-500">{dept.count} تحليل</p>
                                            </div>
                                            <p className={`text-xl font-bold ${getScoreColor(dept.average_score)}`}>
                                                {dept.average_score}%
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Trend */}
                {trend.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="w-5 h-5 text-orange-500" />
                                اتجاه المشاعر (آخر 30 يوم)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-end gap-1 h-40">
                                {trend.map((point, index) => (
                                    <div
                                        key={index}
                                        className="flex-1 bg-orange-200 hover:bg-orange-300 rounded-t transition-colors relative group"
                                        style={{ height: `${point.average_score}%` }}
                                        title={`${point.date}: ${point.average_score}%`}
                                    >
                                        <div className="absolute bottom-full mb-1 right-1/2 transform translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                                            {point.average_score}%
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
