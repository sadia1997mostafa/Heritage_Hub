<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Storage;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $returns = ReturnRequest::where('user_id', $user->id)->latest()->paginate(10);
        return view('returns.index', ['returns' => $returns]);
    }

    public function create(Request $request)
    {
        // expects order_item_id optionally; if not provided, load user's recent order items
        $orderItemId = $request->query('order_item_id');
        $orderItems = [];
        if ($request->user()) {
            $orderItems = \App\Models\OrderItem::whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })->with('product')->latest()->take(25)->get();
        }

        return view('returns.create', ['order_item_id' => $orderItemId, 'orderItems' => $orderItems]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => 'required|integer',
            'reason' => 'required|string|max:2000',
            'photos.*' => 'nullable|image|max:5120'
        ]);

        // Ensure the order item exists and belongs to the current user
        $orderItem = \App\Models\OrderItem::find($data['order_item_id']);
        if (! $orderItem) {
            return back()->withErrors(['order_item_id' => 'Order item not found'])->withInput();
        }
        if ($orderItem->order && $orderItem->order->user_id !== $request->user()->id) {
            return back()->withErrors(['order_item_id' => 'You are not authorized to request a return for this item.'])->withInput();
        }

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('returns', 'public');
                $photos[] = $path;
            }
        }

        // Resolve the vendor id robustly. Prefer eager-loaded relation, fall back to product_id lookup,
        // and try reloading the relation if necessary. Log if still missing so we can debug sporadic 403s.
        $vendorId = optional($orderItem->product)->vendor_id;
        if (is_null($vendorId) && isset($orderItem->product_id) && $orderItem->product_id) {
            $product = \App\Models\Product::find($orderItem->product_id);
            $vendorId = $product->vendor_id ?? null;
        }
        if (is_null($vendorId)) {
            $orderItem->loadMissing('product');
            $vendorId = optional($orderItem->product)->vendor_id;
        }
        if (is_null($vendorId)) {
            logger()->warning('return request created without vendor_id', ['order_item_id' => $orderItem->id, 'user_id' => $request->user()->id]);
        }

        $ret = ReturnRequest::create([
            'order_item_id' => $data['order_item_id'],
            'user_id' => $request->user()->id,
            'vendor_id' => $vendorId,
            'reason' => $data['reason'],
            'photos' => $photos,
            'status' => 'pending'
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'id' => $ret->id, 'message' => 'Return request submitted'], 201);
        }

        return redirect()->route('returns.index')->with('success','Return request submitted');
    }
}
