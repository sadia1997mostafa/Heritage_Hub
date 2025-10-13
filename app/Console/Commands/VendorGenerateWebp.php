<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorProfile;

class VendorGenerateWebp extends Command
{
    protected $signature = 'vendor:generate-webp {--dry-run}';
    protected $description = 'Generate webp variants for vendor images (shop_logo_path and banner_path)';

    public function handle()
    {
        $profiles = VendorProfile::whereNotNull('shop_logo_path')->orWhereNotNull('banner_path')->get();
        if ($profiles->isEmpty()) { $this->info('No images found'); return 0; }
        $sizes = [320,640,1024];
        foreach ($profiles as $p) {
            foreach (['shop_logo_path','banner_path'] as $field) {
                $path = $p->{$field};
                if (empty($path)) continue;
                $src = storage_path('app/public/'.$path);
                if (!file_exists($src)) { $this->line("missing: {$src}"); continue; }

                foreach ($sizes as $w) {
                    $dstRel = preg_replace('/\.[^.]+$/', "-{$w}.webp", $path);
                    $dst = storage_path('app/public/'.$dstRel);
                    $this->line("Converting {$src} -> {$dst}");
                    if ($this->option('dry-run')) continue;

                    // Imagick path (resize & convert)
                    if (class_exists('Imagick')) {
                        try {
                            $i = new \Imagick($src);
                            $i->setImageFormat('webp');
                            $i->resizeImage($w, 0, \Imagick::FILTER_LANCZOS, 1);
                            $i->writeImage($dst);
                            $i->clear();
                        } catch (\Exception $e) { $this->error('Imagick failed: '.$e->getMessage()); }
                    } else {
                        // GD fallback: load and resample
                        $data = @file_get_contents($src);
                        if (!$data) { $this->error('read failed'); continue; }
                        $im = @imagecreatefromstring($data);
                        if (!$im) { $this->error('gd cannot read image'); continue; }
                        $origW = imagesx($im);
                        $origH = imagesy($im);
                        $newW = $w;
                        $newH = intval($origH * ($newW / $origW));
                        $tmp = imagecreatetruecolor($newW, $newH);
                        imagecopyresampled($tmp, $im, 0,0,0,0, $newW, $newH, $origW, $origH);
                        imagewebp($tmp, $dst, 80);
                        imagedestroy($tmp);
                        imagedestroy($im);
                    }
                }
            }
        }
        return 0;
    }
}
