<?php

use Illuminate\Support\Facades\Route;
use KwtSMS\Laravel\Http\Controllers\Admin\AlertsController;
use KwtSMS\Laravel\Http\Controllers\Admin\DashboardController;
use KwtSMS\Laravel\Http\Controllers\Admin\HelpController;
use KwtSMS\Laravel\Http\Controllers\Admin\IntegrationsController;
use KwtSMS\Laravel\Http\Controllers\Admin\LogsController;
use KwtSMS\Laravel\Http\Controllers\Admin\SettingsController;
use KwtSMS\Laravel\Http\Controllers\Admin\TemplatesController;

// IMPORTANT: admin_middleware MUST include an authentication guard (e.g. 'auth').
// Removing 'auth' from kwtsms.admin_middleware in config/kwtsms.php would expose
// all admin routes (logs, settings, templates, credentials status) to unauthenticated users.
Route::prefix(config('kwtsms.admin_route_prefix', 'kwtsms'))
    ->middleware(config('kwtsms.admin_middleware', ['web', 'auth']))
    ->name('kwtsms.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/connect', [SettingsController::class, 'connect'])->name('settings.connect');
        Route::get('/templates', [TemplatesController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [TemplatesController::class, 'create'])->name('templates.create');
        Route::post('/templates', [TemplatesController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}/edit', [TemplatesController::class, 'edit'])->name('templates.edit');
        Route::put('/templates/{template}', [TemplatesController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [TemplatesController::class, 'destroy'])->name('templates.destroy');
        Route::get('/integrations', [IntegrationsController::class, 'index'])->name('integrations');
        Route::post('/integrations', [IntegrationsController::class, 'update'])->name('integrations.update');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
        Route::delete('/logs', [LogsController::class, 'clear'])->name('logs.clear');
        Route::get('/logs/export', [LogsController::class, 'export'])->name('logs.export');
        Route::get('/logs/{log}', [LogsController::class, 'show'])->name('logs.show');
        Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
        Route::post('/alerts', [AlertsController::class, 'update'])->name('alerts.update');
        Route::get('/help', [HelpController::class, 'index'])->name('help');
    });
