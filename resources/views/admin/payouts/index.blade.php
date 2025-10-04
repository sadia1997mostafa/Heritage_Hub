@extends('layouts.admin')

@section('content')
<style>
:root {
  --bg:        #F5EFE6;
  --ink:       #2A1C14;
  --muted:     #7B6A5B;
  --brand:     #6B4E3D;
  --brand-600: #5A3E2B;
  --accent:    #D9A441;
  --terracotta:#B67352;
  --shadow: 0 12px 38px rgba(42,28,20,.12);
}

body {
  background: var(--bg);
  color: var(--ink);
  font-family: 'Poppins', sans-serif;
}

h1 {
  color: var(--brand);
  text-align: center;
  margin-bottom: 1rem;
  letter-spacing: .5px;
}

.table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: var(--shadow);
  padding: 1.5rem;
  margin-bottom: 2rem;
  transition: transform .3s ease;
}

.table-wrapper:hover {
  transform: translateY(-2px);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: .5rem;
}

th {
  background: var(--brand);
  color: #fff;
  text-align: left;
  padding: 12px;
  font-weight: 600;
  border-top-left-radius: 6px;
  border-top-right-radius: 6px;
}

td {
  border-bottom: 1px solid #e8e2d8;
  padding: 10px;
  vertical-align: top;
  color: var(--ink);
}

tr:hover td {
  background: #faf8f4;
}

button {
  background: var(--brand);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  cursor: pointer;
  transition: background .3s;
}

button:hover {
  background: var(--brand-600);
}

.btn-danger {
  background: var(--terracotta);
}

.btn-danger:hover {
  background: #9f5a3b;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-pending {
  background: var(--accent);
  color: #fff;
}

.status-verified {
  background: #4CAF50;
  color: white;
}

.status-rejected {
  background: var(--terracotta);
  color: white;
}
</style>

<h1 class="text-2xl font-bold mb-4">💰 Payout Approvals</h1>

@if(session('status'))
  <div style="background:#e8f5e9; color:#2e7d32; padding:10px 15px; border-radius:8px; margin-bottom:20px;">
    {{ session('status') }}
  </div>
@endif

{{-- ======= Pending ======= --}}
<div class="table-wrapper">
  <h2>⏳ Pending ({{ $pending->total() }})</h2>
  <table>
    <tr><th>User</th><th>Method</th><th>Account</th><th>Proof</th><th>Actions</th></tr>
    @forelse($pending as $p)
      <tr>
        <td>{{ $p->user->email }}</td>
        <td><span class="status-badge status-pending">{{ strtoupper($p->method) }}</span></td>
        <td>
          <b>{{ $p->account_name }}</b><br>{{ $p->account_no }}
          @if($p->bank_name)
            <div style="color:var(--muted); font-size:0.85rem;">
              {{ $p->bank_name }} / {{ $p->branch }} / {{ $p->routing_no }}
            </div>
          @endif
        </td>
        <td>
          @if($p->doc_path)
            <a href="{{ asset('storage/'.$p->doc_path) }}" target="_blank" style="color:var(--brand); text-decoration:underline;">View</a>
          @else
            <span style="color:var(--muted)">—</span>
          @endif
        </td>
        <td>
          <form method="POST" action="{{ route('admin.payouts.approve',$p->id) }}" style="display:inline">@csrf
            <button>Approve</button>
          </form>
          <form method="POST" action="{{ route('admin.payouts.reject',$p->id) }}" style="display:inline">@csrf
            <button class="btn-danger">Reject</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" style="text-align:center; color:var(--muted)">No pending payouts.</td></tr>
    @endforelse
  </table>
</div>

{{-- ======= Verified ======= --}}
<div class="table-wrapper">
  <h2>✅ Verified</h2>
  <table>
    <tr><th>User</th><th>Method</th><th>Account</th></tr>
    @forelse($verified as $p)
      <tr>
        <td>{{ $p->user->email }}</td>
        <td><span class="status-badge status-verified">{{ strtoupper($p->method) }}</span></td>
        <td>{{ $p->account_name }} / {{ $p->account_no }}</td>
      </tr>
    @empty
      <tr><td colspan="3" style="text-align:center; color:var(--muted)">No verified payouts yet.</td></tr>
    @endforelse
  </table>
</div>

{{-- ======= Rejected ======= --}}
<div class="table-wrapper">
  <h2>❌ Rejected</h2>
  <table>
    <tr><th>User</th><th>Method</th><th>Account</th></tr>
    @forelse($rejected as $p)
      <tr>
        <td>{{ $p->user->email }}</td>
        <td><span class="status-badge status-rejected">{{ strtoupper($p->method) }}</span></td>
        <td>{{ $p->account_name }} / {{ $p->account_no }}</td>
      </tr>
    @empty
      <tr><td colspan="3" style="text-align:center; color:var(--muted)">No rejected payouts.</td></tr>
    @endforelse
  </table>
</div>

@endsection
