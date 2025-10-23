<?php

namespace App\Http\Controllers;

use App\Models\Vlog;
use App\Models\VlogImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VlogController extends Controller
{
    public function index(Request $req)
    {
        $user = $req->user();

        // Story tales: all approved vlogs (public)
        $storyVlogs = Vlog::with(['user','images'])
            ->where('approved', true)
            ->latest('published_at')
            ->latest()
            ->paginate(15, ['*'], 'stories_page');

        // My timeline: the current user's vlogs (including pending)
        $myVlogs = null;
        if ($user) {
            $myVlogs = Vlog::with('images')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'my_page');
        }

        return view('vlogs.index', compact('storyVlogs','myVlogs'));
    }

    public function store(Request $req)
    {
        \Log::info('Vlog store invoked', ['user_id' => optional($req->user())->id, 'files' => array_keys($req->allFiles())]);
        $req->validate([
            'title' => 'nullable|max:200',
            'body'  => 'required|max:5000',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:5120'
        ]);

        $user = $req->user();
        if (!$user) return redirect()->route('login');

        $vlog = Vlog::create([
            'user_id' => $user->id,
            'title'   => $req->title,
            'body'    => $req->body,
            'published_at' => now(),
            'approved' => false,
        ]);

        // Handle images[] uploads with error logging
        if ($req->hasFile('images')) {
            foreach ($req->file('images') as $file) {
                try {
                    if (!$file->isValid()) continue;

                    $folder = 'vlogs/' . date('Y/m');
                    $name = uniqid('vlog_') . '.' . $file->getClientOriginalExtension();
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
                    $file->move(dirname($tmpPath), basename($tmpPath));

                    // Resize main (1200px) and thumbnail (400px)
                    $mainPath = storage_path('app/public/') . $folder . '/' . $name;
                    @mkdir(dirname($mainPath), 0755, true);
                    $okMain = $this->resize_image_to($tmpPath, $mainPath, 1200);

                    $thumbName = 'thumb_' . $name;
                    $thumbPath = storage_path('app/public/') . $folder . '/' . $thumbName;
                    $okThumb = $this->resize_image_to($tmpPath, $thumbPath, 400);

                    // If resizing is not possible (GD absent), copy the original file to public storage
                    if (!$okMain) {
                        try {
                            @mkdir(dirname($mainPath), 0755, true);
                            copy($tmpPath, $mainPath);
                            \Log::info('Copied original upload as fallback for main', ['file'=>$name]);
                        } catch (\Throwable $e) {
                            \Log::error('Failed to copy original upload', ['err'=>$e->getMessage()]);
                        }
                    }
                    if (!$okThumb) {
                        try {
                            @mkdir(dirname($thumbPath), 0755, true);
                            copy($tmpPath, $thumbPath);
                        } catch (\Throwable $e) {
                            \Log::warning('Failed to copy thumbnail fallback', ['err'=>$e->getMessage()]);
                        }
                    }

                    // Save record using public path
                    $publicPath = $folder . '/' . $name;
                    VlogImage::create(['vlog_id' => $vlog->id, 'path' => $publicPath]);

                    @unlink($tmpPath);
                } catch (\Throwable $e) {
                    \Log::error('Vlog image upload error', ['message'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
                }
            }
        }

        return redirect()->route('vlogs.index')->with('status','Vlog posted. It will appear after review.');
    }

    // Edit form
    public function edit(Vlog $vlog)
    {
        $this->authorize('update',$vlog);
        $vlog->load('images');
        return view('vlogs.edit', compact('vlog'));
    }

    public function update(Request $req, Vlog $vlog)
    {
        $this->authorize('update',$vlog);

        $req->validate([
            'title' => 'nullable|max:200',
            'body'  => 'required|max:5000',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:5120'
        ]);

        $vlog->update(['title' => $req->title, 'body' => $req->body]);

        // optional new images
        if ($req->hasFile('images')) {
            foreach ($req->file('images') as $file) {
                if (!$file->isValid()) continue;
                $folder = 'vlogs/' . date('Y/m');
                $name = uniqid('vlog_') . '.' . $file->getClientOriginalExtension();
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
                $file->move(dirname($tmpPath), basename($tmpPath));
                $mainPath = storage_path('app/public/') . $folder . '/' . $name;
                @mkdir(dirname($mainPath), 0755, true);
                $this->resize_image_to($tmpPath, $mainPath, 1200);
                $thumbName = 'thumb_' . $name;
                $thumbPath = storage_path('app/public/') . $folder . '/' . $thumbName;
                $this->resize_image_to($tmpPath, $thumbPath, 400);
                $publicPath = $folder . '/' . $name;
                VlogImage::create(['vlog_id' => $vlog->id, 'path' => $publicPath]);
                @unlink($tmpPath);
            }
        }

        return redirect()->route('vlogs.edit', $vlog)->with('status','Vlog updated.');
    }

    public function destroy(Vlog $vlog)
    {
        $this->authorize('delete',$vlog);
        // remove images
        foreach ($vlog->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }
        $vlog->delete();
        return redirect()->route('vlogs.index')->with('status','Vlog deleted.');
    }

    // Admin: list pending vlogs
    public function adminIndex()
    {
        // very simple admin gate: user with ability 'viewAny'
        // Log the current user for debugging admin access issues (both default and admin guard)
        try {
            $webUser = request()->user();
            $adminUser = \Auth::guard('admin')->user();
            \Log::info('Admin vlogs accessed', [
                'web_user_class' => $webUser ? get_class($webUser) : null,
                'web_user_id' => $webUser ? $webUser->id : null,
                'admin_user_class' => $adminUser ? get_class($adminUser) : null,
                'admin_user_id' => $adminUser ? $adminUser->id : null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to log admin vlogs access', ['err' => $e->getMessage()]);
        }

        // If an admin is authenticated via the admin guard, allow access directly.
        if (!\Auth::guard('admin')->check()) {
            $this->authorize('viewAny', Vlog::class);
        }
        $vlogs = Vlog::with(['user','images'])->orderBy('created_at','desc')->paginate(30);
        return view('admin.vlogs.index', compact('vlogs'));
    }

    public function approve(Request $req, Vlog $vlog)
    {
        // Allow admin guard users to approve without hitting the default policy guard mismatch
        if (!\Auth::guard('admin')->check()) {
            $this->authorize('approve', $vlog);
            $approverId = $req->user()->id;
        } else {
            $approver = \Auth::guard('admin')->user();
            $approverId = $approver ? $approver->id : null;
        }

        $vlog->approved = true;
        $vlog->approved_by = $approverId;
        $vlog->approved_at = now();
        $vlog->save();

        // Create a local notification for the vlog owner
        try {
            $owner = $vlog->user;
            if ($owner) {
                \App\Models\LocalNotification::create([
                    'user_id' => $owner->id,
                    'type' => 'vlog.approved',
                    'data' => [
                        'message' => 'Your timeline entry "' . substr($vlog->title ?: $vlog->body,0,80) . '" has been approved.',
                        'url' => route('vlogs.index') . '#vlog-' . $vlog->id,
                        'vlog_id' => $vlog->id,
                    ],
                    'is_read' => false,
                ]);
                \Log::info('LocalNotification created for approved vlog', ['vlog_id'=>$vlog->id, 'owner_id'=>$owner->id]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to create LocalNotification for approved vlog', ['err'=>$e->getMessage()]);
        }

        return back()->with('status','Vlog approved.');
    }

    // Local helper: resize image using GD. Returns true on success.
    protected function resize_image_to($sourcePath, $destPath, $maxWidth)
    {
        if (!file_exists($sourcePath)) return false;
        $info = @getimagesize($sourcePath);
        if (!$info) return false;
        [$width, $height, $type] = $info;
        $ratio = $width / $height;
        $newWidth = min($maxWidth, $width);
        $newHeight = intval($newWidth / $ratio);

        // If GD functions are not available in this PHP build, bail out so caller can fallback.
        if (!function_exists('imagecreatefromjpeg') && !function_exists('imagecreatefrompng') && !function_exists('imagecreatefromgif')) {
            return false;
        }

        switch ($type) {
            case IMAGETYPE_JPEG:
                if (!function_exists('imagecreatefromjpeg')) return false;
                $src = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                if (!function_exists('imagecreatefrompng')) return false;
                $src = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                if (!function_exists('imagecreatefromgif')) return false;
                $src = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($dst, $src, 0,0,0,0, $newWidth, $newHeight, $width, $height);

        $ok = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $ok = imagejpeg($dst, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $ok = imagepng($dst, $destPath);
                break;
            case IMAGETYPE_GIF:
                $ok = imagegif($dst, $destPath);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
        return $ok;
    }
}
