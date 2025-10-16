<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ShipmentStatusUpdated;
use App\Listeners\SendShipmentStatusEmail;
use App\Listeners\SendShipmentStatusSms;
use App\Listeners\HandleShipmentDelivered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ShipmentStatusUpdated::class => [
            SendShipmentStatusEmail::class,
            SendShipmentStatusSms::class,
            HandleShipmentDelivered::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
