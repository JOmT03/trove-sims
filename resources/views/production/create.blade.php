<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Production</h2></x-slot>

    <div class="pr-wrap">
        <div class="pr-head">
            <div>
                <h1>Record Production</h1>
                <p>Producing a batch deducts raw materials by the active recipe. Enter the real counted remaining after production to audit for variance (possible theft or waste).</p>
            </div>
            <a href="{{ route('production.audit') }}" class="pr-auditlink">View Production Audit</a>
        </div>

        @if (session('error'))<div class="pr-alert err">{{ session('error') }}</div>@endif
        @if (session('success'))<div class="pr-alert ok">{{ session('success') }}</div>@endif

        @if (count($recipes) === 0)
            <div class="pr-empty">No products have an active recipe yet. Set a recipe under Products &rarr; Recipes first.</div>
        @else
            <form method="POST" action="{{ route('production.store') }}" class="pr-form">
                @csrf
                <div class="pr-controls">
                    <div class="pr-field">
                        <label>Product</label>
                        <select id="prodSelect" name="product_id" onchange="renderRecipe()" required>
                            <option value="">Select a product...</option>
                            @foreach ($recipes as $pid => $r)
                                <option value="{{ $pid }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pr-field pr-qty">
                        <label>Quantity to produce</label>
                        <input type="number" id="prodQty" name="quantity" min="1" step="1" value="1" oninput="renderRecipe()" required>
                    </div>
                </div>

                <div id="prodWarn" class="pr-warn" style="display:none;">Not enough stock for this quantity. Reduce the amount.</div>

                <div id="ingWrap"></div>

                <div class="pr-field">
                    <label>Note (optional)</label>
                    <input type="text" name="note" maxlength="255" placeholder="e.g. Morning batch, baker: Juan">
                </div>

                <button type="submit" id="prodSubmit" class="pr-submit" disabled>Record Production</button>
            </form>
        @endif
    </div>

    <style>
        .pr-wrap{max-width:980px;margin:0 auto;padding:24px 16px 48px;font-family:'Plus Jakarta Sans',system-ui,Arial,sans-serif;color:#2E1C10;}
        .pr-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
        .pr-head h1{margin:0 0 4px;font-size:22px;font-weight:800;}
        .pr-head p{margin:0;color:#8A7460;font-size:13px;max-width:620px;}
        .pr-auditlink{background:#2E1C10;color:#fff;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;white-space:nowrap;}
        .pr-alert{padding:12px 16px;border-radius:10px;font-size:13.5px;margin-bottom:16px;}
        .pr-alert.err{background:#FBE9E7;color:#B0341F;}
        .pr-alert.ok{background:#EAF6EC;color:#2E7D32;}
        .pr-empty{background:#fff;border:1px solid #EDE0D0;border-radius:14px;padding:40px;text-align:center;color:#8A7460;}
        .pr-form{background:#fff;border:1px solid #EDE0D0;border-radius:16px;padding:20px;}
        .pr-controls{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
        .pr-field{display:flex;flex-direction:column;gap:6px;flex:1;min-width:200px;}
        .pr-field.pr-qty{max-width:200px;}
        .pr-field label{font-size:12px;font-weight:700;color:#8A7460;text-transform:uppercase;letter-spacing:.3px;}
        .pr-field select,.pr-field input{border:1px solid #EDE0D0;border-radius:10px;padding:10px 12px;font-size:14px;outline:none;font-family:inherit;}
        .pr-field select:focus,.pr-field input:focus{border-color:#D9782C;}
        .pr-warn{background:#FDF3E3;color:#C9771A;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;font-weight:600;}
        .pr-max{font-size:13px;color:#8A7460;margin-bottom:10px;}
        .pr-max strong{color:#2E1C10;font-size:15px;}
        .pr-table{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:8px;}
        .pr-table th{text-align:right;font-size:10.5px;text-transform:uppercase;letter-spacing:.3px;color:#8A7460;padding:9px 8px;background:#FDF6EC;border-bottom:1px solid #EDE0D0;}
        .pr-table th:first-child{text-align:left;}
        .pr-table td{padding:8px;border-bottom:1px solid #F3EADF;text-align:right;font-variant-numeric:tabular-nums;}
        .pr-table td:first-child{text-align:left;font-weight:600;}
        .pr-table tr.pr-bad td{background:#FBE9E7;}
        .pr-actual{width:120px;border:1px solid #EDE0D0;border-radius:8px;padding:6px 8px;font-size:13px;text-align:right;outline:none;}
        .pr-actual:focus{border-color:#D9782C;}
        .pr-hint{font-size:12px;color:#8A7460;margin:6px 0 16px;}
        .pr-submit{background:#D9782C;color:#fff;border:none;border-radius:10px;padding:12px 22px;font-size:14px;font-weight:700;cursor:pointer;}
        .pr-submit:disabled{background:#E7C9AE;cursor:not-allowed;}
    </style>

    <script>
        const RECIPES = @json($recipes);
        function fmt(n){ n = Number(n) || 0; return (Math.round(n*100)/100).toString(); }
        function renderRecipe(){
            const pid = document.getElementById('prodSelect').value;
            const qty = parseInt(document.getElementById('prodQty').value, 10) || 0;
            const wrap = document.getElementById('ingWrap');
            const sub = document.getElementById('prodSubmit');
            const warn = document.getElementById('prodWarn');
            const r = RECIPES[pid];
            if (!r){ wrap.innerHTML = ''; sub.disabled = true; warn.style.display='none'; return; }

            let maxMake = null;
            r.items.forEach(function(it){ if (it.qty_used > 0){ const c = Math.floor(it.stock / it.qty_used); maxMake = (maxMake===null) ? c : Math.min(maxMake, c); } });

            let html = '<div class="pr-max">Max producible with current stock: <strong>' + (maxMake===null ? '-' : maxMake) + '</strong></div>';
            html += '<table class="pr-table"><thead><tr><th>Ingredient</th><th>Stock before</th><th>Per unit</th><th>Expected use</th><th>Expected after</th><th>Actual remaining</th></tr></thead><tbody>';
            r.items.forEach(function(it){
                const expected = Math.round(it.qty_used * qty * 100) / 100;
                const expAfter = Math.round((it.stock - expected) * 100) / 100;
                const low = expAfter < -0.0001;
                html += '<tr' + (low ? ' class="pr-bad"' : '') + '>' +
                    '<td>' + it.name + '</td>' +
                    '<td>' + fmt(it.stock) + ' ' + it.unit + '</td>' +
                    '<td>' + fmt(it.qty_used) + '</td>' +
                    '<td>' + fmt(expected) + ' ' + it.unit + '</td>' +
                    '<td>' + fmt(expAfter) + ' ' + it.unit + '</td>' +
                    '<td><input type="number" step="0.01" min="0" name="actual[' + it.inventory_id + ']" placeholder="' + fmt(expAfter) + '" class="pr-actual"></td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            html += '<p class="pr-hint">Leave <strong>Actual remaining</strong> blank to trust the recipe. Enter the real counted amount to audit for variance (theft / waste).</p>';
            wrap.innerHTML = html;

            const over = (maxMake !== null && qty > maxMake);
            warn.style.display = over ? 'block' : 'none';
            sub.disabled = !(pid && qty > 0 && !over);
        }
        document.addEventListener('DOMContentLoaded', renderRecipe);
    </script>
</x-app-layout>