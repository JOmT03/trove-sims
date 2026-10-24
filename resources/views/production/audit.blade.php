<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Production Audit</h2></x-slot>

    <div class="ar-wrap">
        <div class="ar-head">
            <div>
                <h1>Production Audit</h1>
                <p>Every production run, with the recipe-expected usage checked against the actual remaining stock. A variance means more or less was consumed than the recipe allows.</p>
            </div>
            <a href="{{ route('production.create') }}" class="ar-newlink">+ Record Production</a>
        </div>

        <div class="ar-stats">
            <div class="ar-stat"><div class="l">Runs shown</div><div class="v">{{ $runs->count() }}</div></div>
            <div class="ar-stat {{ $flagged ? 'bad' : 'ok' }}"><div class="l">With variance</div><div class="v">{{ $flagged }}</div></div>
        </div>

        @if ($runs->isEmpty())
            <div class="ar-empty">No production runs recorded yet.</div>
        @else
            @foreach ($runs as $run)
                <div class="ar-card {{ $run->has_variance ? 'flag' : '' }}">
                    <div class="ar-card-top">
                        <div class="ar-title">
                            <span class="ar-qty">{{ $run->quantity }}&times;</span> {{ $run->product_name }}
                            @if ($run->has_variance)<span class="ar-badge">VARIANCE</span>@else<span class="ar-badge ok">OK</span>@endif
                        </div>
                        <div class="ar-meta">
                            {{ $run->produced_by_name ?: 'Unknown' }} &middot; {{ $run->created_at?->format('M d, Y g:i A') }}
                        </div>
                    </div>
                    @if ($run->note)<div class="ar-note">{{ $run->note }}</div>@endif

                    <table class="ar-table">
                        <thead><tr><th>Ingredient</th><th>Before</th><th>Expected use</th><th>Actual after</th><th>Actually used</th><th>Variance</th></tr></thead>
                        <tbody>
                            @foreach (($run->items ?? []) as $it)
                                @php $v = (float) ($it['variance'] ?? 0); $flagRow = abs($v) >= 0.01; @endphp
                                <tr class="{{ $flagRow ? 'ar-bad' : '' }}">
                                    <td>{{ $it['name'] ?? '' }}</td>
                                    <td>{{ rtrim(rtrim(number_format((float)($it['before'] ?? 0), 2), '0'), '.') }} {{ $it['unit'] ?? '' }}</td>
                                    <td>{{ rtrim(rtrim(number_format((float)($it['expected'] ?? 0), 2), '0'), '.') }} {{ $it['unit'] ?? '' }}</td>
                                    <td>{{ rtrim(rtrim(number_format((float)($it['after'] ?? 0), 2), '0'), '.') }} {{ $it['unit'] ?? '' }}</td>
                                    <td>{{ rtrim(rtrim(number_format((float)($it['used'] ?? 0), 2), '0'), '.') }} {{ $it['unit'] ?? '' }}</td>
                                    <td class="ar-var">
                                        @if ($flagRow)
                                            <strong>{{ $v > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($v, 2), '0'), '.') }}</strong>
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif
    </div>

    <style>
        .ar-wrap{max-width:1000px;margin:0 auto;padding:24px 16px 48px;font-family:'Plus Jakarta Sans',system-ui,Arial,sans-serif;color:#2E1C10;}
        .ar-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
        .ar-head h1{margin:0 0 4px;font-size:22px;font-weight:800;}
        .ar-head p{margin:0;color:#8A7460;font-size:13px;max-width:640px;}
        .ar-newlink{background:#D9782C;color:#fff;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;white-space:nowrap;}
        .ar-stats{display:flex;gap:12px;margin-bottom:18px;}
        .ar-stat{background:#fff;border:1px solid #EDE0D0;border-radius:12px;padding:12px 18px;min-width:120px;}
        .ar-stat .l{font-size:10.5px;text-transform:uppercase;letter-spacing:.3px;color:#8A7460;font-weight:700;}
        .ar-stat .v{font-size:24px;font-weight:800;}
        .ar-stat.bad .v{color:#C0392B;}
        .ar-stat.ok .v{color:#2E7D32;}
        .ar-empty{background:#fff;border:1px solid #EDE0D0;border-radius:14px;padding:40px;text-align:center;color:#8A7460;}
        .ar-card{background:#fff;border:1px solid #EDE0D0;border-radius:14px;padding:16px;margin-bottom:14px;}
        .ar-card.flag{border-color:#F0B3A8;box-shadow:inset 3px 0 0 #C0392B;}
        .ar-card-top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;}
        .ar-title{font-size:16px;font-weight:800;}
        .ar-qty{color:#D9782C;}
        .ar-badge{font-size:10px;font-weight:800;letter-spacing:.4px;padding:3px 8px;border-radius:999px;margin-left:6px;background:#FBE9E7;color:#C0392B;vertical-align:middle;}
        .ar-badge.ok{background:#EAF6EC;color:#2E7D32;}
        .ar-meta{font-size:12px;color:#8A7460;}
        .ar-note{font-size:12.5px;color:#6B5744;background:#FDF6EC;border-radius:8px;padding:7px 10px;margin-bottom:10px;}
        .ar-table{width:100%;border-collapse:collapse;font-size:12.5px;}
        .ar-table th{text-align:right;font-size:10px;text-transform:uppercase;letter-spacing:.3px;color:#8A7460;padding:8px;background:#FDF6EC;border-bottom:1px solid #EDE0D0;}
        .ar-table th:first-child{text-align:left;}
        .ar-table td{padding:8px;border-bottom:1px solid #F3EADF;text-align:right;font-variant-numeric:tabular-nums;}
        .ar-table td:first-child{text-align:left;font-weight:600;}
        .ar-table tr.ar-bad td{background:#FBE9E7;}
        .ar-var strong{color:#C0392B;}
        .ar-table tr:last-child td{border-bottom:none;}
    </style>
</x-app-layout>