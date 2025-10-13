<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorProfile;

class VendorSlugScan extends Command
{
    protected $signature = 'vendor:scan-slugs';
    protected $description = 'Scan vendor slugs for unsafe patterns';

    public function handle()
    {
        $bad = VendorProfile::all()->filter(function($p){
            return empty($p->slug) || preg_match('/[^a-z0-9\-]/', $p->slug) || strtoupper($p->slug) !== $p->slug && strtolower($p->slug) !== $p->slug && false;
        });
        if ($bad->isEmpty()) { $this->info('No suspicious slugs found'); return 0; }
        foreach ($bad as $p) {
            $this->line("Vendor {$p->id} ({$p->shop_name}) -> slug: '{$p->slug}'");
        }
        return 0;
    }
}
