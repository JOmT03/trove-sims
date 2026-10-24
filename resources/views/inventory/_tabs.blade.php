@php
    $cur           = $current ?? 'active';
    $activeCount   = $activeCount   ?? \App\Models\Inventory::whereNull('archived_at')->count();
    $archivedCount = $archivedCount ?? \App\Models\Inventory::whereNotNull('archived_at')->count();
@endphp
<div class="seg">
    <a href="{{ route('inventory.index', ['view' => 'active']) }}" class="{{ $cur === 'active' ? 'on' : '' }}">Active <span class="count">{{ $activeCount }}</span></a>
    <a href="{{ route('inventory.index', ['view' => 'archived']) }}" class="{{ $cur === 'archived' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg>
        Archived <span class="count">{{ $archivedCount }}</span>
    </a>
    <a href="{{ route('production.create') }}" class="{{ $cur === 'production' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 21V9l5 3V9l5 3V5h6v16H4z"/></svg>
        Production
    </a>
</div>
