<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateWebpForMedia;

class DispatchWebpJobs extends Command
{
    protected $signature = 'webp:dispatch {--limit=100}';
    protected $description = 'Dispatch webp generation jobs for product and vendor media';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $disk = Storage::disk('public');

        // Find product_media entries
        $files = [];
        if (class_exists('\App\Models\ProductMedia')) {
            foreach (\App\Models\ProductMedia::limit($limit)->get() as $pm) {
                $files[] = $pm->path;
            }
        }

        // Vendor profile images (if any)
        if (class_exists('\App\Models\VendorProfile')) {
            foreach (\App\Models\VendorProfile::limit($limit)->get() as $v) {
                if ($v->image) $files[] = $v->image;
            }
        }

        $count = 0;
        foreach ($files as $f) {
            // Only dispatch if original exists
            if ($disk->exists($f)) {
                GenerateWebpForMedia::dispatch($f);
                $count++;
            }
        }

        $this->info("Dispatched {$count} webp jobs");
        return 0;
    }
}
