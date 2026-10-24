<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Production Capacity</h2>
    </x-slot>

    <div class="pcap-wrap">
        <div class="pcap-head">
            <div>
                <h1>Production Capacity</h1>
                <p>How many of each product you can still produce with the raw materials currently in stock. The limiting ingredient is the one that runs out first.</p>
            </div>
            <form method="GET" class="pcap-search">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search product...">
                <button type="submit">Search</button>
            </form>
        </div>

        @if (count($rows) === 0)
            <div class="pcap-empty">No products found.</div>
        @else
            <div class="pcap-grid">
                @foreach ($rows as $row)
                    @php $p = $row['product']; $n = $row['producible']; @endphp
                    <div class="pcap-card">
                        <div class="pcap-card-top">
                            <div class="pcap-name">{{ $p->product_name }}</div>
                            @if ($p->category)<span class="pcap-cat">{{ $p->category }}</span>@endif
                        </div>

                        @if (empty($row['recipe']))
                            <div class="pcap-norecipe">No recipe defined. Add ingredients under Recipes to compute capacity.</div>
                        @else
                            <div class="pcap-figure {{ $n === 0 ? 'is-out' : ($n !== null && $n <= 5 ? 'is-low' : 'is-ok') }}">
                                <span class="pcap-num">{{ $n }}</span>
                                <span class="pcap-unit">can be made now</span>
                            </div>

                            @if ($row['limiting'])
                                <div class="pcap-limit">
                                    Limiting ingredient: <strong>{{ $row['limiting'] }}</strong>
                                </div>
                            @endif

                            <table class="pcap-recipe">
                                <thead>
                                    <tr><th>Ingredient</th><th>Needed / unit</th><th>Available</th><th>Can make</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($row['recipe'] as $ing)
                                        <tr class="{{ $ing['canMake'] !== null && $ing['canMake'] === $n ? 'pcap-bind' : '' }}">
                                            <td>{{ $ing['name'] }}</td>
                                            <td>{{ rtrim(rtrim(number_format($ing['needed'], 2), '0'), '.') }} {{ $ing['unit'] }}</td>
                                            <td>{{ rtrim(rtrim(number_format($ing['available'], 2), '0'), '.') }} {{ $ing['unit'] }}</td>
                                            <td>{{ $ing['canMake'] === null ? 'â€”' : $ing['canMake'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        .pcap-wrap { max-width: 1150px; margin: 0 auto; padding: 24px 16px 48px; font-family: 'Plus Jakarta Sans', system-ui, Arial, sans-serif; color: #2E1C10; }
        .pcap-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
        .pcap-head h1 { margin: 0 0 4px; font-size: 22px; font-weight: 800; }
        .pcap-head p { margin: 0; color: #8A7460; font-size: 13px; max-width: 620px; }
        .pcap-search { display: flex; gap: 8px; }
        .pcap-search input { border: 1px solid #EDE0D0; border-radius: 10px; padding: 9px 12px; font-size: 13px; outline: none; }
        .pcap-search input:focus { border-color: #D9782C; }
        .pcap-search button { background: #2E1C10; color: #fff; border: none; border-radius: 10px; padding: 9px 16px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .pcap-empty { background: #fff; border: 1px solid #EDE0D0; border-radius: 14px; padding: 40px; text-align: center; color: #8A7460; }
        .pcap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
        .pcap-card { background: #fff; border: 1px solid #EDE0D0; border-radius: 16px; padding: 16px 16px 10px; box-shadow: 0 1px 2px rgba(46,28,16,.04); }
        .pcap-card-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px; }
        .pcap-name { font-weight: 800; font-size: 16px; }
        .pcap-cat { background: #FBF2E6; color: #8A5A2B; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; padding: 4px 9px; border-radius: 999px; }
        .pcap-figure { display: flex; align-items: baseline; gap: 8px; padding: 10px 14px; border-radius: 12px; margin-bottom: 10px; }
        .pcap-figure.is-ok { background: #EAF6EC; }
        .pcap-figure.is-low { background: #FDF3E3; }
        .pcap-figure.is-out { background: #FBE9E7; }
        .pcap-num { font-size: 32px; font-weight: 800; line-height: 1; }
        .is-ok .pcap-num { color: #2E7D32; }
        .is-low .pcap-num { color: #C9771A; }
        .is-out .pcap-num { color: #C0392B; }
        .pcap-unit { font-size: 12px; color: #8A7460; font-weight: 600; }
        .pcap-limit { font-size: 12px; color: #8A7460; margin-bottom: 10px; }
        .pcap-limit strong { color: #C0392B; }
        .pcap-norecipe { background: #FAF6F0; border: 1px dashed #E3D3C0; border-radius: 12px; padding: 16px; font-size: 12.5px; color: #8A7460; text-align: center; }
        .pcap-recipe { width: 100%; border-collapse: collapse; font-size: 12px; }
        .pcap-recipe th { text-align: right; font-size: 10px; text-transform: uppercase; letter-spacing: .3px; color: #8A7460; padding: 7px 8px; border-bottom: 1px solid #EDE0D0; }
        .pcap-recipe th:first-child { text-align: left; }
        .pcap-recipe td { padding: 7px 8px; border-bottom: 1px solid #F3EADF; text-align: right; font-variant-numeric: tabular-nums; }
        .pcap-recipe td:first-child { text-align: left; font-weight: 600; }
        .pcap-recipe tr.pcap-bind td { background: #FFF6EC; }
        .pcap-recipe tr:last-child td { border-bottom: none; }
    </style>
</x-app-layout>