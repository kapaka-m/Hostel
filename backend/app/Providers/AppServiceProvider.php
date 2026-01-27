<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Policies\AnnouncementPolicy;
use App\Policies\DormPolicy;
use App\Policies\FloorPolicy;
use App\Policies\RoomAssignmentPolicy;
use App\Policies\RoomPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        Gate::policy(Dorm::class, DormPolicy::class);
        Gate::policy(Floor::class, FloorPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(RoomAssignment::class, RoomAssignmentPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);

        Dorm::observe(AuditObserver::class);
        DormAdmin::observe(AuditObserver::class);
        Floor::observe(AuditObserver::class);
        Room::observe(AuditObserver::class);
        RoomAssignment::observe(AuditObserver::class);
        Student::observe(AuditObserver::class);
        Announcement::observe(AuditObserver::class);
        Ticket::observe(AuditObserver::class);
        TicketComment::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }
}
