@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
  <h2 class="text-xl font-bold mb-4">Payout Setup</h2>
  @if(session('status'))<div class="p-2 bg-green-100">{{ session('status') }}</div>@endif

  <form method="POST" action="{{ route('vendor.payout.save') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3"><label>Method</label>
      <select name="method" class="form-select" required>
        <option value="bkash">bKash</option>
        <option value="nagad">Nagad</option>
        <option value="bank">Bank</option>
      </select>
    </div>
    <div class="mb-3"><label>Account No</label><input name="account_no" class="form-control"></div>
    <div class="mb-3"><label>Account Name</label><input name="account_name" class="form-control"></div>
    <div class="mb-3"><label>Bank Name</label><input name="bank_name" class="form-control"></div>
    <div class="mb-3"><label>Branch</label><input name="branch" class="form-control"></div>
    <div class="mb-3"><label>Routing No</label><input name="routing_no" class="form-control"></div>
    <div class="mb-3"><label>Proof (optional)</label><input type="file" name="doc" class="form-control"></div>
    <button class="btn btn-primary">Save</button>
  </form>

  @isset($latest)
    <hr class="my-4"><p class="text-sm">Last submission: {{ $latest->method }} ({{ $latest->status }})</p>
  @endisset
</div>
@endsection
