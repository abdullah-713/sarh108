import React, { useState, useEffect } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    Users, 
    CheckCircle, 
    XCircle, 
    Clock,
    Search,
    RefreshCw,
    AlertTriangle,
    UserCheck,
    Building2,
    Wifi,
    MapPin,
    Timer,
    Star
} from 'lucide-react';

interface Employee {
    id: number;
    name: string;
    employee_id: string;
    branch?: string;
    branch_id?: number;
    department?: string;
    designation?: string;
    avatar?: string;
    status: 'present' | 'late' | 'absent' | 'on_leave' | 'holiday';
    status_color: string;
    status_label: string;
    checkin_time?: string;
    checkout_time?: string;
    late_minutes: number;
    worked_minutes: number;
    is_verified: boolean;
    verification_method?: string;
    is_perfect_day: boolean;
}

interface Branch {
    id: number;
    name: string;
}

interface BranchStats {
    total_employees: number;
    present: number;
    late: number;
    absent: number;
    on_leave: number;
    total_late_minutes: number;
    attendance_rate: number;
    punctuality_rate: number;
}

interface PageProps {
    employees: Employee[];
    branches: Branch[];
    currentBranch?: Branch;
    stats: BranchStats;
    lastUpdated: string;
}

export default function LiveStatus() {
    const { employees, branches, currentBranch, stats, lastUpdated } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedBranch, setSelectedBranch] = useState<string>(currentBranch?.id?.toString() || 'all');
    const [selectedStatus, setSelectedStatus] = useState<string>('all');
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [lastUpdatedTime, setLastUpdatedTime] = useState(lastUpdated);

    // تصفية الموظفين
    const filteredEmployees = employees.filter(emp => {
        const matchesSearch = emp.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                             emp.employee_id.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesStatus = selectedStatus === 'all' || emp.status === selectedStatus;
        return matchesSearch && matchesStatus;
    });

    // تحديث البيانات
    const refreshData = () => {
        setIsRefreshing(true);
        router.reload({
            only: ['employees', 'stats', 'lastUpdated'],
            onSuccess: () => {
                setIsRefreshing(false);
                setLastUpdatedTime(new Date().toLocaleTimeString('ar-SA'));
            },
            onError: () => {
                setIsRefreshing(false);
            },
        });
    };

    // تحديث تلقائي كل 30 ثانية
    useEffect(() => {
        const timer = setInterval(refreshData, 30000);
        return () => clearInterval(timer);
    }, []);

    // تغيير الفرع
    const handleBranchChange = (branchId: string) => {
        setSelectedBranch(branchId);
        router.get('/attendance/live-status', { branch_id: branchId === 'all' ? null : branchId }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // الحصول على لون الحالة
    const getStatusBadgeVariant = (status: string) => {
        switch (status) {
            case 'present': return 'default';
            case 'late': return 'secondary';
            case 'absent': return 'destructive';
            case 'on_leave': return 'outline';
            default: return 'outline';
        }
    };

    // الحصول على أيقونة طريقة التحقق
    const getVerificationIcon = (method?: string) => {
        switch (method) {
            case 'gps': return <MapPin className="w-4 h-4 text-green-500" />;
            case 'wifi': return <Wifi className="w-4 h-4 text-blue-500" />;
            case 'manual': return <UserCheck className="w-4 h-4 text-orange-500" />;
            default: return null;
        }
    };

    return (
        <AppLayout>
            <Head title="الحالة الحية للموظفين" />

            <div className="max-w-7xl mx-auto p-4 space-y-6">
                {/* الإحصائيات */}
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <Users className="w-8 h-8 mx-auto text-gray-400 mb-2" />
                                <div className="text-2xl font-bold">{stats.total_employees}</div>
                                <div className="text-xs text-gray-500">إجمالي الموظفين</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <CheckCircle className="w-8 h-8 mx-auto text-green-500 mb-2" />
                                <div className="text-2xl font-bold text-green-600">{stats.present}</div>
                                <div className="text-xs text-gray-500">حاضرين</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <Timer className="w-8 h-8 mx-auto text-orange-500 mb-2" />
                                <div className="text-2xl font-bold text-orange-600">{stats.late}</div>
                                <div className="text-xs text-gray-500">متأخرين</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <XCircle className="w-8 h-8 mx-auto text-red-500 mb-2" />
                                <div className="text-2xl font-bold text-red-600">{stats.absent}</div>
                                <div className="text-xs text-gray-500">غائبين</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-3xl font-bold text-primary mb-1">{stats.attendance_rate}%</div>
                                <div className="text-xs text-gray-500">نسبة الحضور</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-3xl font-bold text-green-600 mb-1">{stats.punctuality_rate}%</div>
                                <div className="text-xs text-gray-500">نسبة الالتزام</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* جدول الموظفين */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-xl flex items-center gap-2">
                                    <Users className="w-5 h-5 text-primary" />
                                    الحالة الحية للموظفين
                                </CardTitle>
                                <CardDescription className="flex items-center gap-2 mt-1">
                                    <Clock className="w-4 h-4" />
                                    آخر تحديث: {lastUpdatedTime}
                                </CardDescription>
                            </div>
                            <Button 
                                variant="outline" 
                                size="sm"
                                onClick={refreshData}
                                disabled={isRefreshing}
                            >
                                <RefreshCw className={`w-4 h-4 ml-2 ${isRefreshing ? 'animate-spin' : ''}`} />
                                تحديث
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* أدوات التصفية */}
                        <div className="flex flex-col md:flex-row gap-4">
                            <div className="relative flex-1">
                                <Search className="absolute right-3 top-3 w-4 h-4 text-gray-400" />
                                <Input
                                    placeholder="البحث بالاسم أو الرقم الوظيفي..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="pr-10"
                                />
                            </div>
                            <Select value={selectedBranch} onValueChange={handleBranchChange}>
                                <SelectTrigger className="w-full md:w-48">
                                    <Building2 className="w-4 h-4 ml-2" />
                                    <SelectValue placeholder="اختر الفرع" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">جميع الفروع</SelectItem>
                                    {branches.map((branch) => (
                                        <SelectItem key={branch.id} value={branch.id.toString()}>
                                            {branch.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                <SelectTrigger className="w-full md:w-48">
                                    <SelectValue placeholder="الحالة" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">جميع الحالات</SelectItem>
                                    <SelectItem value="present">حاضر</SelectItem>
                                    <SelectItem value="late">متأخر</SelectItem>
                                    <SelectItem value="absent">غائب</SelectItem>
                                    <SelectItem value="on_leave">إجازة</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* الجدول */}
                        <div className="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>الموظف</TableHead>
                                        <TableHead>الفرع / القسم</TableHead>
                                        <TableHead>الحالة</TableHead>
                                        <TableHead>الحضور</TableHead>
                                        <TableHead>الانصراف</TableHead>
                                        <TableHead>التأخير</TableHead>
                                        <TableHead>التحقق</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredEmployees.map((employee) => (
                                        <TableRow key={employee.id}>
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        {employee.avatar ? (
                                                            <img 
                                                                src={employee.avatar} 
                                                                alt={employee.name}
                                                                className="w-10 h-10 rounded-full object-cover"
                                                            />
                                                        ) : (
                                                            <span className="text-gray-500 font-medium">
                                                                {employee.name.charAt(0)}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div>
                                                        <div className="font-medium flex items-center gap-1">
                                                            {employee.name}
                                                            {employee.is_perfect_day && (
                                                                <Star className="w-4 h-4 text-yellow-500 fill-yellow-500" />
                                                            )}
                                                        </div>
                                                        <div className="text-sm text-gray-500">{employee.employee_id}</div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    <div>{employee.branch}</div>
                                                    <div className="text-gray-500">{employee.department}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusBadgeVariant(employee.status)}>
                                                    {employee.status_label}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {employee.checkin_time ? (
                                                    <span className="text-green-600 font-medium">{employee.checkin_time}</span>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {employee.checkout_time ? (
                                                    <span className="text-red-600 font-medium">{employee.checkout_time}</span>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {employee.late_minutes > 0 ? (
                                                    <Badge variant="secondary" className="bg-orange-100 text-orange-700">
                                                        {employee.late_minutes} دقيقة
                                                    </Badge>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1">
                                                    {employee.is_verified ? (
                                                        <>
                                                            {getVerificationIcon(employee.verification_method)}
                                                            <CheckCircle className="w-4 h-4 text-green-500" />
                                                        </>
                                                    ) : (
                                                        <AlertTriangle className="w-4 h-4 text-orange-500" />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {filteredEmployees.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-8 text-gray-500">
                                                لا يوجد موظفين مطابقين للبحث
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* ملخص */}
                        <div className="text-sm text-gray-500 text-center">
                            عرض {filteredEmployees.length} من {employees.length} موظف
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
