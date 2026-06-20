<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientProjectController;
use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\Admin\ProjectFileController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Client\TicketController as ClientTicketController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Admin\CompanySettingController;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::resource(
    '/admin/users',
    App\Http\Controllers\Admin\UserController::class
)->middleware([
    'auth',
    'role:super_admin'
])->names('users');

Route::resource(
    '/admin/clients',
    App\Http\Controllers\Admin\ClientController::class
)->middleware([
    'auth',
    'role:super_admin'
])->names('clients');

Route::resource(
    '/admin/projects',
    App\Http\Controllers\Admin\ProjectController::class
)->middleware([
    'auth',
    'role:super_admin'
])->names('projects');

Route::resource(
    '/admin/invoices',
    App\Http\Controllers\Admin\InvoiceController::class
)->middleware([
    'auth',
    'role:super_admin'
])->names('invoices');

Route::get(
    '/admin/payments/export',
    [App\Http\Controllers\Admin\PaymentController::class, 'export']
)->middleware([
    'auth',
    'role:super_admin'
])->name('payments.export');

Route::resource(
    '/admin/payments',
    App\Http\Controllers\Admin\PaymentController::class
)->middleware([
    'auth',
    'role:super_admin'
])->names('payments');

Route::get(
    '/admin/invoices/{invoice}/pdf',
    [App\Http\Controllers\Admin\InvoiceController::class, 'pdf']
)->middleware([
    'auth',
    'role:super_admin'
])->name('invoices.pdf');

Route::get(
    '/admin/projects/{project}/files',
    [App\Http\Controllers\Admin\ProjectFileController::class, 'index']
)->middleware([
    'auth',
    'role:super_admin'
])->name('project.files');

Route::post(
    '/admin/projects/{project}/files',
    [App\Http\Controllers\Admin\ProjectFileController::class, 'store']
)->middleware([
    'auth',
    'role:super_admin'
])->name('project.files.store');

Route::delete(
    '/admin/project-files/{file}',
    [ProjectFileController::class, 'destroy']
)->middleware([
    'auth',
    'role:super_admin'
])->name('project.files.delete');

/*
|--------------------------------------------------------------------------
| CLIENT AREA
|--------------------------------------------------------------------------
*/

Route::prefix('client')
    ->middleware([
        'auth',
        'role:client'
    ])
    ->group(function () {

        // Dashboard
        Route::get(
            '/dashboard',
            [App\Http\Controllers\Client\DashboardController::class, 'index']
        )->name('client.dashboard');

        // Projects
        Route::get(
            '/projects',
            [App\Http\Controllers\Client\ProjectController::class, 'index']
        )->name('client.projects');

        Route::get(
            '/projects/{project}',
            [ClientProjectController::class, 'show']
        )->name('client.projects.show');

        Route::get(
            '/projects/{project}/files',
            [App\Http\Controllers\Client\ProjectFileController::class, 'index']
        )->name('client.project.files');

        // Invoices
        Route::get(
            '/invoices',
            [App\Http\Controllers\Client\InvoiceController::class, 'index']
        )->name('client.invoices');

        Route::get(
            '/invoices/{invoice}/pdf',
            [App\Http\Controllers\Client\InvoiceController::class, 'pdf']
        )->name('client.invoices.pdf');

        // Payments
        Route::get(
            '/payments',
            [App\Http\Controllers\Client\PaymentController::class, 'index']
        )->name('client.payments');

        // Profile
        Route::get(
            '/profile',
            [ClientProfileController::class, 'index']
        )->name('client.profile');

        Route::put(
            '/profile/update',
            [ClientProfileController::class, 'update']
        )->name('client.profile.update');

        // Notifications
        Route::get(
            '/notifications',
            [ClientNotificationController::class, 'index']
        )->name('client.notifications');

        // Tickets
        Route::get(
            '/tickets',
            [ClientTicketController::class, 'index']
        )->name('client.tickets.index');

        Route::get(
            '/tickets/create',
            [ClientTicketController::class, 'create']
        )->name('client.tickets.create');

        Route::post(
            '/tickets/store',
            [ClientTicketController::class, 'store']
        )->name('client.tickets.store');

        Route::get(
            '/tickets/{ticket}',
            [ClientTicketController::class, 'show']
        )->name('client.tickets.show');

        Route::post(
            '/tickets/{ticket}/reply',
            [ClientTicketController::class, 'reply']
        )->name('client.tickets.reply');

    });


/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware([
        'auth',
        'role:super_admin|admin'
    ])
    ->group(function () {

        // Dashboard
        Route::get(
            '/dashboard',
            [App\Http\Controllers\Admin\DashboardController::class, 'index']
        )->name('admin.dashboard');

        // Notifications
        Route::get(
            '/notifications',
            [AdminNotificationController::class, 'index']
        )->name('admin.notifications');

        // Settings
        Route::get(
            '/settings',
            [CompanySettingController::class, 'edit']
        )->name('settings.index');

        Route::put(
            '/settings',
            [CompanySettingController::class, 'update']
        )->name('settings.update');

        // Tickets
        Route::get(
            '/tickets',
            [AdminTicketController::class, 'index']
        )->name('admin.tickets.index');

        Route::get(
            '/tickets/{ticket}',
            [AdminTicketController::class, 'show']
        )->name('admin.tickets.show');

        Route::put(
            '/tickets/{ticket}/status',
            [AdminTicketController::class, 'updateStatus']
        )->name('admin.tickets.status');

        Route::post(
            '/tickets/{ticket}/reply',
            [AdminTicketController::class, 'reply']
        )->name('admin.tickets.reply');

        Route::get(
            '/invoice/{invoice}/download',
            [App\Http\Controllers\Admin\InvoiceController::class, 'pdf']
        )->name('invoice.download');

        Route::get(
            '/invoice/{invoice}/view',
            [App\Http\Controllers\Admin\InvoiceController::class, 'viewPdf']
        )->name('invoice.view');

        Route::delete(
            '/notifications/{notification}',
            [AdminNotificationController::class, 'destroy']
        )->name(
            'admin.notifications.destroy'
        );


    });




require __DIR__.'/auth.php';
