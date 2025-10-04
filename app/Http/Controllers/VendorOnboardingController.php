<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Models\VendorPayoutAccount;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $logoPath = $req->file('shop_logo')->storePublicly('vendors/logos','public');
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
        return view('vendor.setup', compact('profile','districts','categories'));
    }

    public function setupSave(Request $req)
    {
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
        ]);

        $p = VendorProfile::where('user_id',$req->user()->id)->firstOrFail();

        if ($req->hasFile('shop_logo')) {
            $p->shop_logo_path = $req->file('shop_logo')->storePublicly('vendors/logos','public');
        }
        if ($req->hasFile('banner')) {
            $p->banner_path = $req->file('banner')->storePublicly('vendors/banners','public');
        }

        $p->fill($req->only([
            'shop_name','support_email','support_phone','district_id','vendor_category',
            'address','description','heritage_story'
        ]));

        if (!$p->slug) $p->slug = Str::slug($req->shop_name).'-'.Str::random(4);

        $p->save();

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
            ? $req->file('doc')->storePublicly('vendors/kyc','public')
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
}
