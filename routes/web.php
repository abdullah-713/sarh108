<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanOrderController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PayPalPaymentController;
use App\Http\Controllers\BankPaymentController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\FlutterwavePaymentController;
use App\Http\Controllers\PayTabsPaymentController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\CoinGatePaymentController;
use App\Http\Controllers\PayfastPaymentController;
use App\Http\Controllers\TapPaymentController;
use App\Http\Controllers\XenditPaymentController;
use App\Http\Controllers\PayTRPaymentController;
use App\Http\Controllers\MolliePaymentController;
use App\Http\Controllers\ToyyibPayPaymentController;
use App\Http\Controllers\CashfreeController;
use App\Http\Controllers\IyzipayPaymentController;
use App\Http\Controllers\BenefitPaymentController;
use App\Http\Controllers\OzowPaymentController;
use App\Http\Controllers\EasebuzzPaymentController;
use App\Http\Controllers\KhaltiPaymentController;
use App\Http\Controllers\AuthorizeNetPaymentController;
use App\Http\Controllers\FedaPayPaymentController;
use App\Http\Controllers\AwardTypeController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ResignationController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmployeeTransferController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TrainingTypeController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingAssessmentController;
use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\PayHerePaymentController;
use App\Http\Controllers\CinetPayPaymentController;
use App\Http\Controllers\PaiementPaymentController;
use App\Http\Controllers\NepalstePaymentController;
use App\Http\Controllers\YooKassaPaymentController;
use App\Http\Controllers\AamarpayPaymentController;
use App\Http\Controllers\MidtransPaymentController;
use App\Http\Controllers\PaymentWallPaymentController;
use App\Http\Controllers\SSPayPaymentController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CookieConsentController;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HrDocumentController;
use App\Http\Controllers\PerformanceIndicatorCategoryController;
use App\Http\Controllers\PerformanceIndicatorController;
use App\Http\Controllers\GoalTypeController;
use App\Http\Controllers\EmployeeGoalController;
use App\Http\Controllers\ReviewCycleController;
use App\Http\Controllers\EmployeeReviewController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

// Public form submission routes

// Cashfree webhook (public route)
Route::post('cashfree/webhook', [CashfreeController::class, 'webhook'])->name('cashfree.webhook');

// Benefit webhook (public route)
Route::post('benefit/webhook', [BenefitPaymentController::class, 'webhook'])->name('benefit.webhook');
Route::get('payments/benefit/success', [BenefitPaymentController::class, 'success'])->name('benefit.success');
Route::post('payments/benefit/callback', [BenefitPaymentController::class, 'callback'])->name('benefit.callback');

// FedaPay callback (public route)
Route::match(['GET', 'POST'], 'payments/fedapay/callback', [FedaPayPaymentController::class, 'callback'])->name('fedapay.callback');

// YooKassa success/callback (public routes)
Route::get('payments/yookassa/success', [YooKassaPaymentController::class, 'success'])->name('yookassa.success');
Route::post('payments/yookassa/callback', [YooKassaPaymentController::class, 'callback'])->name('yookassa.callback');

// Nepalste success/callback (public routes)
Route::get('payments/nepalste/success', [NepalstePaymentController::class, 'success'])->name('nepalste.success');
Route::post('payments/nepalste/callback', [NepalstePaymentController::class, 'callback'])->name('nepalste.callback');

// PayTR callback (public route)
Route::post('payments/paytr/callback', [PayTRPaymentController::class, 'callback'])->name('paytr.callback');

// PayTabs callback (public route)
Route::match(['GET', 'POST'], 'payments/paytabs/callback', [PayTabsPaymentController::class, 'callback'])->name('paytabs.callback');
Route::get('payments/paytabs/success', [PayTabsPaymentController::class, 'success'])->name('paytabs.success');

// Tap payment routes (public routes)
Route::get('payments/tap/success', [TapPaymentController::class, 'success'])->name('tap.success');
Route::post('payments/tap/callback', [TapPaymentController::class, 'callback'])->name('tap.callback');

// Aamarpay payment routes (public routes)
Route::match(['GET', 'POST'], 'payments/aamarpay/success', [AamarpayPaymentController::class, 'success'])->name('aamarpay.success');
Route::post('payments/aamarpay/callback', [AamarpayPaymentController::class, 'callback'])->name('aamarpay.callback');

// PaymentWall callback (public route)
Route::match(['GET', 'POST'], 'payments/paymentwall/callback', [PaymentWallPaymentController::class, 'callback'])->name('paymentwall.callback');
Route::get('payments/paymentwall/success', [PaymentWallPaymentController::class, 'success'])->name('paymentwall.success');

// PayFast payment routes (public routes)
Route::get('payments/payfast/success', [PayfastPaymentController::class, 'success'])->name('payfast.success');
Route::post('payments/payfast/callback', [PayfastPaymentController::class, 'callback'])->name('payfast.callback');

// CoinGate callback (public route)
Route::match(['GET', 'POST'], 'payments/coingate/callback', [CoinGatePaymentController::class, 'callback'])->name('coingate.callback');

// Xendit payment routes (public routes)
Route::get('payments/xendit/success', [XenditPaymentController::class, 'success'])->name('xendit.success');
Route::post('payments/xendit/callback', [XenditPaymentController::class, 'callback'])->name('xendit.callback');

// PWA Manifest routes removed

Route::get('/translations/{locale}', [TranslationController::class, 'getTranslations'])->name('translations');
Route::get('/refresh-language/{locale}', [TranslationController::class, 'refreshLanguage'])->name('refresh-language');
Route::get('/initial-locale', [TranslationController::class, 'getInitialLocale'])->name('initial-locale');


