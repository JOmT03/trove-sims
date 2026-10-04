<x-app-layout>
<x-slot name="header">Commissions</x-slot>
<x-slot name="subheader">Custom &amp; institutional cake orders</x-slot>

<style>
.top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
.tabs{display:flex;gap:8px;}
.tab{padding:8px 16px;border-radius:9px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid #EDE0D0;background:#fff;color:#8A7460;}
.tab.active{background:#4A2C17;color:#fff;border-color:#4A2C17;}
.tab .n{font-weight:800;}
.btn{padding:10px 18px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;}
.cc{background:#fff;border:1px solid #EDE0D0;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:18px;text-decoration:none;color:inherit;display:block;}
.cc:hover{box-shadow:0 4px 14px rgba(0,0,0,.1);}
.cc-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px;}
.cc-name{font-size:15px;font-weight:800;color:#2E1C10;}
.cc-type{font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap;}
.t-ind{background:#EDE6F5;color:#6D4C9F;} .t-inst{background:#E6ECF5;color:#3F5B8B;}
.cc-sub{font-size:12.5px;color:#8A7460;margin-bottom:12px;}
.cc-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;padding-top:12px;border-top:1px solid #EDE0D0;}
.stage{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;}
.s-Inquiry{background:#FBEBD6;color:#B45309;} .s-Confirmed{background:#E6ECF5;color:#3F5B8B;}
.s-Baking{background:#EDE6F5;color:#6D4C9F;} .s-Ready{background:#E7F3EA;color:#166534;} .s-Completed{background:#f3f4f6;color:#6b7280;}
.bal{font-weight:700;font-variant-numeric:tabular-nums;} .bal.unpaid{color:#C2410C;} .bal.paid{color:#166534;}
.empty{background:#fff;border:1px solid #EDE0D0;border-radius:14px;padding:50px 20px;text-align:center;color:#8A7460;}
</style>

<div style="max-width:1050px;margin:0 auto;">
    @if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif

    <div class="top">
        <div class="tabs">
            <a href="{{ route('commissions.index', ['type'=>'all']) }}" class="tab {{ $type==='all'?'active':'' }}">All <span class="n">{{ $counts['all'] }}</span></a>
            <a href="{{ route('commissions.index', ['type'=>'individual']) }}" class="tab {{ $type==='individual'?'active':'' }}">Individual <span class="n">{{ $counts['individual'] }}</span></a>
            <a href="{{ route('commissions.index', ['type'=>'institutional']) }}" class="tab {{ $type==='institutional'?'active':'' }}">Institutional <span class="n">{{ $counts['institutional'] }}</span></a>
        </div>
        @can('admin')<a href="{{ route('commissions.create') }}" class="btn btn-gold">+ New Commission</a>@endcan
    </div>

    @if($commissions->count())
    <div class="cards">
        @foreach($commissions as $c)
        <a href="{{ route('commissions.show', $c) }}" class="cc">
            <div class="cc-top">
                <div class="cc-name">{{ $c->customer_name }}</div>
                <span class="cc-type {{ $c->client_type==='institutional'?'t-inst':'t-ind' }}">{{ ucfirst($c->client_type) }}</span>
            </div>
            <div class="cc-sub">{{ $c->order_type ?: 'Custom cake' }}@if($c->needed_by_date) &middot; due {{ $c->needed_by_date->format('M d') }}@endif</div>
            <div class="cc-row">
                <span class="stage s-{{ $c->status }}">{{ $c->status }}</span>
                <span class="bal {{ $c->balance() > 0 ? 'unpaid':'paid' }}">{{ $c->balance() > 0 ? 'Balance &#8369;'.number_format($c->balance(),2) : 'Fully paid' }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="empty">
        <p style="margin-bottom:16px;">No {{ $type!=='all' ? $type : '' }} commissions yet.</p>
        @can('admin')<a href="{{ route('commissions.create') }}" class="btn btn-gold">+ Create First Commission</a>@endcan
    </div>
    @endif
</div>
</x-app-layout>