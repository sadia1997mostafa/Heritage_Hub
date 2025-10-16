<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::where('status','pending')->latest()->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update(['status'=>'approved']);
        return redirect()->back()->with('success','Approved');
    }

    public function hide(Review $review)
    {
        $review->update(['status'=>'hidden']);
        return redirect()->back()->with('success','Hidden');
    }
}
