<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CampaignContactController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyMemberController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
 */

Route::prefix('faqs')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [FaqController::class, 'store']);
        Route::get('/{id}', [FaqController::class, 'edit']);
        Route::post('/{id}', [FaqController::class, 'update']);
        Route::delete('/{id}', [FaqController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::get('/category/{categorie}', [FaqController::class, 'showByCategory']);
    });
});

Route::prefix('activities')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/{id}', [ActivityController::class, 'edit']);
        Route::post('/{id}', [ActivityController::class, 'update']);
        Route::delete('/{id}', [ActivityController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
    });
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login');

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::get('/me', [AdminController::class, 'me']);
        Route::put('/update-info', [AdminController::class, 'updateInfo']);
        Route::put('/change-password', [AdminController::class, 'changePassword']);
        Route::put('/change-email', [AdminController::class, 'changeEmail']);
        Route::post('/update-avatar', [AdminController::class, 'updateAvatar']);
        Route::get('/dashboard/statistics', [AppController::class, 'getDashboardStatistics']);
    });
});

Route::prefix('projects')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/assign', [ProjectController::class, 'getProjectsToAssign']);
        Route::get('/assign/search', [ProjectController::class, 'searchProjectsToAssign']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::post('{id}', [ProjectController::class, 'update']);
        Route::delete('{id}', [ProjectController::class, 'destroy']);
        Route::get('/search', [ProjectController::class, 'searchByTitle']);
        Route::get('{id}', [ProjectController::class, 'show']);
        Route::post('/{id}/fiscal-receipt', [ProjectController::class, 'addFiscalReceipt']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
    });
});

Route::middleware(['auth:sanctum', 'company'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'show']);
    Route::put('/', [NotificationController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'company'])->prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::post('/', [ContactController::class, 'store']);
    Route::put('/{id}', [ContactController::class, 'update']);
    Route::delete('/{id}', [ContactController::class, 'destroy']);
    Route::get('/search', [ContactController::class, 'search']);
    Route::get('/{id}', [ContactController::class, 'getSingleContact']);
});

Route::prefix('campaign')->group(function () {
    Route::middleware(['auth:sanctum', 'company'])->group(function () {
        Route::post('/contacts', [CampaignContactController::class, 'store']);
        Route::delete('/contacts/{contactId}', [CampaignContactController::class, 'destroy']);
        Route::get('/contacts', [CampaignContactController::class, 'getContactsForCampaign']);
        Route::get('/steps/status', [CampaignController::class, 'checkCampaignSteps']);
        Route::post('/launch', [CampaignController::class, 'launchCampaign']);
        Route::get('/stats', [CampaignController::class, 'getCampaignStats']);
        Route::get('/results', [CampaignController::class, 'getCampaignResults']);
        Route::post('/{campaignId}/finish', [CampaignController::class, 'finishCampaign']);
        Route::post('/{campaignId}/relaunch', [CampaignController::class, 'relaunchCampaign']);
    });

    Route::get('{campaignId}/{contactId}/projects', [CampaignController::class, 'getCampaignProjects']);
    Route::post('{campaignId}/vote', [CampaignController::class, 'submitVote']);
});

Route::prefix('company')->group(function () {
    Route::post('login', [CompanyController::class, 'login']);

    Route::middleware(['auth:sanctum', 'company'])->group(function () {
        Route::post('logout', [CompanyController::class, 'logout']);
        Route::post('change-password', [CompanyController::class, 'changePassword']);
        Route::post('{id}/update', [CompanyController::class, 'updateForClient']);
        Route::post('{companyId}/projects/validate', [ProjectController::class, 'validateProjects']);
        Route::post('/members', [CompanyMemberController::class, 'store']);
        Route::get('/members', [CompanyMemberController::class, 'index']);
        Route::put('/billing', [BillingController::class, 'updateBillingInformation']);
        Route::get('/donations',   [CompanyController::class, 'getCompanyDonations']);
        Route::get('impact-statistics', [AppController::class, 'getImpactStatistics']);
        Route::get('dashboard', [AppController::class, 'getCustomerCompanyStatistics']);
    });

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/filter', [CompanyController::class, 'filter']);
        Route::post('{id}', [CompanyController::class, 'update']);
        Route::delete('{id}', [CompanyController::class, 'destroy']);
        Route::post('{companyId}/assign-project', [ProjectController::class, 'assignProjects']);
        Route::delete('{companyId}/projects/{projectId}', [ProjectController::class, 'removeProject']);
        Route::get('{companyId}/members', [CompanyMemberController::class, 'companyMembers']);
        Route::get('{companyId}/donations',   [CompanyController::class, 'getCompanyDonationsByAdmin']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::delete('{companyId}/members/{memberId}', [CompanyMemberController::class, 'destroy']);
        Route::put('{companyId}/members/{memberId}', [CompanyMemberController::class, 'update']);
        Route::get('{companyId}/current-projects', [ProjectController::class, 'getCurrentProjects']);
        Route::get('{companyId}/projects', [ProjectController::class, 'getCompanyProjects']);
        Route::get('{companyId}/billing', [BillingController::class, 'show']);
        Route::get('{id}', [CompanyController::class, 'show']);
    });
});

Route::any('{any}', function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
})->where('any', '.*');

