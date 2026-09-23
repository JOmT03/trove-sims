{{--
    resources/views/suppliers/index.blade.php
    FIXED: Actions now use icon buttons (eye, pencil, trash)
    Font: Syne + DM Sans (same as student portal reference)
--}}
<x-app-layout>
<x-slot name="header">Suppliers</x-slot>
<x-slot name="subheader">All registered construction material suppliers</x-slot>

<style>
    /* ── Toolbar ── */
    .toolbar{display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap}
    .search-wrap{position:relative;flex:1;min-width:200px;max-width:400px}
    .search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)}
    .search-input{width:100%;padding:9px 12px 9px 38px;border:1px solid var(--border);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;background:var(--white);outline:none;transition:border-color .2s}
    .search-input:focus{border-color:var(--gold)}
    .filter-select{padding:9px 14px;border:1px solid var(--border);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;background:var(--white);outline:none;cursor:pointer;color:var(--text)}

    /* ── Add Supplier button ── */
    .btn-add{background:var(--gold);color:var(--navy);font-family:'Syne',sans-serif;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s;margin-left:auto}
    .btn-add:hover{background:var(--gold-light)}

    /* ── Table ── */
    .table-card{background:var(--white);border-radius:14px;border:1px solid var(--border);overflow:hidden}
    .supplier-table{width:100%;border-collapse:collapse}
    .supplier-table th{text-align:left;font-family:'Syne',sans-serif;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;padding:14px 20px;background:#fafbfc;border-bottom:1px solid var(--border)}
    .supplier-table td{padding:15px 20px;font-size:14px;border-bottom:1px solid var(--border);vertical-align:middle}
    .supplier-table tr:last-child td{border-bottom:none}
    .supplier-table tbody tr:hover td{background:#fafcff}

    .sup-num{font-size:13px;color:var(--muted);font-weight:500}
    .sup-name{font-family:'Syne',sans-serif;font-weight:700;font-size:14px}
    .sup-email{font-size:12px;color:var(--muted);margin-top:2px}

    .category-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500;background:rgba(15,31,61,.07);color:var(--navy-mid)}

    /* ── ICON ACTION BUTTONS ── */
    .actions-cell{display:flex;gap:4px;align-items:center}
    .icon-btn{width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;flex-shrink:0}
    .icon-btn svg{width:15px;height:15px;display:block}

    /* View — navy ghost */
    .icon-btn-view{background:rgba(15,31,61,.07);color:var(--navy)}
    .icon-btn-view:hover{background:var(--navy);color:var(--white)}

    /* Edit — blue tint */
    .icon-btn-edit{background:rgba(59,130,246,.1);color:#2563eb}
    .icon-btn-edit:hover{background:#2563eb;color:var(--white)}

    /* Delete — red tint */
    .icon-btn-delete{background:rgba(239,68,68,.1);color:var(--red)}
    .icon-btn-delete:hover{background:var(--red);color:var(--white)}

    /* Tooltip */
    .icon-btn{position:relative}
    .icon-btn::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:11px;font-family:'DM Sans',sans-serif;white-space:nowrap;padding:3px 8px;border-radius:5px;pointer-events:none;opacity:0;transition:opacity .15s}
    .icon-btn:hover::after{opacity:1}

    /* Alert */
    .alert{padding:12px 20px;border-radius:10px;margin-bottom:16px;font-size:14px;font-family:'DM Sans',sans-serif}
    .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d}
    .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#dc2626}

    /* Empty state */
    .empty-state{text-align:center;padding:60px 32px;color:var(--muted);font-family:'DM Sans',sans-serif}
    .empty-state .big-icon{font-size:48px;margin-bottom:12px}
    .empty-state p{font-size:15px;margin-bottom:16px}
</style>

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">✗ {{ session('error') }}</div>
@endif

{{-- Toolbar --}}
<div class="toolbar">
    <div class="search-wrap">
        <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" id="searchInput" class="search-input" placeholder="Search suppliers by name, email...">
    </div>
    <select class="filter-select" id="categoryFilter">
        <option value="">All Categories</option>
        @foreach(\App\Models\Supplier::CATEGORIES as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </select>
    <a href="{{ route('suppliers.create') }}" class="btn-add">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Add Supplier
    </a>
</div>

{{-- Table --}}
<div class="table-card">
    @if($suppliers->count())
    <table class="supplier-table" id="supplierTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>Category</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $i => $supplier)
            <tr class="sup-row"
                data-name="{{ strtolower($supplier->name) }}"
                data-email="{{ strtolower($supplier->email) }}"
                data-category="{{ $supplier->category }}">
                <td class="sup-num">{{ $i + 1 }}</td>
                <td>
                    <div class="sup-name">{{ $supplier->name }}</div>
                    <div class="sup-email">{{ $supplier->email }}</div>
                </td>
                <td><span class="category-badge">{{ $supplier->category }}</span></td>
                <td style="color:var(--muted);font-size:13px">{{ $supplier->phone }}</td>
                <td style="color:var(--muted);font-size:13px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $supplier->address }}
                </td>
                <td>
                    <div class="actions-cell">
                        {{-- View icon --}}
                        <a href="{{ route('suppliers.show', $supplier) }}"
                           class="icon-btn icon-btn-view" data-tip="View">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>

                        {{-- Edit icon --}}
                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="icon-btn icon-btn-edit" data-tip="Edit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        {{-- Delete icon --}}
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                              style="display:inline"
                              onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn icon-btn-delete" data-tip="Delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="big-icon">🏗️</div>
        <p>No suppliers yet. Add your first supplier to get started.</p>
        <a href="{{ route('suppliers.create') }}" class="btn-add" style="display:inline-flex;margin:0 auto">+ Add Supplier</a>
    </div>
    @endif
</div>

<script>
    const searchInput    = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const rows           = document.querySelectorAll('.sup-row');

    function filterRows() {
        const q   = searchInput.value.toLowerCase();
        const cat = categoryFilter.value;
        rows.forEach(row => {
            const matchQ   = !q   || row.dataset.name.includes(q) || row.dataset.email.includes(q);
            const matchCat = !cat || row.dataset.category === cat;
            row.style.display = matchQ && matchCat ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    categoryFilter.addEventListener('change', filterRows);
</script>

</x-app-layout>