<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnRequest;

class ReturnRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin-only');
    }

    public function index()
    {
        $returns = ReturnRequest::latest()->paginate(20);
        return view('admin.returns.index', ['returns' => $returns]);
    }

    public function show(ReturnRequest $returnRequest)
    {
        return view('admin.returns.show', ['r' => $returnRequest]);
    }

    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $returnRequest->update(['status' => 'approved', 'admin_status' => 'approved', 'admin_notes' => $request->input('admin_notes'), 'admin_handled_at' => now()]);
        return redirect()->route('admin.returns.index')->with('success','Return approved');
    }

    public function decline(Request $request, ReturnRequest $returnRequest)
    {
        $returnRequest->update(['status' => 'declined', 'admin_status' => 'declined', 'admin_notes' => $request->input('admin_notes'), 'admin_handled_at' => now()]);
        return redirect()->route('admin.returns.index')->with('success','Return declined');
    }
}