Route::middleware(['auth', 'verified', 'setting'])->group(function () {

    // All other routes require plan access check
    Route::middleware('plan.access')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/redirect', [DashboardController::class, 'redirectToFirstAvailablePage'])->name('dashboard.redirect');

        // Permissions routes with granular permissions
        Route::middleware('permission:manage-permissions')->group(function () {
            Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:manage-permissions')->name('permissions.index');
            Route::get('permissions/create', [PermissionController::class, 'create'])->middleware('permission:create-permissions')->name('permissions.create');
            Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:create-permissions')->name('permissions.store');
            Route::get('permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:view-permissions')->name('permissions.show');
            Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->middleware('permission:edit-permissions')->name('permissions.edit');
            Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:edit-permissions')->name('permissions.update');
            Route::patch('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:edit-permissions');
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:delete-permissions')->name('permissions.destroy');
        });

        // Roles routes with granular permissions
        Route::middleware('permission:manage-roles')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->middleware('permission:manage-roles')->name('roles.index');
            Route::get('roles/create', [RoleController::class, 'create'])->middleware('permission:create-roles')->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->middleware('permission:create-roles')->name('roles.store');
            Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:view-roles')->name('roles.show');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:edit-roles')->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:edit-roles')->name('roles.update');
            Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:edit-roles');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles')->name('roles.destroy');
        });

        // Users routes with granular permissions
        Route::middleware('permission:manage-users')->group(function () {
            Route::get('users', [UserController::class, 'index'])->middleware('permission:manage-users')->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->middleware('permission:create-users')->name('users.create');
            Route::post('users', [UserController::class, 'store'])->middleware('permission:create-users')->name('users.store');
            Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:view-users')->name('users.show');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:edit-users')->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:edit-users')->name('users.update');
            Route::patch('users/{user}', [UserController::class, 'update'])->middleware('permission:edit-users');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users')->name('users.destroy');

            // Additional user routes
            Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:reset-password-users')->name('users.reset-password');
            Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('permission:toggle-status-users')->name('users.toggle-status');
        });


        // HR Module routes
        // Branch routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('hr/branches', [BranchController::class, 'index'])->name('hr.branches.index');
            Route::post('hr/branches', [BranchController::class, 'store'])->middleware('permission:create-branches')->name('hr.branches.store');
            Route::put('hr/branches/{branch}', [BranchController::class, 'update'])->middleware('permission:edit-branches')->name('hr.branches.update');
            Route::delete('hr/branches/{branch}', [BranchController::class, 'destroy'])->middleware('permission:delete-branches')->name('hr.branches.destroy');
            Route::put('hr/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->middleware('permission:edit-branches')->name('hr.branches.toggle-status');
        });

        // Department routes
        Route::middleware('permission:manage-departments')->group(function () {
            Route::get('hr/departments', [DepartmentController::class, 'index'])->name('hr.departments.index');
            Route::post('hr/departments', [DepartmentController::class, 'store'])->middleware('permission:create-departments')->name('hr.departments.store');
            Route::put('hr/departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:edit-departments')->name('hr.departments.update');
            Route::delete('hr/departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:delete-departments')->name('hr.departments.destroy');
            Route::put('hr/departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->middleware('permission:edit-departments')->name('hr.departments.toggle-status');
        });





        // Designation routes
        Route::middleware('permission:manage-designations')->group(function () {
            Route::get('hr/designations', [\App\Http\Controllers\DesignationController::class, 'index'])->name('hr.designations.index');
            Route::post('hr/designations', [\App\Http\Controllers\DesignationController::class, 'store'])->middleware('permission:create-designations')->name('hr.designations.store');
            Route::put('hr/designations/{designation}', [\App\Http\Controllers\DesignationController::class, 'update'])->middleware('permission:edit-designations')->name('hr.designations.update');
            Route::delete('hr/designations/{designation}', [\App\Http\Controllers\DesignationController::class, 'destroy'])->middleware('permission:delete-designations')->name('hr.designations.destroy');
            Route::put('hr/designations/{designation}/toggle-status', [\App\Http\Controllers\DesignationController::class, 'toggleStatus'])->middleware('permission:toggle-status-designations')->name('hr.designations.toggle-status');
        });

        // Documenttype Routes
        Route::middleware('permission:manage-document-types')->group(function () {
            Route::get('hr/document-types', [\App\Http\Controllers\DocumentTypeController::class, 'index'])->name('hr.document-types.index');
            Route::post('hr/document-types', [\App\Http\Controllers\DocumentTypeController::class, 'store'])->middleware('permission:create-document-types')->name('hr.document-types.store');
            Route::put('hr/document-types/{documentType}', [\App\Http\Controllers\DocumentTypeController::class, 'update'])->middleware('permission:edit-document-types')->name('hr.document-types.update');
            Route::delete('hr/document-types/{documentType}', [\App\Http\Controllers\DocumentTypeController::class, 'destroy'])->middleware('permission:delete-document-types')->name('hr.document-types.destroy');
        });

        // Employee Routes
        Route::middleware('permission:manage-employees')->group(function () {
            Route::get('hr/employees', [EmployeeController::class, 'index'])->name('hr.employees.index');
            Route::get('hr/employees/create', [EmployeeController::class, 'create'])->middleware('permission:create-employees')->name('hr.employees.create');
            Route::post('hr/employees', [EmployeeController::class, 'store'])->middleware('permission:create-employees')->name('hr.employees.store');
            Route::get('hr/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:view-employees')->name('hr.employees.show');
            Route::get('hr/employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:edit-employees')->name('hr.employees.edit');
            Route::put('hr/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:edit-employees')->name('hr.employees.update');
            Route::delete('hr/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:delete-employees')->name('hr.employees.destroy');
            Route::put('hr/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->middleware('permission:edit-employees')->name('hr.employees.toggle-status');
            Route::put('hr/employees/{employee}/change-password', [EmployeeController::class, 'changePassword'])->middleware('permission:edit-employees')->name('hr.employees.change-password');
            Route::delete('hr/employees/{userId}/documents/{documentId}', [EmployeeController::class, 'deleteDocument'])->middleware('permission:edit-employees')->name('hr.employees.documents.destroy');
            Route::put('hr/employees/{employee}/documents/{documentId}/approve', [EmployeeController::class, 'approveDocument'])->middleware('permission:edit-employees')->name('hr.employees.documents.approve');
            Route::put('hr/employees/{employee}/documents/{documentId}/reject', [EmployeeController::class, 'rejectDocument'])->middleware('permission:edit-employees')->name('hr.employees.documents.reject');
            Route::get('hr/employees/{userId}/documents/{documentId}/download', [EmployeeController::class, 'downloadDocument'])->middleware('permission:view-employees')->name('hr.employees.documents.download');
        });

        // Award Type Routes
        Route::middleware('permission:manage-award-types')->group(function () {
            Route::get('hr/award-types', [AwardTypeController::class, 'index'])->name('hr.award-types.index');
            Route::post('hr/award-types', [AwardTypeController::class, 'store'])->middleware('permission:create-award-types')->name('hr.award-types.store');
            Route::put('hr/award-types/{awardType}', [AwardTypeController::class, 'update'])->middleware('permission:edit-award-types')->name('hr.award-types.update');
            Route::delete('hr/award-types/{awardType}', [AwardTypeController::class, 'destroy'])->middleware('permission:delete-award-types')->name('hr.award-types.destroy');
            Route::put('hr/award-types/{awardType}/toggle-status', [AwardTypeController::class, 'toggleStatus'])->middleware('permission:edit-award-types')->name('hr.award-types.toggle-status');
        });

        // Award Routes
        Route::middleware('permission:manage-awards')->group(function () {
            Route::get('hr/awards', [AwardController::class, 'index'])->name('hr.awards.index');
            Route::get('hr/awards/create', [AwardController::class, 'create'])->middleware('permission:create-awards')->name('hr.awards.create');
            Route::post('hr/awards', [AwardController::class, 'store'])->middleware('permission:create-awards')->name('hr.awards.store');
            Route::get('hr/awards/{award}', [AwardController::class, 'show'])->middleware('permission:view-awards')->name('hr.awards.show');
            Route::get('hr/awards/{award}/edit', [AwardController::class, 'edit'])->middleware('permission:edit-awards')->name('hr.awards.edit');
            Route::put('hr/awards/{award}', [AwardController::class, 'update'])->middleware('permission:edit-awards')->name('hr.awards.update');
            Route::delete('hr/awards/{award}', [AwardController::class, 'destroy'])->middleware('permission:delete-awards')->name('hr.awards.destroy');
            Route::get('hr/awards/{award}/download-certificate', [AwardController::class, 'downloadCertificate'])->middleware('permission:view-awards')->name('hr.awards.download-certificate');
            Route::get('hr/awards/{award}/download-photo', [AwardController::class, 'downloadPhoto'])->middleware('permission:view-awards')->name('hr.awards.download-photo');
        });


        // Promotion Routes
        Route::middleware('permission:manage-promotions')->group(function () {
            Route::get('hr/promotions', [PromotionController::class, 'index'])->name('hr.promotions.index');
            Route::post('hr/promotions', [PromotionController::class, 'store'])->middleware('permission:create-promotions')->name('hr.promotions.store');
            Route::put('hr/promotions/{promotion}', [PromotionController::class, 'update'])->middleware('permission:edit-promotions')->name('hr.promotions.update');
            Route::delete('hr/promotions/{promotion}', [PromotionController::class, 'destroy'])->middleware('permission:delete-promotions')->name('hr.promotions.destroy');
            Route::get('hr/promotions/{promotion}/download-document', [PromotionController::class, 'downloadDocument'])->middleware('permission:view-promotions')->name('hr.promotions.download-document');
            Route::put('hr/promotions/{promotion}/update-status', [PromotionController::class, 'updateStatus'])->middleware('permission:edit-promotions')->name('hr.promotions.update-status');
        });

        // Resignation Routes
        Route::middleware('permission:manage-resignations')->group(function () {
            Route::get('hr/resignations', [ResignationController::class, 'index'])->name('hr.resignations.index');
            Route::post('hr/resignations', [ResignationController::class, 'store'])->middleware('permission:create-resignations')->name('hr.resignations.store');
            Route::put('hr/resignations/{resignation}', [ResignationController::class, 'update'])->middleware('permission:edit-resignations')->name('hr.resignations.update');
            Route::delete('hr/resignations/{resignation}', [ResignationController::class, 'destroy'])->middleware('permission:delete-resignations')->name('hr.resignations.destroy');
            Route::get('hr/resignations/{resignation}/download-document', [ResignationController::class, 'downloadDocument'])->middleware('permission:view-resignations')->name('hr.resignations.download-document');
            Route::put('hr/resignations/{resignation}/change-status', [ResignationController::class, 'changeStatus'])->middleware('permission:edit-resignations')->name('hr.resignations.change-status');
        });

        // Termination Routes
        Route::middleware('permission:manage-terminations')->group(function () {
            Route::get('hr/terminations', [TerminationController::class, 'index'])->name('hr.terminations.index');
            Route::post('hr/terminations', [TerminationController::class, 'store'])->middleware('permission:create-terminations')->name('hr.terminations.store');
            Route::put('hr/terminations/{termination}', [TerminationController::class, 'update'])->middleware('permission:edit-terminations')->name('hr.terminations.update');
            Route::delete('hr/terminations/{termination}', [TerminationController::class, 'destroy'])->middleware('permission:delete-terminations')->name('hr.terminations.destroy');
            Route::get('hr/terminations/{termination}/download-document', [TerminationController::class, 'downloadDocument'])->middleware('permission:view-terminations')->name('hr.terminations.download-document');
            Route::put('hr/terminations/{termination}/change-status', [TerminationController::class, 'changeStatus'])->middleware('permission:edit-terminations')->name('hr.terminations.change-status');
        });

        // Warning Routes
        Route::middleware('permission:manage-warnings')->group(function () {
            Route::get('hr/warnings', [WarningController::class, 'index'])->name('hr.warnings.index');
            Route::post('hr/warnings', [WarningController::class, 'store'])->middleware('permission:create-warnings')->name('hr.warnings.store');
            Route::put('hr/warnings/{warning}', [WarningController::class, 'update'])->middleware('permission:edit-warnings')->name('hr.warnings.update');
            Route::delete('hr/warnings/{warning}', [WarningController::class, 'destroy'])->middleware('permission:delete-warnings')->name('hr.warnings.destroy');
            Route::get('hr/warnings/{warning}/download-document', [WarningController::class, 'downloadDocument'])->middleware('permission:view-warnings')->name('hr.warnings.download-document');
            Route::put('hr/warnings/{warning}/change-status', [WarningController::class, 'changeStatus'])->middleware('permission:edit-warnings')->name('hr.warnings.change-status');
            Route::put('hr/warnings/{warning}/update-improvement-plan', [WarningController::class, 'updateImprovementPlan'])->middleware('permission:edit-warnings')->name('hr.warnings.update-improvement-plan');
        });

        // Trip Routes
        Route::middleware('permission:manage-trips')->group(function () {
            Route::get('hr/trips', [TripController::class, 'index'])->name('hr.trips.index');
            Route::post('hr/trips', [TripController::class, 'store'])->middleware('permission:create-trips')->name('hr.trips.store');
            Route::put('hr/trips/{trip}', [TripController::class, 'update'])->middleware('permission:edit-trips')->name('hr.trips.update');
            Route::delete('hr/trips/{trip}', [TripController::class, 'destroy'])->middleware('permission:delete-trips')->name('hr.trips.destroy');
            Route::get('hr/trips/{trip}/download-document', [TripController::class, 'downloadDocument'])->middleware('permission:view-trips')->name('hr.trips.download-document');
            Route::put('hr/trips/{trip}/change-status', [TripController::class, 'changeStatus'])->middleware('permission:edit-trips')->name('hr.trips.change-status');
            Route::put('hr/trips/{trip}/update-advance-status', [TripController::class, 'updateAdvanceStatus'])->middleware('permission:edit-trips')->name('hr.trips.update-advance-status');
            Route::put('hr/trips/{trip}/update-reimbursement-status', [TripController::class, 'updateReimbursementStatus'])->middleware('permission:edit-trips')->name('hr.trips.update-reimbursement-status');

            // Trip Expenses Routes
            Route::get('hr/trips/{trip}/expenses', [TripController::class, 'showExpenses'])->middleware('permission:manage-trip-expenses')->name('hr.trips.expenses');
            Route::post('hr/trips/{trip}/expenses', [TripController::class, 'storeExpense'])->middleware('permission:manage-trip-expenses')->name('hr.trips.expenses.store');
            Route::put('hr/trips/{trip}/expenses/{expense}', [TripController::class, 'updateExpense'])->middleware('permission:manage-trip-expenses')->name('hr.trips.expenses.update');
            Route::delete('hr/trips/{trip}/expenses/{expense}', [TripController::class, 'destroyExpense'])->middleware('permission:manage-trip-expenses')->name('hr.trips.expenses.destroy');
            Route::get('hr/trips/{trip}/expenses/{expense}/download-receipt', [TripController::class, 'downloadReceipt'])->middleware('permission:manage-trip-expenses')->name('hr.trips.expenses.download-receipt');
        });

        // Complaint Routes
        Route::middleware('permission:manage-complaints')->group(function () {
            Route::get('hr/complaints', [ComplaintController::class, 'index'])->name('hr.complaints.index');
            Route::post('hr/complaints', [ComplaintController::class, 'store'])->middleware('permission:create-complaints')->name('hr.complaints.store');
            Route::put('hr/complaints/{complaint}', [ComplaintController::class, 'update'])->middleware('permission:edit-complaints')->name('hr.complaints.update');
            Route::delete('hr/complaints/{complaint}', [ComplaintController::class, 'destroy'])->middleware('permission:delete-complaints')->name('hr.complaints.destroy');
            Route::get('hr/complaints/{complaint}/download-document', [ComplaintController::class, 'downloadDocument'])->middleware('permission:view-complaints')->name('hr.complaints.download-document');
            Route::put('hr/complaints/{complaint}/change-status', [ComplaintController::class, 'changeStatus'])->middleware('permission:edit-complaints')->name('hr.complaints.change-status');
            Route::put('hr/complaints/{complaint}/assign', [ComplaintController::class, 'assignComplaint'])->middleware('permission:assign-complaints')->name('hr.complaints.assign');
            Route::put('hr/complaints/{complaint}/resolve', [ComplaintController::class, 'resolveComplaint'])->middleware('permission:resolve-complaints')->name('hr.complaints.resolve');
            Route::put('hr/complaints/{complaint}/follow-up', [ComplaintController::class, 'updateFollowUp'])->middleware('permission:resolve-complaints')->name('hr.complaints.follow-up');
        });

        // Employee Transfer Routes
        Route::middleware('permission:manage-employee-transfers')->group(function () {
            Route::get('hr/transfers', [EmployeeTransferController::class, 'index'])->name('hr.transfers.index');
            Route::post('hr/transfers', [EmployeeTransferController::class, 'store'])->middleware('permission:create-employee-transfers')->name('hr.transfers.store');
            Route::put('hr/transfers/{transfer}', [EmployeeTransferController::class, 'update'])->middleware('permission:edit-employee-transfers')->name('hr.transfers.update');
            Route::delete('hr/transfers/{transfer}', [EmployeeTransferController::class, 'destroy'])->middleware('permission:delete-employee-transfers')->name('hr.transfers.destroy');
            Route::get('hr/transfers/{transfer}/download-document', [EmployeeTransferController::class, 'downloadDocument'])->middleware('permission:view-employee-transfers')->name('hr.transfers.download-document');
            Route::put('hr/transfers/{transfer}/approve', [EmployeeTransferController::class, 'approve'])->middleware('permission:approve-employee-transfers')->name('hr.transfers.approve');
            Route::put('hr/transfers/{transfer}/reject', [EmployeeTransferController::class, 'reject'])->middleware('permission:reject-employee-transfers')->name('hr.transfers.reject');
            Route::get('hr/transfers/get-department/{branchId}', [EmployeeTransferController::class, 'getDepartment'])->name('hr.transfers.getdepartment');
            Route::get('hr/transfers/get-designation/{departmentId}', [EmployeeTransferController::class, 'getDesignation'])->name('hr.transfers.getdesignation');
        });

        // Holiday Routes
        Route::middleware('permission:manage-holidays')->group(function () {
            Route::get('hr/holidays', [HolidayController::class, 'index'])->name('hr.holidays.index');
            Route::get('hr/holidays/calendar', [HolidayController::class, 'calendar'])->name('hr.holidays.calendar');
            Route::post('hr/holidays', [HolidayController::class, 'store'])->middleware('permission:create-holidays')->name('hr.holidays.store');
            Route::put('hr/holidays/{holiday}', [HolidayController::class, 'update'])->middleware('permission:edit-holidays')->name('hr.holidays.update');
            Route::delete('hr/holidays/{holiday}', [HolidayController::class, 'destroy'])->middleware('permission:delete-holidays')->name('hr.holidays.destroy');
            Route::get('hr/holidays/export/pdf', [HolidayController::class, 'exportPdf'])->name('hr.holidays.export.pdf');
            Route::get('hr/holidays/export/ical', [HolidayController::class, 'exportIcal'])->name('hr.holidays.export.ical');
        });

        // Announcement Routes
        Route::middleware('permission:manage-announcements')->group(function () {
            Route::get('hr/announcements', [AnnouncementController::class, 'index'])->name('hr.announcements.index');
            Route::get('hr/announcements/dashboard', [AnnouncementController::class, 'dashboard'])->name('hr.announcements.dashboard');
            Route::get('hr/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('hr.announcements.show');
            Route::post('hr/announcements', [AnnouncementController::class, 'store'])->middleware('permission:create-announcements')->name('hr.announcements.store');
            Route::put('hr/announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('permission:edit-announcements')->name('hr.announcements.update');
            Route::delete('hr/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('permission:delete-announcements')->name('hr.announcements.destroy');
            Route::get('hr/announcements/{announcement}/download-attachment', [AnnouncementController::class, 'downloadAttachment'])->name('hr.announcements.download-attachment');
            Route::get('hr/announcements/{announcement}/statistics', [AnnouncementController::class, 'viewStatistics'])->name('hr.announcements.statistics');
            Route::post('hr/announcements/{announcement}/mark-as-read', [AnnouncementController::class, 'markAsRead'])->name('hr.announcements.mark-as-read');
            Route::get('hr/announcements/get-departments/{branchIds}', [AnnouncementController::class, 'getDepartments'])->name('hr.announcements.get-departments');
        });

        // Asset Type Routes
        Route::middleware('permission:manage-asset-types')->group(function () {
            Route::get('hr/asset-types', [AssetTypeController::class, 'index'])->name('hr.asset-types.index');
            Route::post('hr/asset-types', [AssetTypeController::class, 'store'])->middleware('permission:create-asset-types')->name('hr.asset-types.store');
            Route::put('hr/asset-types/{assetType}', [AssetTypeController::class, 'update'])->middleware('permission:edit-asset-types')->name('hr.asset-types.update');
            Route::delete('hr/asset-types/{assetType}', [AssetTypeController::class, 'destroy'])->middleware('permission:delete-asset-types')->name('hr.asset-types.destroy');
        });

        // Asset Routes
        Route::middleware('permission:manage-assets')->group(function () {
            Route::get('hr/assets', [AssetController::class, 'index'])->name('hr.assets.index');
            Route::get('hr/assets/dashboard', [AssetController::class, 'dashboard'])->name('hr.assets.dashboard');
            Route::get('hr/assets/depreciation-report', [AssetController::class, 'depreciationReport'])->name('hr.assets.depreciation-report');
            Route::get('hr/assets/export-depreciation-csv', [AssetController::class, 'exportDepreciationCsv'])->name('hr.assets.export-depreciation-csv');
            Route::get('hr/assets/export-depreciation-csv', [AssetController::class, 'exportDepreciationCsv'])->name('hr.assets.export-depreciation-csv');
            Route::get('hr/assets/{asset}', [AssetController::class, 'show'])->name('hr.assets.show');
            Route::post('hr/assets', [AssetController::class, 'store'])->middleware('permission:create-assets')->name('hr.assets.store');
            Route::put('hr/assets/{asset}', [AssetController::class, 'update'])->middleware('permission:edit-assets')->name('hr.assets.update');
            Route::delete('hr/assets/{asset}', [AssetController::class, 'destroy'])->middleware('permission:delete-assets')->name('hr.assets.destroy');
            Route::post('hr/assets/{asset}/assign', [AssetController::class, 'assign'])->middleware('permission:assign-assets')->name('hr.assets.assign');
            Route::post('hr/assets/{asset}/return', [AssetController::class, 'returnAsset'])->middleware('permission:assign-assets')->name('hr.assets.return');
            Route::post('hr/assets/{asset}/schedule-maintenance', [AssetController::class, 'scheduleMaintenance'])->middleware('permission:manage-asset-maintenance')->name('hr.assets.schedule-maintenance');
            Route::put('hr/assets/maintenance/{maintenance}', [AssetController::class, 'updateMaintenance'])->middleware('permission:manage-asset-maintenance')->name('hr.assets.update-maintenance');
            Route::get('hr/assets/{asset}/download-document', [AssetController::class, 'downloadDocument'])->name('hr.assets.download-document');
            Route::get('hr/assets/{asset}/view-image', [AssetController::class, 'viewImage'])->name('hr.assets.view-image');
        });

        // Performance Module Routes

        // Performance Indicator Categories
        Route::middleware('permission:manage-performance-indicator-categories')->group(function () {
            Route::get('hr/performance/indicator-categories', [PerformanceIndicatorCategoryController::class, 'index'])->name('hr.performance.indicator-categories.index');
            Route::post('hr/performance/indicator-categories', [PerformanceIndicatorCategoryController::class, 'store'])->middleware('permission:create-performance-indicator-categories')->name('hr.performance.indicator-categories.store');
            Route::put('hr/performance/indicator-categories/{indicatorCategory}', [PerformanceIndicatorCategoryController::class, 'update'])->middleware('permission:edit-performance-indicator-categories')->name('hr.performance.indicator-categories.update');
            Route::delete('hr/performance/indicator-categories/{indicatorCategory}', [PerformanceIndicatorCategoryController::class, 'destroy'])->middleware('permission:delete-performance-indicator-categories')->name('hr.performance.indicator-categories.destroy');
            Route::put('hr/performance/indicator-categories/{indicatorCategory}/toggle-status', [PerformanceIndicatorCategoryController::class, 'toggleStatus'])->middleware('permission:edit-performance-indicator-categories')->name('hr.performance.indicator-categories.toggle-status');
        });

        // Performance Indicators
        Route::middleware('permission:manage-performance-indicators')->group(function () {
            Route::get('hr/performance/indicators', [PerformanceIndicatorController::class, 'index'])->name('hr.performance.indicators.index');
            Route::post('hr/performance/indicators', [PerformanceIndicatorController::class, 'store'])->middleware('permission:create-performance-indicators')->name('hr.performance.indicators.store');
            Route::put('hr/performance/indicators/{indicator}', [PerformanceIndicatorController::class, 'update'])->middleware('permission:edit-performance-indicators')->name('hr.performance.indicators.update');
            Route::delete('hr/performance/indicators/{indicator}', [PerformanceIndicatorController::class, 'destroy'])->middleware('permission:delete-performance-indicators')->name('hr.performance.indicators.destroy');
            Route::put('hr/performance/indicators/{indicator}/toggle-status', [PerformanceIndicatorController::class, 'toggleStatus'])->middleware('permission:edit-performance-indicators')->name('hr.performance.indicators.toggle-status');
        });

        // Goal Types
        Route::middleware('permission:manage-goal-types')->group(function () {
            Route::get('hr/performance/goal-types', [GoalTypeController::class, 'index'])->name('hr.performance.goal-types.index');
            Route::post('hr/performance/goal-types', [GoalTypeController::class, 'store'])->middleware('permission:create-goal-types')->name('hr.performance.goal-types.store');
            Route::put('hr/performance/goal-types/{goalType}', [GoalTypeController::class, 'update'])->middleware('permission:edit-goal-types')->name('hr.performance.goal-types.update');
            Route::delete('hr/performance/goal-types/{goalType}', [GoalTypeController::class, 'destroy'])->middleware('permission:delete-goal-types')->name('hr.performance.goal-types.destroy');
            Route::put('hr/performance/goal-types/{goalType}/toggle-status', [GoalTypeController::class, 'toggleStatus'])->middleware('permission:edit-goal-types')->name('hr.performance.goal-types.toggle-status');
        });

        // Employee Goals
        Route::middleware('permission:manage-employee-goals')->group(function () {
            Route::get('hr/performance/employee-goals', [EmployeeGoalController::class, 'index'])->name('hr.performance.employee-goals.index');
            Route::post('hr/performance/employee-goals', [EmployeeGoalController::class, 'store'])->middleware('permission:create-employee-goals')->name('hr.performance.employee-goals.store');
            Route::put('hr/performance/employee-goals/{employeeGoal}', [EmployeeGoalController::class, 'update'])->middleware('permission:edit-employee-goals')->name('hr.performance.employee-goals.update');
            Route::delete('hr/performance/employee-goals/{employeeGoal}', [EmployeeGoalController::class, 'destroy'])->middleware('permission:delete-employee-goals')->name('hr.performance.employee-goals.destroy');
            Route::put('hr/performance/employee-goals/{employeeGoal}/progress', [EmployeeGoalController::class, 'updateProgress'])->middleware('permission:edit-employee-goals')->name('hr.performance.employee-goals.update-progress');
        });

        // Review Cycles
        Route::middleware('permission:manage-review-cycles')->group(function () {
            Route::get('hr/performance/review-cycles', [ReviewCycleController::class, 'index'])->name('hr.performance.review-cycles.index');
            Route::post('hr/performance/review-cycles', [ReviewCycleController::class, 'store'])->middleware('permission:create-review-cycles')->name('hr.performance.review-cycles.store');
            Route::put('hr/performance/review-cycles/{reviewCycle}', [ReviewCycleController::class, 'update'])->middleware('permission:edit-review-cycles')->name('hr.performance.review-cycles.update');
            Route::delete('hr/performance/review-cycles/{reviewCycle}', [ReviewCycleController::class, 'destroy'])->middleware('permission:delete-review-cycles')->name('hr.performance.review-cycles.destroy');
            Route::put('hr/performance/review-cycles/{reviewCycle}/toggle-status', [ReviewCycleController::class, 'toggleStatus'])->middleware('permission:edit-review-cycles')->name('hr.performance.review-cycles.toggle-status');
        });


        // Employee Reviews
        Route::middleware('permission:manage-employee-reviews')->group(function () {
            Route::get('hr/performance/employee-reviews', [EmployeeReviewController::class, 'index'])->name('hr.performance.employee-reviews.index');
            Route::get('hr/performance/employee-reviews/create', [EmployeeReviewController::class, 'create'])->middleware('permission:create-employee-reviews')->name('hr.performance.employee-reviews.create');
            Route::post('hr/performance/employee-reviews', [EmployeeReviewController::class, 'store'])->middleware('permission:create-employee-reviews')->name('hr.performance.employee-reviews.store');
            Route::get('hr/performance/employee-reviews/{employeeReview}', [EmployeeReviewController::class, 'show'])->middleware('permission:view-employee-reviews')->name('hr.performance.employee-reviews.show');
            Route::get('hr/performance/employee-reviews/{employeeReview}/conduct', [EmployeeReviewController::class, 'conduct'])->middleware('permission:edit-employee-reviews')->name('hr.performance.employee-reviews.conduct');
            Route::post('hr/performance/employee-reviews/{employeeReview}/submit-ratings', [EmployeeReviewController::class, 'submitRatings'])->middleware('permission:edit-employee-reviews')->name('hr.performance.employee-reviews.submit-ratings');
            Route::put('hr/performance/employee-reviews/{employeeReview}', [EmployeeReviewController::class, 'update'])->middleware('permission:edit-employee-reviews')->name('hr.performance.employee-reviews.update');
            Route::delete('hr/performance/employee-reviews/{employeeReview}', [EmployeeReviewController::class, 'destroy'])->middleware('permission:delete-employee-reviews')->name('hr.performance.employee-reviews.destroy');
            Route::put('hr/performance/employee-reviews/{employeeReview}/status', [EmployeeReviewController::class, 'updateStatus'])->middleware('permission:edit-employee-reviews')->name('hr.performance.employee-reviews.update-status');
        });



        // Contract Types Routes
        Route::middleware('permission:manage-contract-types')->group(function () {
            Route::get('hr/contracts/contract-types', [\App\Http\Controllers\ContractTypeController::class, 'index'])->name('hr.contracts.contract-types.index');
            Route::post('hr/contracts/contract-types', [\App\Http\Controllers\ContractTypeController::class, 'store'])->middleware('permission:create-contract-types')->name('hr.contracts.contract-types.store');
            Route::put('hr/contracts/contract-types/{contractType}', [\App\Http\Controllers\ContractTypeController::class, 'update'])->middleware('permission:edit-contract-types')->name('hr.contracts.contract-types.update');
            Route::delete('hr/contracts/contract-types/{contractType}', [\App\Http\Controllers\ContractTypeController::class, 'destroy'])->middleware('permission:delete-contract-types')->name('hr.contracts.contract-types.destroy');
            Route::put('hr/contracts/contract-types/{contractType}/toggle-status', [\App\Http\Controllers\ContractTypeController::class, 'toggleStatus'])->middleware('permission:edit-contract-types')->name('hr.contracts.contract-types.toggle-status');
        });

        // Employee Contracts Routes
        Route::middleware('permission:manage-employee-contracts')->group(function () {
            Route::get('hr/contracts/employee-contracts', [\App\Http\Controllers\EmployeeContractController::class, 'index'])->name('hr.contracts.employee-contracts.index');
            Route::post('hr/contracts/employee-contracts', [\App\Http\Controllers\EmployeeContractController::class, 'store'])->middleware('permission:create-employee-contracts')->name('hr.contracts.employee-contracts.store');
            Route::put('hr/contracts/employee-contracts/{employeeContract}', [\App\Http\Controllers\EmployeeContractController::class, 'update'])->middleware('permission:edit-employee-contracts')->name('hr.contracts.employee-contracts.update');
            Route::delete('hr/contracts/employee-contracts/{employeeContract}', [\App\Http\Controllers\EmployeeContractController::class, 'destroy'])->middleware('permission:delete-employee-contracts')->name('hr.contracts.employee-contracts.destroy');
            Route::put('hr/contracts/employee-contracts/{employeeContract}/status', [\App\Http\Controllers\EmployeeContractController::class, 'updateStatus'])->middleware('permission:approve-employee-contracts')->name('hr.contracts.employee-contracts.update-status');
        });



        // Contract Renewals Routes
        Route::middleware('permission:manage-contract-renewals')->group(function () {
            Route::get('hr/contracts/contract-renewals', [\App\Http\Controllers\ContractRenewalController::class, 'index'])->name('hr.contracts.contract-renewals.index');
            Route::post('hr/contracts/contract-renewals', [\App\Http\Controllers\ContractRenewalController::class, 'store'])->middleware('permission:create-contract-renewals')->name('hr.contracts.contract-renewals.store');
            Route::put('hr/contracts/contract-renewals/{contractRenewal}', [\App\Http\Controllers\ContractRenewalController::class, 'update'])->middleware('permission:edit-contract-renewals')->name('hr.contracts.contract-renewals.update');
            Route::delete('hr/contracts/contract-renewals/{contractRenewal}', [\App\Http\Controllers\ContractRenewalController::class, 'destroy'])->middleware('permission:delete-contract-renewals')->name('hr.contracts.contract-renewals.destroy');
            Route::put('hr/contracts/contract-renewals/{contractRenewal}/approve', [\App\Http\Controllers\ContractRenewalController::class, 'approve'])->middleware('permission:approve-contract-renewals')->name('hr.contracts.contract-renewals.approve');
            Route::put('hr/contracts/contract-renewals/{contractRenewal}/reject', [\App\Http\Controllers\ContractRenewalController::class, 'reject'])->middleware('permission:reject-contract-renewals')->name('hr.contracts.contract-renewals.reject');
            Route::put('hr/contracts/contract-renewals/{contractRenewal}/process', [\App\Http\Controllers\ContractRenewalController::class, 'process'])->middleware('permission:edit-contract-renewals')->name('hr.contracts.contract-renewals.process');
        });

        // Contract Templates Routes
        Route::middleware('permission:manage-contract-templates')->group(function () {
            Route::get('hr/contracts/contract-templates', [\App\Http\Controllers\ContractTemplateController::class, 'index'])->name('hr.contracts.contract-templates.index');
            Route::post('hr/contracts/contract-templates', [\App\Http\Controllers\ContractTemplateController::class, 'store'])->middleware('permission:create-contract-templates')->name('hr.contracts.contract-templates.store');
            Route::put('hr/contracts/contract-templates/{contractTemplate}', [\App\Http\Controllers\ContractTemplateController::class, 'update'])->middleware('permission:edit-contract-templates')->name('hr.contracts.contract-templates.update');
            Route::delete('hr/contracts/contract-templates/{contractTemplate}', [\App\Http\Controllers\ContractTemplateController::class, 'destroy'])->middleware('permission:delete-contract-templates')->name('hr.contracts.contract-templates.destroy');
            Route::put('hr/contracts/contract-templates/{contractTemplate}/toggle-status', [\App\Http\Controllers\ContractTemplateController::class, 'toggleStatus'])->middleware('permission:edit-contract-templates')->name('hr.contracts.contract-templates.toggle-status');
            Route::post('hr/contracts/contract-templates/{contractTemplate}/generate', [\App\Http\Controllers\ContractTemplateController::class, 'generate'])->middleware('permission:view-contract-templates')->name('hr.contracts.contract-templates.generate');
        });

        // Document Categories Routes
        Route::middleware('permission:manage-document-categories')->group(function () {
            Route::get('hr/documents/document-categories', [\App\Http\Controllers\DocumentCategoryController::class, 'index'])->name('hr.documents.document-categories.index');
            Route::post('hr/documents/document-categories', [\App\Http\Controllers\DocumentCategoryController::class, 'store'])->middleware('permission:create-document-categories')->name('hr.documents.document-categories.store');
            Route::put('hr/documents/document-categories/{documentCategory}', [\App\Http\Controllers\DocumentCategoryController::class, 'update'])->middleware('permission:edit-document-categories')->name('hr.documents.document-categories.update');
            Route::delete('hr/documents/document-categories/{documentCategory}', [\App\Http\Controllers\DocumentCategoryController::class, 'destroy'])->middleware('permission:delete-document-categories')->name('hr.documents.document-categories.destroy');
            Route::put('hr/documents/document-categories/{documentCategory}/toggle-status', [\App\Http\Controllers\DocumentCategoryController::class, 'toggleStatus'])->middleware('permission:edit-document-categories')->name('hr.documents.document-categories.toggle-status');
        });

        // HR Documents Routes
        Route::middleware('permission:manage-hr-documents')->group(function () {
            Route::get('hr/documents/hr-documents', [\App\Http\Controllers\HrDocumentController::class, 'index'])->name('hr.documents.hr-documents.index');
            Route::post('hr/documents/hr-documents', [\App\Http\Controllers\HrDocumentController::class, 'store'])->middleware('permission:create-hr-documents')->name('hr.documents.hr-documents.store');
            Route::put('hr/documents/hr-documents/{hrDocument}', [\App\Http\Controllers\HrDocumentController::class, 'update'])->middleware('permission:edit-hr-documents')->name('hr.documents.hr-documents.update');
            Route::delete('hr/documents/hr-documents/{hrDocument}', [\App\Http\Controllers\HrDocumentController::class, 'destroy'])->middleware('permission:delete-hr-documents')->name('hr.documents.hr-documents.destroy');
            Route::get('hr/documents/hr-documents/{hrDocument}/download', [HrDocumentController::class, 'download'])->middleware('permission:view-hr-documents')->name('hr.documents.hr-documents.download');
            Route::put('hr/documents/hr-documents/{hrDocument}/status', [\App\Http\Controllers\HrDocumentController::class, 'updateStatus'])->middleware('permission:edit-hr-documents')->name('hr.documents.hr-documents.update-status');
        });



        // Document Acknowledgments Routes
        Route::middleware('permission:manage-document-acknowledgments')->group(function () {
            Route::get('hr/documents/document-acknowledgments', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'index'])->name('hr.documents.document-acknowledgments.index');
            Route::post('hr/documents/document-acknowledgments', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'store'])->middleware('permission:create-document-acknowledgments')->name('hr.documents.document-acknowledgments.store');
            Route::put('hr/documents/document-acknowledgments/{documentAcknowledgment}', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'update'])->middleware('permission:edit-document-acknowledgments')->name('hr.documents.document-acknowledgments.update');
            Route::delete('hr/documents/document-acknowledgments/{documentAcknowledgment}', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'destroy'])->middleware('permission:delete-document-acknowledgments')->name('hr.documents.document-acknowledgments.destroy');
            Route::put('hr/documents/document-acknowledgments/{documentAcknowledgment}/acknowledge', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'acknowledge'])->middleware('permission:acknowledge-document-acknowledgments')->name('hr.documents.document-acknowledgments.acknowledge');
            Route::post('hr/documents/document-acknowledgments/bulk-assign', [\App\Http\Controllers\DocumentAcknowledgmentController::class, 'bulkAssign'])->middleware('permission:create-document-acknowledgments')->name('hr.documents.document-acknowledgments.bulk-assign');
        });

        // Document Templates Routes
        Route::middleware('permission:manage-document-templates')->group(function () {
            Route::get('hr/documents/document-templates', [\App\Http\Controllers\DocumentTemplateController::class, 'index'])->name('hr.documents.document-templates.index');
            Route::post('hr/documents/document-templates', [\App\Http\Controllers\DocumentTemplateController::class, 'store'])->middleware('permission:create-document-templates')->name('hr.documents.document-templates.store');
            Route::put('hr/documents/document-templates/{documentTemplate}', [\App\Http\Controllers\DocumentTemplateController::class, 'update'])->middleware('permission:edit-document-templates')->name('hr.documents.document-templates.update');
            Route::delete('hr/documents/document-templates/{documentTemplate}', [\App\Http\Controllers\DocumentTemplateController::class, 'destroy'])->middleware('permission:delete-document-templates')->name('hr.documents.document-templates.destroy');
            Route::put('hr/documents/document-templates/{documentTemplate}/toggle-status', [\App\Http\Controllers\DocumentTemplateController::class, 'toggleStatus'])->middleware('permission:edit-document-templates')->name('hr.documents.document-templates.toggle-status');
            Route::post('hr/documents/document-templates/{documentTemplate}/preview', [\App\Http\Controllers\DocumentTemplateController::class, 'preview'])->middleware('permission:view-document-templates')->name('hr.documents.document-templates.preview');
            Route::post('hr/documents/document-templates/{documentTemplate}/generate', [\App\Http\Controllers\DocumentTemplateController::class, 'generate'])->middleware('permission:view-document-templates')->name('hr.documents.document-templates.generate');
        });

        // Leave Types routes
        Route::middleware('permission:manage-leave-types')->group(function () {
            Route::get('hr/leave-types', [\App\Http\Controllers\LeaveTypeController::class, 'index'])->name('hr.leave-types.index');
            Route::post('hr/leave-types', [\App\Http\Controllers\LeaveTypeController::class, 'store'])->middleware('permission:create-leave-types')->name('hr.leave-types.store');
            Route::put('hr/leave-types/{leaveType}', [\App\Http\Controllers\LeaveTypeController::class, 'update'])->middleware('permission:edit-leave-types')->name('hr.leave-types.update');
            Route::delete('hr/leave-types/{leaveType}', [\App\Http\Controllers\LeaveTypeController::class, 'destroy'])->middleware('permission:delete-leave-types')->name('hr.leave-types.destroy');
            Route::put('hr/leave-types/{leaveType}/toggle-status', [\App\Http\Controllers\LeaveTypeController::class, 'toggleStatus'])->middleware('permission:edit-leave-types')->name('hr.leave-types.toggle-status');
        });

        // Leave Policies routes
        Route::middleware('permission:manage-leave-policies')->group(function () {
            Route::get('hr/leave-policies', [\App\Http\Controllers\LeavePolicyController::class, 'index'])->name('hr.leave-policies.index');
            Route::post('hr/leave-policies', [\App\Http\Controllers\LeavePolicyController::class, 'store'])->middleware('permission:create-leave-policies')->name('hr.leave-policies.store');
            Route::put('hr/leave-policies/{leavePolicy}', [\App\Http\Controllers\LeavePolicyController::class, 'update'])->middleware('permission:edit-leave-policies')->name('hr.leave-policies.update');
            Route::delete('hr/leave-policies/{leavePolicy}', [\App\Http\Controllers\LeavePolicyController::class, 'destroy'])->middleware('permission:delete-leave-policies')->name('hr.leave-policies.destroy');
            Route::put('hr/leave-policies/{leavePolicy}/toggle-status', [\App\Http\Controllers\LeavePolicyController::class, 'toggleStatus'])->middleware('permission:edit-leave-policies')->name('hr.leave-policies.toggle-status');
        });

        // Leave Applications routes
        Route::middleware('permission:manage-leave-applications')->group(function () {
            Route::get('hr/leave-applications', [\App\Http\Controllers\LeaveApplicationController::class, 'index'])->name('hr.leave-applications.index');
            Route::post('hr/leave-applications', [\App\Http\Controllers\LeaveApplicationController::class, 'store'])->middleware('permission:create-leave-applications')->name('hr.leave-applications.store');
            Route::put('hr/leave-applications/{leaveApplication}', [\App\Http\Controllers\LeaveApplicationController::class, 'update'])->middleware('permission:edit-leave-applications')->name('hr.leave-applications.update');
            Route::delete('hr/leave-applications/{leaveApplication}', [\App\Http\Controllers\LeaveApplicationController::class, 'destroy'])->middleware('permission:delete-leave-applications')->name('hr.leave-applications.destroy');
            Route::put('hr/leave-applications/{leaveApplication}/status', [\App\Http\Controllers\LeaveApplicationController::class, 'updateStatus'])->middleware('permission:approve-leave-applications')->name('hr.leave-applications.update-status');
        });

        // Leave Balances routes
        Route::middleware('permission:manage-leave-balances')->group(function () {
            Route::get('hr/leave-balances', [\App\Http\Controllers\LeaveBalanceController::class, 'index'])->name('hr.leave-balances.index');
            Route::post('hr/leave-balances', [\App\Http\Controllers\LeaveBalanceController::class, 'store'])->middleware('permission:create-leave-balances')->name('hr.leave-balances.store');
            Route::put('hr/leave-balances/{leaveBalance}', [\App\Http\Controllers\LeaveBalanceController::class, 'update'])->middleware('permission:edit-leave-balances')->name('hr.leave-balances.update');
            Route::delete('hr/leave-balances/{leaveBalance}', [\App\Http\Controllers\LeaveBalanceController::class, 'destroy'])->middleware('permission:delete-leave-balances')->name('hr.leave-balances.destroy');
            Route::put('hr/leave-balances/{leaveBalance}/adjust', [\App\Http\Controllers\LeaveBalanceController::class, 'adjust'])->middleware('permission:adjust-leave-balances')->name('hr.leave-balances.adjust');
        });

        // Shifts routes
        Route::middleware('permission:manage-shifts')->group(function () {
            Route::get('hr/shifts', [\App\Http\Controllers\ShiftController::class, 'index'])->name('hr.shifts.index');
            Route::post('hr/shifts', [\App\Http\Controllers\ShiftController::class, 'store'])->middleware('permission:create-shifts')->name('hr.shifts.store');
            Route::put('hr/shifts/{shift}', [\App\Http\Controllers\ShiftController::class, 'update'])->middleware('permission:edit-shifts')->name('hr.shifts.update');
            Route::delete('hr/shifts/{shift}', [\App\Http\Controllers\ShiftController::class, 'destroy'])->middleware('permission:delete-shifts')->name('hr.shifts.destroy');
            Route::put('hr/shifts/{shift}/toggle-status', [\App\Http\Controllers\ShiftController::class, 'toggleStatus'])->middleware('permission:edit-shifts')->name('hr.shifts.toggle-status');
        });

        // Attendance Policies routes
        Route::middleware('permission:manage-attendance-policies')->group(function () {
            Route::get('hr/attendance-policies', [\App\Http\Controllers\AttendancePolicyController::class, 'index'])->name('hr.attendance-policies.index');
            Route::post('hr/attendance-policies', [\App\Http\Controllers\AttendancePolicyController::class, 'store'])->middleware('permission:create-attendance-policies')->name('hr.attendance-policies.store');
            Route::put('hr/attendance-policies/{attendancePolicy}', [\App\Http\Controllers\AttendancePolicyController::class, 'update'])->middleware('permission:edit-attendance-policies')->name('hr.attendance-policies.update');
            Route::delete('hr/attendance-policies/{attendancePolicy}', [\App\Http\Controllers\AttendancePolicyController::class, 'destroy'])->middleware('permission:delete-attendance-policies')->name('hr.attendance-policies.destroy');
            Route::put('hr/attendance-policies/{attendancePolicy}/toggle-status', [\App\Http\Controllers\AttendancePolicyController::class, 'toggleStatus'])->middleware('permission:edit-attendance-policies')->name('hr.attendance-policies.toggle-status');
        });

        // Attendance Records routes
        Route::middleware('permission:manage-attendance-records')->group(function () {
            Route::get('hr/attendance-records', [\App\Http\Controllers\AttendanceRecordController::class, 'index'])->name('hr.attendance-records.index');
            Route::post('hr/attendance-records', [\App\Http\Controllers\AttendanceRecordController::class, 'store'])->middleware('permission:create-attendance-records')->name('hr.attendance-records.store');
            Route::put('hr/attendance-records/{attendanceRecord}', [\App\Http\Controllers\AttendanceRecordController::class, 'update'])->middleware('permission:edit-attendance-records')->name('hr.attendance-records.update');
            Route::delete('hr/attendance-records/{attendanceRecord}', [\App\Http\Controllers\AttendanceRecordController::class, 'destroy'])->middleware('permission:delete-attendance-records')->name('hr.attendance-records.destroy');
        });

        // Clock In/Out routes
        Route::middleware('permission:clock-in-out')->group(function () {
            Route::post('hr/attendance/clock-in', [\App\Http\Controllers\AttendanceRecordController::class, 'clockIn'])->name('hr.attendance.clock-in');
            Route::post('hr/attendance/clock-out', [\App\Http\Controllers\AttendanceRecordController::class, 'clockOut'])->name('hr.attendance.clock-out');
        });

        // Attendance Regularizations routes
        Route::middleware('permission:manage-attendance-regularizations')->group(function () {
            Route::get('hr/attendance-regularizations', [\App\Http\Controllers\AttendanceRegularizationController::class, 'index'])->name('hr.attendance-regularizations.index');
            Route::post('hr/attendance-regularizations', [\App\Http\Controllers\AttendanceRegularizationController::class, 'store'])->middleware('permission:create-attendance-regularizations')->name('hr.attendance-regularizations.store');
            Route::put('hr/attendance-regularizations/{regularization}', [\App\Http\Controllers\AttendanceRegularizationController::class, 'update'])->middleware('permission:edit-attendance-regularizations')->name('hr.attendance-regularizations.update');
            Route::delete('hr/attendance-regularizations/{regularization}', [\App\Http\Controllers\AttendanceRegularizationController::class, 'destroy'])->middleware('permission:delete-attendance-regularizations')->name('hr.attendance-regularizations.destroy');
            Route::put('hr/attendance-regularizations/{regularization}/status', [\App\Http\Controllers\AttendanceRegularizationController::class, 'updateStatus'])->middleware('permission:approve-attendance-regularizations')->name('hr.attendance-regularizations.update-status');
            Route::get('hr/attendance-regularizations/get-employee-attendance/{id}', [\App\Http\Controllers\AttendanceRegularizationController::class, 'getEmployeeAttendance'])->name('hr.attendance-regularizations.get-employee-attendance');
        });

        // Time Entries routes
        Route::middleware('permission:manage-time-entries')->group(function () {
            Route::get('hr/time-entries', [\App\Http\Controllers\TimeEntryController::class, 'index'])->name('hr.time-entries.index');
            Route::post('hr/time-entries', [\App\Http\Controllers\TimeEntryController::class, 'store'])->middleware('permission:create-time-entries')->name('hr.time-entries.store');
            Route::put('hr/time-entries/{timeEntry}', [\App\Http\Controllers\TimeEntryController::class, 'update'])->middleware('permission:edit-time-entries')->name('hr.time-entries.update');
            Route::delete('hr/time-entries/{timeEntry}', [\App\Http\Controllers\TimeEntryController::class, 'destroy'])->middleware('permission:delete-time-entries')->name('hr.time-entries.destroy');
            Route::put('hr/time-entries/{timeEntry}/status', [\App\Http\Controllers\TimeEntryController::class, 'updateStatus'])->middleware('permission:approve-time-entries')->name('hr.time-entries.update-status');
        });








        // Currencies routes
        Route::middleware('permission:manage-currencies')->group(function () {
            Route::get('currencies', [CurrencyController::class, 'index'])->middleware('permission:manage-currencies')->name('currencies.index');
            Route::post('currencies', [CurrencyController::class, 'store'])->middleware('permission:create-currencies')->name('currencies.store');
            Route::put('currencies/{currency}', [CurrencyController::class, 'update'])->middleware('permission:edit-currencies')->name('currencies.update');
            Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy'])->middleware('permission:delete-currencies')->name('currencies.destroy');
        });

        // Calendar routes
        Route::middleware('permission:view-calendar')->group(function () {
            Route::get('calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
        });

        // =============================================
        // Attendance Module Routes (Phase 1)
        // =============================================

        // Quick Checkin Routes (Employee)
        Route::get('attendance/quick-checkin', [\App\Http\Controllers\QuickCheckinController::class, 'index'])->name('attendance.quick-checkin.index');
        Route::post('attendance/quick-checkin', [\App\Http\Controllers\QuickCheckinController::class, 'store'])->name('attendance.quick-checkin.store');
        Route::get('attendance/quick-checkin/live-status', [\App\Http\Controllers\QuickCheckinController::class, 'liveStatus'])->name('attendance.quick-checkin.live-status');

        // Bulk Checkin Routes (Supervisor)
        Route::middleware('permission:manage-employees')->group(function () {
            Route::get('attendance/bulk-checkin', [\App\Http\Controllers\BulkCheckinController::class, 'index'])->name('attendance.bulk-checkin.index');
            Route::post('attendance/bulk-checkin', [\App\Http\Controllers\BulkCheckinController::class, 'store'])->name('attendance.bulk-checkin.store');
            Route::get('attendance/bulk-checkin/logs', [\App\Http\Controllers\BulkCheckinController::class, 'logs'])->name('attendance.bulk-checkin.logs');
        });

        // Live Status Routes
        Route::get('attendance/live-status', [\App\Http\Controllers\QuickCheckinController::class, 'liveStatus'])->name('attendance.live-status');

        // Wi-Fi Networks Management Routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('hr/wifi-networks', [\App\Http\Controllers\WifiNetworkController::class, 'index'])->name('hr.wifi-networks.index');
            Route::post('hr/wifi-networks', [\App\Http\Controllers\WifiNetworkController::class, 'store'])->name('hr.wifi-networks.store');
            Route::put('hr/wifi-networks/{wifiNetwork}', [\App\Http\Controllers\WifiNetworkController::class, 'update'])->name('hr.wifi-networks.update');
            Route::delete('hr/wifi-networks/{wifiNetwork}', [\App\Http\Controllers\WifiNetworkController::class, 'destroy'])->name('hr.wifi-networks.destroy');
            Route::post('hr/wifi-networks/verify', [\App\Http\Controllers\WifiNetworkController::class, 'verify'])->name('hr.wifi-networks.verify');
        });

        // Time Windows Management Routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('hr/time-windows', [\App\Http\Controllers\TimeWindowController::class, 'index'])->name('hr.time-windows.index');
            Route::post('hr/time-windows', [\App\Http\Controllers\TimeWindowController::class, 'store'])->name('hr.time-windows.store');
            Route::put('hr/time-windows/{timeWindow}', [\App\Http\Controllers\TimeWindowController::class, 'update'])->name('hr.time-windows.update');
            Route::delete('hr/time-windows/{timeWindow}', [\App\Http\Controllers\TimeWindowController::class, 'destroy'])->name('hr.time-windows.destroy');
            Route::get('hr/time-windows/current', [\App\Http\Controllers\TimeWindowController::class, 'getCurrentWindow'])->name('hr.time-windows.current');
        });

        // Deduction Tiers Management Routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('hr/deduction-tiers', [\App\Http\Controllers\DeductionTierController::class, 'index'])->name('hr.deduction-tiers.index');
            Route::post('hr/deduction-tiers', [\App\Http\Controllers\DeductionTierController::class, 'store'])->name('hr.deduction-tiers.store');
            Route::put('hr/deduction-tiers/{deductionTier}', [\App\Http\Controllers\DeductionTierController::class, 'update'])->name('hr.deduction-tiers.update');
            Route::delete('hr/deduction-tiers/{deductionTier}', [\App\Http\Controllers\DeductionTierController::class, 'destroy'])->name('hr.deduction-tiers.destroy');
            Route::get('hr/deduction-tiers/calculate', [\App\Http\Controllers\DeductionTierController::class, 'calculate'])->name('hr.deduction-tiers.calculate');
        });

        // =====================================================
        // Phase 2: Competition & Gamification Routes
        // =====================================================

        // Branch Ranking Routes
        Route::get('reports/branch-ranking', [\App\Http\Controllers\BranchRankingController::class, 'index'])->name('reports.branch-ranking.index');
        Route::get('api/branch-ranking', [\App\Http\Controllers\BranchRankingController::class, 'getRankings'])->name('api.branch-ranking.index');
        Route::get('api/branch-ranking/{branchId}/stats', [\App\Http\Controllers\BranchRankingController::class, 'getBranchStats'])->name('api.branch-ranking.stats');
        Route::get('api/branch-ranking/top', [\App\Http\Controllers\BranchRankingController::class, 'getTopBranches'])->name('api.branch-ranking.top');
        Route::post('reports/branch-ranking/recalculate', [\App\Http\Controllers\BranchRankingController::class, 'recalculate'])->name('reports.branch-ranking.recalculate');

        // Badge Management Routes
        Route::middleware('permission:manage-employees')->group(function () {
            Route::get('hr/badges', [\App\Http\Controllers\BadgeController::class, 'index'])->name('hr.badges.index');
            Route::post('hr/badges', [\App\Http\Controllers\BadgeController::class, 'store'])->name('hr.badges.store');
            Route::put('hr/badges/{badge}', [\App\Http\Controllers\BadgeController::class, 'update'])->name('hr.badges.update');
            Route::delete('hr/badges/{badge}', [\App\Http\Controllers\BadgeController::class, 'destroy'])->name('hr.badges.destroy');
            Route::post('hr/badges/award', [\App\Http\Controllers\BadgeController::class, 'awardToEmployee'])->name('hr.badges.award');
            Route::post('hr/badges/create-defaults', [\App\Http\Controllers\BadgeController::class, 'createDefaults'])->name('hr.badges.create-defaults');
        });
        Route::get('api/badges/employee/{employeeId}', [\App\Http\Controllers\BadgeController::class, 'getEmployeeBadges'])->name('api.badges.employee');
        Route::get('api/badges/leaderboard', [\App\Http\Controllers\BadgeController::class, 'leaderboard'])->name('api.badges.leaderboard');

        // MVP & Leaderboard Routes
        Route::get('hr/mvp', [\App\Http\Controllers\MVPController::class, 'index'])->name('hr.mvp.index');
        Route::get('api/mvp/top-ten', [\App\Http\Controllers\MVPController::class, 'getTopTen'])->name('api.mvp.top-ten');
        Route::get('api/mvp/employee/{employeeId}', [\App\Http\Controllers\MVPController::class, 'getEmployeePerformance'])->name('api.mvp.employee');
        Route::get('api/mvp/streaks', [\App\Http\Controllers\MVPController::class, 'getTopStreaks'])->name('api.mvp.streaks');
        Route::get('api/mvp/records', [\App\Http\Controllers\MVPController::class, 'getRecordBreakers'])->name('api.mvp.records');
        Route::post('hr/mvp/select-monthly', [\App\Http\Controllers\MVPController::class, 'selectMonthlyMVP'])->name('hr.mvp.select-monthly');

        // News Ticker Routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('settings/news-ticker', [\App\Http\Controllers\NewsTickerController::class, 'index'])->name('settings.news-ticker.index');
            Route::post('settings/news-ticker', [\App\Http\Controllers\NewsTickerController::class, 'store'])->name('settings.news-ticker.store');
            Route::put('settings/news-ticker/{newsTicker}', [\App\Http\Controllers\NewsTickerController::class, 'update'])->name('settings.news-ticker.update');
            Route::delete('settings/news-ticker/{newsTicker}', [\App\Http\Controllers\NewsTickerController::class, 'destroy'])->name('settings.news-ticker.destroy');
        });
        Route::get('api/news-ticker/active', [\App\Http\Controllers\NewsTickerController::class, 'getActive'])->name('api.news-ticker.active');
        Route::post('api/news-ticker/{id}/view', [\App\Http\Controllers\NewsTickerController::class, 'trackView'])->name('api.news-ticker.view');
        Route::post('api/news-ticker/{id}/click', [\App\Http\Controllers\NewsTickerController::class, 'trackClick'])->name('api.news-ticker.click');

        // =====================================================
        // Phase 3 Routes: AI Features
        // =====================================================
        
        // Risk Predictions Routes
        Route::middleware('permission:view-hr-reports')->group(function () {
            Route::get('ai/risk-predictions', [\App\Http\Controllers\RiskPredictionController::class, 'index'])->name('ai.risk-predictions.index');
            Route::post('ai/risk-predictions/run', [\App\Http\Controllers\RiskPredictionController::class, 'runAnalysis'])->name('ai.risk-predictions.run');
            Route::put('ai/risk-predictions/{prediction}/status', [\App\Http\Controllers\RiskPredictionController::class, 'updateStatus'])->name('ai.risk-predictions.update-status');
            Route::post('ai/risk-predictions/{prediction}/outcome', [\App\Http\Controllers\RiskPredictionController::class, 'recordOutcome'])->name('ai.risk-predictions.record-outcome');
            Route::get('ai/risk-predictions/dashboard', [\App\Http\Controllers\RiskPredictionController::class, 'dashboard'])->name('ai.risk-predictions.dashboard');
        });
        
        // API Risk Predictions Routes
        Route::get('api/risk-predictions', [\App\Http\Controllers\RiskPredictionController::class, 'getPredictions'])->name('api.risk-predictions.index');
        Route::get('api/risk-predictions/stats', [\App\Http\Controllers\RiskPredictionController::class, 'getStats'])->name('api.risk-predictions.stats');
        
        // Security Routes (Liveness & Tamper Detection)
        Route::middleware('permission:view-hr-reports')->group(function () {
            Route::get('ai/security', [\App\Http\Controllers\SecurityController::class, 'index'])->name('ai.security.index');
            Route::get('ai/security/liveness-logs', [\App\Http\Controllers\SecurityController::class, 'livenessLogs'])->name('ai.security.liveness-logs');
            Route::get('ai/security/tamper-logs', [\App\Http\Controllers\SecurityController::class, 'tamperLogs'])->name('ai.security.tamper-logs');
            Route::put('ai/security/tamper/{tamperLog}/review', [\App\Http\Controllers\SecurityController::class, 'reviewTamper'])->name('ai.security.review-tamper');
        });
        
        // API Security Routes
        Route::post('api/security/liveness-check', [\App\Http\Controllers\SecurityController::class, 'performLivenessCheck'])->name('api.security.liveness-check');
        Route::get('api/security/liveness-stats', [\App\Http\Controllers\SecurityController::class, 'getLivenessStats'])->name('api.security.liveness-stats');
        Route::get('api/security/tamper-stats', [\App\Http\Controllers\SecurityController::class, 'getTamperStats'])->name('api.security.tamper-stats');
        
        // Sentiment Analysis Routes
        Route::middleware('permission:view-hr-reports')->group(function () {
            Route::get('ai/sentiment', [\App\Http\Controllers\SentimentAnalysisController::class, 'index'])->name('ai.sentiment.index');
            Route::post('ai/sentiment/run', [\App\Http\Controllers\SentimentAnalysisController::class, 'runAnalysis'])->name('ai.sentiment.run');
            Route::get('ai/sentiment/employee/{employee}', [\App\Http\Controllers\SentimentAnalysisController::class, 'analyzeEmployee'])->name('ai.sentiment.employee');
            Route::post('ai/sentiment/{analysis}/followup', [\App\Http\Controllers\SentimentAnalysisController::class, 'assignFollowup'])->name('ai.sentiment.assign-followup');
            Route::put('ai/sentiment/{analysis}/followup-status', [\App\Http\Controllers\SentimentAnalysisController::class, 'updateFollowupStatus'])->name('ai.sentiment.update-followup');
        });
        
        // API Sentiment Routes
        Route::get('api/sentiment/summary', [\App\Http\Controllers\SentimentAnalysisController::class, 'getSummary'])->name('api.sentiment.summary');
        Route::get('api/sentiment/trend', [\App\Http\Controllers\SentimentAnalysisController::class, 'getTrend'])->name('api.sentiment.trend');
        Route::get('api/sentiment/by-branch', [\App\Http\Controllers\SentimentAnalysisController::class, 'getByBranch'])->name('api.sentiment.by-branch');
        Route::get('api/sentiment/by-department', [\App\Http\Controllers\SentimentAnalysisController::class, 'getByDepartment'])->name('api.sentiment.by-department');

        // =====================================================
        // Phase 4 Routes: Advanced Features
        // =====================================================
        
        // Work Zones Routes
        Route::middleware('permission:manage-branches')->group(function () {
            Route::get('settings/work-zones', [\App\Http\Controllers\WorkZoneController::class, 'index'])->name('settings.work-zones.index');
            Route::post('settings/work-zones', [\App\Http\Controllers\WorkZoneController::class, 'store'])->name('settings.work-zones.store');
            Route::get('settings/work-zones/{zone}', [\App\Http\Controllers\WorkZoneController::class, 'show'])->name('settings.work-zones.show');
            Route::put('settings/work-zones/{zone}', [\App\Http\Controllers\WorkZoneController::class, 'update'])->name('settings.work-zones.update');
            Route::delete('settings/work-zones/{zone}', [\App\Http\Controllers\WorkZoneController::class, 'destroy'])->name('settings.work-zones.destroy');
            Route::put('settings/work-zones/{zone}/toggle-status', [\App\Http\Controllers\WorkZoneController::class, 'toggleStatus'])->name('settings.work-zones.toggle-status');
            Route::post('settings/work-zones/{zone}/employees', [\App\Http\Controllers\WorkZoneController::class, 'addEmployee'])->name('settings.work-zones.add-employee');
            Route::delete('settings/work-zones/{zone}/employees/{employee}', [\App\Http\Controllers\WorkZoneController::class, 'removeEmployee'])->name('settings.work-zones.remove-employee');
        });
        
        // Zone Access Logs Routes
        Route::middleware('permission:view-hr-reports')->group(function () {
            Route::get('reports/zone-access-logs', [\App\Http\Controllers\WorkZoneController::class, 'accessLogs'])->name('reports.zone-access-logs');
            Route::get('reports/zone-unauthorized', [\App\Http\Controllers\WorkZoneController::class, 'unauthorizedAttempts'])->name('reports.zone-unauthorized');
        });
        
        // API Zone Routes
        Route::post('api/zones/check-access', [\App\Http\Controllers\WorkZoneController::class, 'checkAccess'])->name('api.zones.check-access');
        Route::post('api/zones/log-entry', [\App\Http\Controllers\WorkZoneController::class, 'logEntry'])->name('api.zones.log-entry');
        Route::post('api/zones/log-exit', [\App\Http\Controllers\WorkZoneController::class, 'logExit'])->name('api.zones.log-exit');
        
        // Exit Permits Routes
        Route::middleware('permission:manage-leaves')->group(function () {
            Route::get('hr/exit-permits', [\App\Http\Controllers\ExitPermitController::class, 'index'])->name('hr.exit-permits.index');
            Route::get('hr/exit-permits/create', [\App\Http\Controllers\ExitPermitController::class, 'create'])->name('hr.exit-permits.create');
            Route::post('hr/exit-permits', [\App\Http\Controllers\ExitPermitController::class, 'store'])->name('hr.exit-permits.store');
            Route::get('hr/exit-permits/{permit}', [\App\Http\Controllers\ExitPermitController::class, 'show'])->name('hr.exit-permits.show');
            Route::put('hr/exit-permits/{permit}', [\App\Http\Controllers\ExitPermitController::class, 'update'])->name('hr.exit-permits.update');
            Route::delete('hr/exit-permits/{permit}', [\App\Http\Controllers\ExitPermitController::class, 'destroy'])->name('hr.exit-permits.destroy');
            Route::post('hr/exit-permits/{permit}/approve', [\App\Http\Controllers\ExitPermitController::class, 'approve'])->name('hr.exit-permits.approve');
            Route::post('hr/exit-permits/{permit}/reject', [\App\Http\Controllers\ExitPermitController::class, 'reject'])->name('hr.exit-permits.reject');
            Route::post('hr/exit-permits/{permit}/cancel', [\App\Http\Controllers\ExitPermitController::class, 'cancel'])->name('hr.exit-permits.cancel');
            Route::post('hr/exit-permits/{permit}/record-return', [\App\Http\Controllers\ExitPermitController::class, 'recordReturn'])->name('hr.exit-permits.record-return');
            Route::post('hr/exit-permits/{permit}/extend', [\App\Http\Controllers\ExitPermitController::class, 'extend'])->name('hr.exit-permits.extend');
        });
        
        // Exit Permit Settings Routes
        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('settings/exit-permits', [\App\Http\Controllers\ExitPermitController::class, 'settings'])->name('settings.exit-permits.index');
            Route::put('settings/exit-permits', [\App\Http\Controllers\ExitPermitController::class, 'updateSettings'])->name('settings.exit-permits.update');
        });
        
        // API Exit Permit Routes
        Route::get('api/exit-permits/verify/{qrCode}', [\App\Http\Controllers\ExitPermitController::class, 'verifyQR'])->name('api.exit-permits.verify');
        Route::get('api/exit-permits/my-permits', [\App\Http\Controllers\ExitPermitController::class, 'myPermits'])->name('api.exit-permits.my-permits');
        Route::get('api/exit-permits/pending-count', [\App\Http\Controllers\ExitPermitController::class, 'pendingCount'])->name('api.exit-permits.pending-count');
        
        // Lockdown Routes
        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('security/lockdown', [\App\Http\Controllers\LockdownController::class, 'index'])->name('security.lockdown.index');
            Route::post('security/lockdown', [\App\Http\Controllers\LockdownController::class, 'store'])->name('security.lockdown.store');
            Route::get('security/lockdown/{lockdown}', [\App\Http\Controllers\LockdownController::class, 'show'])->name('security.lockdown.show');
            Route::put('security/lockdown/{lockdown}', [\App\Http\Controllers\LockdownController::class, 'update'])->name('security.lockdown.update');
            Route::put('security/lockdown/{lockdown}/end', [\App\Http\Controllers\LockdownController::class, 'end'])->name('security.lockdown.end');
            Route::post('security/lockdown/{lockdown}/add-exempt', [\App\Http\Controllers\LockdownController::class, 'addExempt'])->name('security.lockdown.add-exempt');
            Route::delete('security/lockdown/{lockdown}/remove-exempt/{employee}', [\App\Http\Controllers\LockdownController::class, 'removeExempt'])->name('security.lockdown.remove-exempt');
            Route::post('security/lockdown/{lockdown}/emergency-override', [\App\Http\Controllers\LockdownController::class, 'emergencyOverride'])->name('security.lockdown.emergency-override');
        });
        
        // API Lockdown Routes
        Route::get('api/lockdown/status', [\App\Http\Controllers\LockdownController::class, 'checkStatus'])->name('api.lockdown.status');
        Route::get('api/lockdown/can-checkin', [\App\Http\Controllers\LockdownController::class, 'canCheckin'])->name('api.lockdown.can-checkin');
        
        // Audit Logs Routes
        Route::middleware('permission:view-hr-reports')->group(function () {
            Route::get('security/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('security.audit-logs.index');
            Route::get('security/audit-logs/dashboard', [\App\Http\Controllers\AuditLogController::class, 'dashboard'])->name('security.audit-logs.dashboard');
            Route::get('security/audit-logs/export', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('security.audit-logs.export');
            Route::get('security/audit-logs/{log}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('security.audit-logs.show');
            Route::put('security/audit-logs/{log}/reviewed', [\App\Http\Controllers\AuditLogController::class, 'markReviewed'])->name('security.audit-logs.mark-reviewed');
        });
        
        // API Audit Logs Routes
        Route::get('api/audit-logs/recent', [\App\Http\Controllers\AuditLogController::class, 'getRecent'])->name('api.audit-logs.recent');
        Route::get('api/audit-logs/stats', [\App\Http\Controllers\AuditLogController::class, 'getStats'])->name('api.audit-logs.stats');
        
        // PWA Routes
        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('settings/pwa', [\App\Http\Controllers\PwaController::class, 'index'])->name('settings.pwa.index');
            Route::put('settings/pwa', [\App\Http\Controllers\PwaController::class, 'update'])->name('settings.pwa.update');
            Route::post('settings/pwa/send-notification', [\App\Http\Controllers\PwaController::class, 'sendNotification'])->name('settings.pwa.send-notification');
            Route::get('settings/pwa/subscriptions', [\App\Http\Controllers\PwaController::class, 'subscriptions'])->name('settings.pwa.subscriptions');
            Route::get('settings/pwa/offline-queue', [\App\Http\Controllers\PwaController::class, 'offlineQueue'])->name('settings.pwa.offline-queue');
            Route::post('settings/pwa/process-offline-queue', [\App\Http\Controllers\PwaController::class, 'processOfflineQueue'])->name('settings.pwa.process-offline-queue');
        });
        
        // API PWA Routes (public within authenticated)
        Route::get('api/pwa/manifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('api.pwa.manifest');
        Route::post('api/pwa/subscribe', [\App\Http\Controllers\PwaController::class, 'subscribe'])->name('api.pwa.subscribe');
        Route::post('api/pwa/unsubscribe', [\App\Http\Controllers\PwaController::class, 'unsubscribe'])->name('api.pwa.unsubscribe');
        Route::post('api/pwa/sync-offline', [\App\Http\Controllers\PwaController::class, 'syncOffline'])->name('api.pwa.sync-offline');
        Route::get('api/pwa/offline-data', [\App\Http\Controllers\PwaController::class, 'offlineData'])->name('api.pwa.offline-data');

        // Impersonation routes
        Route::middleware('App\Http\Middleware\SuperAdminMiddleware')->group(function () {
            Route::get('impersonate/{userId}', [ImpersonateController::class, 'start'])->name('impersonate.start');
        });

        Route::post('impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
    }); // End plan.access middleware group
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

Route::match(['GET', 'POST'], 'payments/easebuzz/success', [EasebuzzPaymentController::class, 'success'])->name('easebuzz.success');
Route::post('payments/easebuzz/callback', [EasebuzzPaymentController::class, 'callback'])->name('easebuzz.callback');

// Cookie consent routes
Route::post('/cookie-consent/store', [CookieConsentController::class, 'store'])->name('cookie.consent.store');
Route::get('/cookie-consent/download', [CookieConsentController::class, 'download'])->name('cookie.consent.download');
