<?php

namespace App\Providers;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Policies\DormPolicy;
use App\Policies\FloorPolicy;
use App\Policies\RoomPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Dorm::class, DormPolicy::class);
        Gate::policy(Floor::class, FloorPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);

        Dorm::observe(AuditObserver::class);
        DormAdmin::observe(AuditObserver::class);
        Floor::observe(AuditObserver::class);
        Room::observe(AuditObserver::class);
        RoomAssignment::observe(AuditObserver::class);
        Student::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }
}
