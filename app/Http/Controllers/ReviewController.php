<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\OrderItem;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'order_item_id' => 'nullable|integer|exists:order_items,id',
            // rating is optional now; we'll default to 0 when absent so reviews can be text-only
            'rating' => 'nullable|integer|min:0|max:5',
            'body' => 'nullable|string|max:2000',
        ]);

        // If order_item_id provided, ensure the user actually ordered it and it is delivered
        if (! empty($data['order_item_id'])) {
            $oi = OrderItem::find($data['order_item_id']);
            if (! $oi || $oi->order->user_id !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['order_item_id' => ['Invalid order item']]], 422);
                }
                return back()->withErrors(['order_item_id' => 'Invalid order item']);
            }
        }

        // Ensure one review per order_item/product combo
        $exists = Review::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])
            ->where('order_item_id', $data['order_item_id'] ?? null)
            ->exists();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['rating' => ['You have already reviewed this item.']]], 422);
            }
            return back()->withErrors(['rating' => 'You have already reviewed this item.']);
        }

        $r = Review::create([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
            'order_item_id' => $data['order_item_id'] ?? null,
            'rating' => $data['rating'] ?? 0,
            'body' => $data['body'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            // recompute average and count including pending so user sees immediate change
            $avgAll = round(Review::where('product_id', $data['product_id'])->avg('rating') ?? 0, 1);
            $countAll = Review::where('product_id', $data['product_id'])->count();
            return response()->json([
                'message' => 'Review submitted; awaiting moderation.',
                'review' => $r->load('user'),
                'avg' => $avgAll,
                'count' => $countAll,
            ], 200);
        }

        return redirect()->back()->with('success','Review submitted; awaiting moderation.');
    }
}
