<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class GenerateWebpForMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mediaPath;

    public function __construct(string $mediaPath)
    {
        $this->mediaPath = $mediaPath;
    }

    public function handle()
    {
        // Keep logic small: reuse existing console command codepath if present.
        $fs = new Filesystem();
        $disk = Storage::disk('public');
        if (!$disk->exists($this->mediaPath)) {
            return;
        }

        $full = storage_path('app/public/' . $this->mediaPath);
        if (!file_exists($full)) return;

        $sizes = [320,640,1024];
        foreach ($sizes as $s) {
            $dest = preg_replace('/\.[^.]+$/', "-{$s}.webp", $full);
            try {
                // try Imagick first
                if (class_exists('Imagick')) {
                    $img = new \Imagick($full);
                    $img->setImageColorspace(Imagick::COLORSPACE_RGB);
                    $img->setImageFormat('webp');
                    $img->resizeImage($s, 0, \Imagick::FILTER_LANCZOS, 1);
                    $img->writeImage($dest);
                    $img->clear();
                    $img->destroy();
                } else {
                    // GD fallback
                    $src = imagecreatefromstring(file_get_contents($full));
                    $w = imagesx($src);
                    $h = imagesy($src);
                    $nw = $s;
                    $nh = intval($h * ($nw / $w));
                    $tmp = imagecreatetruecolor($nw, $nh);
                    imagecopyresampled($tmp, $src, 0,0,0,0, $nw, $nh, $w, $h);
                    imagewebp($tmp, $dest, 80);
                    imagedestroy($tmp);
                    imagedestroy($src);
                }
            } catch (\Exception $e) {
                // Log and continue
                \Log::error('WebP generation failed for ' . $this->mediaPath . ' size ' . $s . ': ' . $e->getMessage());
            }
        }
    }
}
