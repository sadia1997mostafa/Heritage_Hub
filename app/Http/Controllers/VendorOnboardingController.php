<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Models\VendorPayoutAccount;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class VendorOnboardingController extends Controller
{
    public function applyForm()
    {
        $districts = District::orderBy('name')->get(['id','name']);
        // hardcoded vendor categories (no DB table)
        $categories = [
            'Handloom & Textiles',
            'Embroidery & Needlework',
            'Pottery & Terracotta',
            'Woodcraft & Bamboo',
            'Metal & Brassware',
            'Jewelry & Ornaments',
            'Painting & Folk Art',
            'Leather Craft',
            'Food Heritage',
            'Festive & Ritual Items',
        ];
        return view('vendor.apply', compact('districts','categories'));
    }

    public function applySubmit(Request $req)
    {
        $req->validate([
            'shop_name'       => 'required|max:120',
            'phone'           => 'required|max:32',
            'district_id'     => 'nullable|exists:districts,id',
            'vendor_category' => 'required|string|max:120',   // <- hardcoded list, validate as string
            'address'         => 'nullable|max:255',
            'description'     => 'nullable|max:2000',
            'heritage_story'  => 'nullable|max:3000',
            'shop_logo'       => 'nullable|image|max:2048',
            'support_email'   => 'nullable|email',
            'support_phone'   => 'nullable|max:32',
        ]);

        $u = $req->user();
        $slug = Str::slug($req->shop_name).'-'.Str::random(4);

        $logoPath = null;
        if ($req->hasFile('shop_logo')) {
            $logoPath = Storage::disk('public')->putFile('vendors/logos', $req->file('shop_logo'));
        }

        VendorProfile::updateOrCreate(
            ['user_id' => $u->id],
            [
                'shop_name'       => $req->shop_name,
                'slug'            => $slug,
                'status'          => 'pending',
                'phone'           => $req->phone,
                'support_email'   => $req->support_email,
                'support_phone'   => $req->support_phone,
                'district_id'     => $req->district_id,
                'vendor_category' => $req->vendor_category,
                'address'         => $req->address,
                'description'     => $req->description,
                'heritage_story'  => $req->heritage_story,
                'shop_logo_path'  => $logoPath,
            ]
        );

        return redirect()->route('home')->with('status','Vendor application submitted. Await admin approval.');
    }

    public function setupForm(Request $req)
    {
        $profile   = VendorProfile::where('user_id',$req->user()->id)->firstOrFail();
        // protect against missing vendor_profile_images table which can crash the view
        try {
            if (!Schema::hasTable('vendor_profile_images')) {
                Log::warning('vendor_profile_images table missing — setting empty images relation');
                $profile->setRelation('images', collect());
            }
        } catch (\Throwable $e) {
            // ignore schema checks if they fail
        }
        $districts = District::orderBy('name')->get(['id','name']);
        $categories = [
            'Handloom & Textiles',
            'Embroidery & Needlework',
            'Pottery & Terracotta',
            'Woodcraft & Bamboo',
            'Metal & Brassware',
            'Jewelry & Ornaments',
            'Painting & Folk Art',
            'Leather Craft',
            'Food Heritage',
            'Festive & Ritual Items',
        ];
        $user = $req->user();
        // list any files present in storage/app/public/debug/uploads for quick attach
        $debugUploads = [];
        try {
            $files = \Illuminate\Support\Facades\Storage::disk('public')->files('debug/uploads');
            // only include filenames
            $debugUploads = array_values(array_map(fn($f)=>$f, $files));
        } catch (\Throwable $e) {
            $debugUploads = [];
        }
        return view('vendor.setup', compact('profile','districts','categories','user','debugUploads'));
    }

    public function setupSave(Request $req)
    {
        // Early quick log so we know the request reached this method (before validation can redirect)
        try {
            Log::info('Vendor setupSave invoked', [
                'user_id' => optional($req->user())->id,
                'files_keys' => array_keys($req->files->all()),
                'post_keys' => array_keys($req->all()),
                'method' => $req->method()
            ]);
            // also log raw PHP $_FILES keys for low-level inspection
            try { Log::info('_FILES', ['keys'=>array_keys($_FILES)]); } catch (\Throwable$e) {}
        } catch (\Throwable $e) {
            // don't break the flow if logging fails
        }

        // Validate user fields first so owner name/email persist even if vendor-specific validation fails
        try {
            $userValidator = Validator::make($req->only('user_name','user_email'), [
                'user_name'  => 'nullable|string|max:120',
                'user_email' => 'nullable|email',
            ]);
            $userValidator->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            try { Log::info('Vendor setupSave - user validation failed', ['errors'=>$e->errors(),'input'=>$req->only('user_name','user_email')]); } catch (\Throwable$logE) {}
            // if user data is invalid, stop early and show errors (do not save)
            throw $e;
        }

        // Save user fields early so they persist regardless of vendor/profile validation
        $uEarly = $req->user();
        try {
            if ($req->filled('user_name')) $uEarly->name = $req->user_name;
            if ($req->filled('user_email')) $uEarly->email = $req->user_email;
            $uEarly->save();
            try { Log::info('Vendor setupSave - user saved early', ['id'=>optional($uEarly)->id,'name'=>optional($uEarly)->name,'email'=>optional($uEarly)->email]); } catch (\Throwable$logE) {}
        } catch (\Throwable $e) {
            Log::error('Vendor setupSave - early user save failed', ['error'=>$e->getMessage(),'id'=>optional($uEarly)->id]);
        }

        // Now validate vendor/profile fields (exclude user_* keys)
        try {
            $req->validate([
                'shop_name'       => 'required|max:120',
                'support_email'   => 'nullable|email',
                'support_phone'   => 'nullable|max:32',
                'district_id'     => 'nullable|exists:districts,id',
                'vendor_category' => 'required|string|max:120',
                'address'         => 'nullable|max:255',
                'description'     => 'nullable|max:2000',
                'heritage_story'  => 'nullable|max:3000',
                'shop_logo'       => 'nullable|image|max:2048',
                'banner'          => 'nullable|image|max:4096',
                'gallery.*'       => 'nullable|image|max:4096',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            try { Log::info('Vendor setupSave - validation failed', ['errors'=>$e->errors(),'input'=> $req->all()]); } catch (\Throwable$logE) {}
            throw $e;
        }

        $p = VendorProfile::where('user_id',$req->user()->id)->firstOrFail();

        // user fields were already validated & saved early above; log that state here
        try {
            $u = $req->user();
            Log::info('Vendor setupSave - user already saved early', ['id'=>optional($u)->id,'name'=>optional($u)->name,'email'=>optional($u)->email]);
        } catch (\Throwable $e) {}

        if ($req->hasFile('shop_logo')) {
            $p->shop_logo_path = Storage::disk('public')->putFile('vendors/logos', $req->file('shop_logo'));
        }
        if ($req->hasFile('banner')) {
            $p->banner_path = Storage::disk('public')->putFile('vendors/banners', $req->file('banner'));
        }

        // handle gallery uploads (multiple) - verbose debug
        $galleryDebug = ['received'=>0,'saved'=>[], 'errors'=>[],'files_keys'=>array_keys($req->files->all())];
        Log::info('Gallery debugging - request file keys: '.json_encode($galleryDebug['files_keys']));
        if ($req->hasFile('gallery')) {
            $files = $req->file('gallery');
            $galleryDebug['received'] = is_array($files) ? count($files) : 1;
            Log::info('Vendor gallery upload count: '.$galleryDebug['received']);
            foreach ($files as $idx => $file) {
                // record some immediate file metadata
                $galleryDebug['files_meta'][$idx] = ['clientName'=>$file->getClientOriginalName(),'size'=>$file->getSize(),'mime'=>$file->getClientMimeType()];
                try {
                    $path = Storage::disk('public')->putFile('vendors/gallery', $file);
                    $rec = \App\Models\VendorProfileImage::create([
                        'vendor_profile_id' => $p->id,
                        'path' => $path,
                    ]);
                    $galleryDebug['saved'][] = ['idx'=>$idx,'path'=>$path,'id'=>$rec->id];
                    Log::info('Saved vendor gallery file', ['idx'=>$idx,'path'=>$path,'vendor_profile_id'=>$p->id]);
                } catch (\Throwable $e) {
                    $galleryDebug['errors'][] = ['idx'=>$idx,'msg'=>$e->getMessage()];
                    Log::error('Failed saving vendor gallery file', ['idx'=>$idx,'error'=>$e->getMessage()]);
                }
            }
        }

        // If client-side pre-uploaded files were sent as JSON paths, attach them too
        if ($req->filled('uploaded_gallery_paths')) {
            try {
                $paths = json_decode($req->uploaded_gallery_paths, true) ?: [];
                foreach ($paths as $p) {
                    $rec = \App\Models\VendorProfileImage::create([
                        'vendor_profile_id' => $p->vendor_profile_id ?? $p['vendor_profile_id'] ?? $p,
                        'path' => $p,
                    ]);
                    $galleryDebug['saved'][] = ['path'=>$p,'id'=>$rec->id];
                }
            } catch (\Throwable $e) {
                Log::error('Failed attaching uploaded_gallery_paths: '.$e->getMessage());
            }
        }

        $p->fill($req->only([
            'shop_name','support_email','support_phone','district_id','vendor_category',
            'address','description','heritage_story'
        ]));

        if (!$p->slug) $p->slug = Str::slug($req->shop_name).'-'.Str::random(4);

        $p->save();

        // Attach debug info to the session so the view can show immediate feedback
        if (!empty($galleryDebug)) {
            return back()->with(['status'=>'Store profile saved.','gallery_debug'=>$galleryDebug]);
        }

        return back()->with('status','Store profile saved.');
    }

    public function payoutForm(Request $req)
    {
        $latest = VendorPayoutAccount::where('user_id',$req->user()->id)->latest()->first();
        return view('vendor.payout', compact('latest'));
    }

    public function payoutSave(Request $req)
    {
        $req->validate([
            'method'      => 'required|in:bkash,nagad,bank',
            'account_no'  => 'nullable|max:100',
            'account_name'=> 'nullable|max:120',
            'bank_name'   => 'nullable|max:120',
            'branch'      => 'nullable|max:120',
            'routing_no'  => 'nullable|max:50',
            'doc'         => 'nullable|file|max:4096',
        ]);

        $doc = $req->hasFile('doc')
            ? Storage::disk('public')->putFile('vendors/kyc', $req->file('doc'))
            : null;

        VendorPayoutAccount::create([
            'user_id'      => $req->user()->id,
            'method'       => $req->method,
            'account_no'   => $req->account_no,
            'account_name' => $req->account_name,
            'bank_name'    => $req->bank_name,
            'branch'       => $req->branch,
            'routing_no'   => $req->routing_no,
            'doc_path'     => $doc,
            'status'       => 'pending',
        ]);

        return back()->with('status','Payout info submitted (pending verification).');
    }

    // quick helper: attach existing files in storage/app/public/debug/uploads to the vendor profile
    public function attachDebugUploads(Request $req)
    {
        // log incoming attempt for debugging
        try {
            Log::info('attachDebugUploads invoked', ['user_id'=>optional($req->user())->id,'paths'=>$req->input('paths')]);
        } catch (\Throwable $e) {}
        $req->validate(['paths' => 'required|array']);
        $profile = VendorProfile::where('user_id',$req->user()->id)->firstOrFail();
        $added = [];
        foreach ($req->paths as $p) {
            // sanitize path
            $p = trim($p);
            if (!$p) continue;
            // only allow debug/uploads path for safety
            if (strpos($p, 'debug/uploads/') !== 0) continue;
            $rec = \App\Models\VendorProfileImage::create(['vendor_profile_id'=>$profile->id,'path'=>$p]);
            $added[] = $rec->id;
        }
        return back()->with('status','Attached '.count($added).' debug images.');
    }

    // Simple GET form that lists debug/uploads and provides a plain POST form to attach selected files.
    public function attachDebugForm(Request $req)
    {
        $files = [];
        try { $files = Storage::disk('public')->files('debug/uploads'); } catch(\Throwable$e) {}
        return view('vendor.attach_debug_form', ['debugUploads'=>$files]);
    }

    public function removeGalleryImage(Request $req, \App\Models\VendorProfileImage $image)
    {
        $profile = VendorProfile::where('user_id',$req->user()->id)->firstOrFail();
        if ($image->vendor_profile_id !== $profile->id) return back()->with('status','Unauthorized');
        // delete file from storage if exists
        try { \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path); } catch(\Throwable$e){}
        $image->delete();
        return back()->with('status','Image removed.');
    }
}
