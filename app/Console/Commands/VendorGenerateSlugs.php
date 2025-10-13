<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorProfile;
use Illuminate\Support\Str;

class VendorGenerateSlugs extends Command
{
    protected $signature = 'vendor:generate-slugs {--dry-run}';
    protected $description = 'Generate slugs for vendor profiles missing them';

    public function handle()
    {
        $profiles = VendorProfile::whereNull('slug')->orWhere('slug','')->get();
        if ($profiles->isEmpty()) { $this->info('No vacant slugs found'); return 0; }
        foreach ($profiles as $p) {
            $base = Str::slug($p->shop_name ?: ('vendor-'.$p->id));
            $slug = $base; $i = 1;
            while (VendorProfile::where('slug',$slug)->exists()) { $slug = $base.'-'.$i++; }
            $this->line("Assigning slug {$slug} to vendor {$p->id} ({$p->shop_name})");
            if (!$this->option('dry-run')) { $p->slug = $slug; $p->save(); }
        }
        return 0;
    }
}
