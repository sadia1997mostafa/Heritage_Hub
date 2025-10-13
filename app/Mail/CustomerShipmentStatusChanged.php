<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerShipmentStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $shipment;
    public $oldStatus;
    public $newStatus;

    public function __construct($shipment, $oldStatus, $newStatus)
    {
        $this->shipment = $shipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject("Shipment #{$this->shipment->id} status updated to {$this->newStatus}")
                    ->view('emails.shipment-status')
                    ->with(['shipment'=>$this->shipment,'newStatus'=>$this->newStatus]);
    }
}
