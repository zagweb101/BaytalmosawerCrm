<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerNoteController;
use App\Http\Controllers\CustomerImportController;
use App\Http\Controllers\CustomerCommunicationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\OfferingController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\RecommendationActionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('login', [AuthController::class, 'create'])->name('login');
Route::post('login', [AuthController::class, 'store'])->name('login.store');
Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('password', [AuthController::class, 'editPassword'])->name('password.edit');
    Route::patch('password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('pipeline', [PipelineController::class, 'index'])->middleware('permission:customers.view')->name('pipeline.index');
    Route::patch('pipeline/customers/{customer}/status', [PipelineController::class, 'updateStatus'])->middleware('permission:customers.update')->name('pipeline.status.update');
    Route::get('reports/sources', [ReportController::class, 'sources'])->middleware('permission:reports.view')->name('reports.sources');
    Route::get('reports/interests', [ReportController::class, 'interests'])->middleware('permission:reports.view')->name('reports.interests');
    Route::get('reports/campaigns', [ReportController::class, 'campaigns'])->middleware('permission:reports.view')->name('reports.campaigns');
    Route::get('reports/duplicates', [ReportController::class, 'duplicates'])->middleware('permission:reports.view')->name('reports.duplicates');
    Route::get('reports/team', [ReportController::class, 'team'])->middleware('permission:reports.view')->name('reports.team');
    Route::get('follow-ups/today', [FollowUpController::class, 'today'])->middleware('permission:followups.view')->name('follow-ups.today');
    Route::patch('follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->middleware('permission:followups.complete')->name('follow-ups.complete');
    Route::get('tasks/today', [CustomerTaskController::class, 'today'])->middleware('permission:tasks.view')->name('tasks.today');
    Route::patch('tasks/{customerTask}/complete', [CustomerTaskController::class, 'complete'])->middleware('permission:tasks.complete')->name('tasks.complete');
    Route::get('deals', [DealController::class, 'index'])->middleware('permission:deals.view')->name('deals.index');
    Route::patch('deals/{deal}/stage', [DealController::class, 'updateStage'])->middleware('permission:deals.change_stage')->name('deals.stage.update');
    Route::get('alerts', [AlertController::class, 'index'])->middleware('permission:alerts.view')->name('alerts.index');

    Route::middleware('permission:customers.import')->group(function () {
        Route::get('customers/import', [CustomerImportController::class, 'create'])->name('customers.import.create');
        Route::get('customers/import/template', [CustomerImportController::class, 'template'])->name('customers.import.template');
        Route::post('customers/import', [CustomerImportController::class, 'store'])->name('customers.import.store');
    });

    Route::middleware('permission:reports.export')->group(function () {
        Route::get('exports/customers', [ExportController::class, 'customers'])->name('exports.customers');
        Route::get('exports/deals', [ExportController::class, 'deals'])->name('exports.deals');
    });

    Route::resource('offerings', OfferingController::class)->except(['show'])->middleware('permission:offerings.manage');
    Route::resource('campaigns', CampaignController::class)->except(['show'])->middleware('permission:campaigns.manage');
    Route::resource('team-members', TeamMemberController::class)->except(['show'])->middleware('permission:team.manage');
    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:users.manage');
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('permission:roles.manage');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');

    Route::get('customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->middleware('permission:customers.create')->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view')->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customers.update')->name('customers.edit');
    Route::match(['PUT', 'PATCH'], 'customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update')->name('customers.update');

    Route::post('customers/{customer}/follow-ups', [FollowUpController::class, 'store'])->middleware('permission:followups.create')->name('customers.follow-ups.store');
    Route::post('customers/{customer}/tasks', [CustomerTaskController::class, 'store'])->middleware('permission:tasks.create')->name('customers.tasks.store');
    Route::post('customers/{customer}/deals', [DealController::class, 'store'])->middleware('permission:deals.create')->name('customers.deals.store');
    Route::post('customers/{customer}/notes', [CustomerNoteController::class, 'store'])->middleware('permission:customers.update')->name('customers.notes.store');
    Route::post('customers/{customer}/communication', [CustomerCommunicationController::class, 'store'])->middleware('permission:followups.create')->name('customers.communication.store');
    Route::post('customers/{customer}/recommendations', [RecommendationActionController::class, 'store'])->middleware('permission:followups.create')->name('customers.recommendations.store');
    Route::delete('customer-notes/{customerNote}', [CustomerNoteController::class, 'destroy'])->middleware('permission:customers.update')->name('customer-notes.destroy');
});
