<x-app-layout>
<x-slot name="header">Branch Transfers</x-slot>
<x-slot name="subheader">Matina &rarr; Jacinto weekly batches &amp; returns reconciliation</x-slot>

<style>
.bt-card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;}
.bt-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;}
.btn{padding:10px 18px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.btn-gold{background:#D9782C;color:#fff;}
.alert{padding:12px 16px;border-radius:9px;font-size:13px;margin-bottom:16px;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8A7460;padding:12px 16px;background:#FDF6EC;}
th.r,td.r{text-align:right;}
td{padding:12px 16px;border-top:1px solid #EDE0D0;font-variant-numeric:tabular-nums;}
.badge{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;}
.badge.sent{background:#FBEBD6;color:#B45309;}
.badge.rec{background:#E7F3EA;color:#166534;}
.lnk{color:#4A2C17;font-weight:700;text-decoration:none;}
.lnk:hover{text-decoration:underline;}
.empty{padding:50px 20px;text-align:center;color:#8A7460;}
</style>

<div style="max-width:1050px;margin:0 auto;">
    <div class="bt-head">
        <div style="font-size:13px;color:#8A7460;">Each batch records what Matina sent and what Jacinto returned. The system computes <strong style="color:#2E1C10;">Net Sold = Sent - Returned</strong> &mdash; fully offline.</div>
        @can('admin')
        <a href="{{ route('branch-transfers.create') }}" class="btn btn-gold">+ New Batch Dispatch</a>
        @endcan
    </div>

    @if(session('success'))<div class="alert alert-ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-err">{{ session('error') }}</div>@endif

    <div class="bt-card">
        @if($batches->count())
        <table>
            <thead>
                <tr>
                    <th>Batch Date</th><th>Route</th><th class="r">Sent</th>
                    <th class="r">Returned</th><th class="r">Net Sold</th>
                    <th class="r">Revenue</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($batches as $batch)
                <tr>
                    <td>{{ $batch->batch_date->format('M d, Y') }}</td>
                    <td>{{ $batch->sourceSite->site_name ?? 'Matina' }} &rarr; {{ $batch->destinationSite->site_name ?? 'Jacinto' }}</td>
                    <td class="r">{{ $batch->totalSent() }}</td>
                    <td class="r" style="color:#C2410C;">{{ $batch->totalReturned() }}</td>
                    <td class="r" style="font-weight:700;">{{ $batch->totalNetSold() }}</td>
                    <td class="r">&#8369;{{ number_format($batch->totalRevenue(), 2) }}</td>
                    <td><span class="badge {{ $batch->status === 'reconciled' ? 'rec' : 'sent' }}">{{ ucfirst($batch->status) }}</span></td>
                    <td class="r"><a href="{{ route('branch-transfers.show', $batch) }}" class="lnk">View &rarr;</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty">
            <p style="margin-bottom:16px;">No batches yet.</p>
            @can('admin')<a href="{{ route('branch-transfers.create') }}" class="btn btn-gold">+ Create First Batch</a>@endcan
        </div>
        @endif
    </div>
</div>
</x-app-layout>