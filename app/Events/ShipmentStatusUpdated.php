<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ShipmentStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $shipment;
    public $oldStatus;
    public $newStatus;

    public function __construct($shipment, $oldStatus, $newStatus)
    {
        $this->shipment = $shipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
