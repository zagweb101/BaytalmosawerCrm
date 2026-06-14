<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerTask;
use App\Models\FollowUp;
use App\Services\RecommendationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            if (! auth()->check()) {
                $view->with('globalAlertCount', 0);
                return;
            }

            $todayEnd = Carbon::today()->endOfDay();
            $dueFollowUps = FollowUp::whereNull('completed_at')
                ->whereNotNull('due_at')
                ->where('due_at', '<=', $todayEnd)
                ->count();
            $dueTasks = CustomerTask::whereNull('completed_at')
                ->where(function ($query) use ($todayEnd) {
                    $query->whereNull('due_at')->orWhere('due_at', '<=', $todayEnd);
                })
                ->count();
            $recommendations = app(RecommendationService::class)->active(30)->count();
            $unassignedCustomers = Customer::whereNull('team_member_id')->count();

            $view->with('globalAlertCount', $dueFollowUps + $dueTasks + $recommendations + $unassignedCustomers);
        });
    }
}
