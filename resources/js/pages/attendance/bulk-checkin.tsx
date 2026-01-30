import React, { useState, useEffect } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Textarea } from '@/components/ui/textarea';
import { 
    Users, 
    CheckCircle, 
    XCircle, 
    Loader2, 
    Search,
    Clock,
    AlertTriangle,
    UserCheck,
    Building2
} from 'lucide-react';

interface Employee {
    id: number;
    name: string;
    employee_id: string;
    department?: { name: string };
    designation?: { name: string };
    has_checked_in: boolean;
    checkin_time?: string;
}

interface Branch {
    id: number;
    name: string;
}

interface PageProps {
    employees: Employee[];
    branches: Branch[];
    currentBranch?: Branch;
    currentTime: string;
}

export default function BulkCheckin() {
    const { employees, branches, currentBranch, currentTime } = usePage<{ props: PageProps }>().props as unknown as PageProps;
    
    const [selectedEmployees, setSelectedEmployees] = useState<number[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedBranch, setSelectedBranch] = useState<string>(currentBranch?.id?.toString() || 'all');
    const [notes, setNotes] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitResult, setSubmitResult] = useState<{ success: boolean; message: string } | null>(null);
    const [currentTimeState, setCurrentTimeState] = useState(currentTime);

    // تحديث الوقت
    useEffect(() => {
        const timer = setInterval(() => {
            const now = new Date();
            setCurrentTimeState(now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // تصفية الموظفين
    const filteredEmployees = employees.filter(emp => {
        const matchesSearch = emp.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                             emp.employee_id.toLowerCase().includes(searchQuery.toLowerCase());
        return matchesSearch;
    });

    // الموظفين الذين لم يسجلوا حضورهم
    const pendingEmployees = filteredEmployees.filter(emp => !emp.has_checked_in);

    // تحديد الكل
    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedEmployees(pendingEmployees.map(emp => emp.id));
        } else {
            setSelectedEmployees([]);
        }
    };

    // تحديد موظف واحد
    const handleSelectEmployee = (employeeId: number, checked: boolean) => {
        if (checked) {
            setSelectedEmployees([...selectedEmployees, employeeId]);
        } else {
            setSelectedEmployees(selectedEmployees.filter(id => id !== employeeId));
        }
    };

    // إرسال التسجيل الجماعي
    const handleBulkCheckin = () => {
        if (selectedEmployees.length === 0) {
            setSubmitResult({ success: false, message: 'الرجاء تحديد موظف واحد على الأقل' });
            return;
        }

        setIsSubmitting(true);
        setSubmitResult(null);

        router.post('/attendance/bulk-checkin', {
            employee_ids: selectedEmployees,
            notes: notes,
        }, {
            onSuccess: () => {
                setSubmitResult({ 
                    success: true, 
                    message: `تم تسجيل حضور ${selectedEmployees.length} موظف بنجاح!` 
                });
                setSelectedEmployees([]);
                setNotes('');
                setIsSubmitting(false);
            },
            onError: (errors) => {
                setSubmitResult({ 
                    success: false, 
                    message: Object.values(errors).flat().join(', ') 
                });
                setIsSubmitting(false);
            },
        });
    };

    // تغيير الفرع
    const handleBranchChange = (branchId: string) => {
        setSelectedBranch(branchId);
        setSelectedEmployees([]);
        router.get('/attendance/bulk-checkin', { branch_id: branchId === 'all' ? null : branchId }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout>
            <Head title="التسجيل الجماعي للحضور" />

            <div className="max-w-6xl mx-auto p-4 space-y-6">
                {/* الإحصائيات */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm text-gray-500">الوقت الحالي</div>
                                    <div className="text-2xl font-bold text-primary">{currentTimeState}</div>
                                </div>
                                <Clock className="w-10 h-10 text-primary opacity-20" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm text-gray-500">إجمالي الموظفين</div>
                                    <div className="text-2xl font-bold">{employees.length}</div>
                                </div>
                                <Users className="w-10 h-10 text-gray-200" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm text-gray-500">سجلوا الحضور</div>
                                    <div className="text-2xl font-bold text-green-600">
                                        {employees.filter(e => e.has_checked_in).length}
                                    </div>
                                </div>
                                <UserCheck className="w-10 h-10 text-green-200" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm text-gray-500">لم يسجلوا</div>
                                    <div className="text-2xl font-bold text-orange-600">
                                        {pendingEmployees.length}
                                    </div>
                                </div>
                                <AlertTriangle className="w-10 h-10 text-orange-200" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* بطاقة التسجيل */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-xl flex items-center gap-2">
                            <Users className="w-5 h-5 text-primary" />
                            التسجيل الجماعي للحضور
                        </CardTitle>
                        <CardDescription>
                            حدد الموظفين لتسجيل حضورهم دفعة واحدة
                        </CardDescription>
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
                        </div>

                        {/* رسائل النتيجة */}
                        {submitResult && (
                            <Alert variant={submitResult.success ? 'default' : 'destructive'} 
                                   className={submitResult.success ? 'border-green-500 bg-green-50' : ''}>
                                {submitResult.success ? (
                                    <CheckCircle className="w-4 h-4 text-green-500" />
                                ) : (
                                    <AlertTriangle className="w-4 h-4" />
                                )}
                                <AlertDescription className={submitResult.success ? 'text-green-700' : ''}>
                                    {submitResult.message}
                                </AlertDescription>
                            </Alert>
                        )}

                        {/* جدول الموظفين */}
                        <div className="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-12">
                                            <Checkbox
                                                checked={selectedEmployees.length === pendingEmployees.length && pendingEmployees.length > 0}
                                                onCheckedChange={handleSelectAll}
                                            />
                                        </TableHead>
                                        <TableHead>الموظف</TableHead>
                                        <TableHead>الرقم الوظيفي</TableHead>
                                        <TableHead>القسم</TableHead>
                                        <TableHead>الحالة</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredEmployees.map((employee) => (
                                        <TableRow 
                                            key={employee.id}
                                            className={employee.has_checked_in ? 'bg-gray-50' : ''}
                                        >
                                            <TableCell>
                                                <Checkbox
                                                    checked={selectedEmployees.includes(employee.id)}
                                                    onCheckedChange={(checked) => handleSelectEmployee(employee.id, checked as boolean)}
                                                    disabled={employee.has_checked_in}
                                                />
                                            </TableCell>
                                            <TableCell className="font-medium">{employee.name}</TableCell>
                                            <TableCell>{employee.employee_id}</TableCell>
                                            <TableCell>{employee.department?.name || '-'}</TableCell>
                                            <TableCell>
                                                {employee.has_checked_in ? (
                                                    <Badge variant="default" className="bg-green-500">
                                                        <CheckCircle className="w-3 h-3 ml-1" />
                                                        حاضر ({employee.checkin_time})
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="secondary">
                                                        <XCircle className="w-3 h-3 ml-1" />
                                                        لم يسجل
                                                    </Badge>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {filteredEmployees.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center py-8 text-gray-500">
                                                لا يوجد موظفين
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* ملاحظات وزر الإرسال */}
                        <div className="space-y-4 pt-4 border-t">
                            <div>
                                <label className="block text-sm font-medium mb-2">ملاحظات (اختياري)</label>
                                <Textarea
                                    value={notes}
                                    onChange={(e) => setNotes(e.target.value)}
                                    placeholder="أضف ملاحظات للتسجيل الجماعي..."
                                    rows={3}
                                />
                            </div>
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-500">
                                    تم تحديد <span className="font-bold text-primary">{selectedEmployees.length}</span> موظف
                                </div>
                                <Button
                                    size="lg"
                                    onClick={handleBulkCheckin}
                                    disabled={selectedEmployees.length === 0 || isSubmitting}
                                >
                                    {isSubmitting ? (
                                        <>
                                            <Loader2 className="w-4 h-4 animate-spin ml-2" />
                                            جاري التسجيل...
                                        </>
                                    ) : (
                                        <>
                                            <UserCheck className="w-4 h-4 ml-2" />
                                            تسجيل الحضور للموظفين المحددين
                                        </>
                                    )}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
