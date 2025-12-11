<?php

namespace App\Providers;

use App\Events\InvoiceStatusUpdated;
use App\Events\ServiceOrderStatusUpdated;
use App\Listeners\SendInvoiceNotification;
use App\Listeners\SendServiceOrderNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ServiceOrderStatusUpdated::class => [
            SendServiceOrderNotification::class,
        ],
        InvoiceStatusUpdated::class => [
            SendInvoiceNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
