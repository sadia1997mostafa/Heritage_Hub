@extends('layouts.vendor')

@section('title','Payout')

@section('content')
  <h1 class="text-2xl font-bold mb-4">Setup Payout</h1>
  <form method="POST" action="{{ route('vendor.payout.save') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <select name="method" class="form-control">
      <option value="bkash">bKash</option>
      <option value="nagad">Nagad</option>
      <option value="bank">Bank</option>
    </select>
    <input type="text" name="account_no" placeholder="Account No" class="form-control">
    <input type="text" name="account_name" placeholder="Account Name" class="form-control">
    <input type="file" name="doc" class="form-control">
    <button class="btn btn-primary">Save</button>
  </form>
@endsection
