<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-[#0f2d52]">{{ $delivery->delivery_number }}</h2>
                <p class="text-sm text-slate-500">
                    Order: {{ $delivery->order->order_number }} ·
                    Supplier: {{ $delivery->supplier->name }}
                </p>
            </div>
            @php $statusInfo = \App\Models\Delivery::STATUSES[$delivery->status] ?? ['label'=>$delivery->status,'color'=>'bg-gray-100']; @endphp
            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusInfo['color'] }}">
                {{ $statusInfo['label'] }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Delivery Summary -->
            <div class="bg-white rounded-2xl shadow p-6 grid md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-slate-500 mb-1">Date Received</p>
                    <p class="font-bold">{{ $delivery->delivered_at?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Received By</p>
                    <p class="font-bold">{{ $delivery->received_by }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Total Items</p>
                    <p class="font-bold">{{ $delivery->items->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Damaged Items</p>
                    <p class="font-bold {{ $delivery->items->sum('quantity_damaged') > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $delivery->items->sum('quantity_damaged') > 0
                            ? $delivery->items->sum('quantity_damaged') . ' unit(s)'
                            : 'None' }}
                    </p>
                </div>
                @if($delivery->notes)
                    <div class="md:col-span-4 bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-500 mb-1">Notes</p>
                        <p class="text-sm">{{ $delivery->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items Received -->
            <div class="bg-white rounded-2xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-[#0f2d52]">Items Received</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-slate-600 text-xs uppercase tracking-wide">
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Ordered</th>
                            <th class="px-5 py-3">Delivered</th>
                            <th class="px-5 py-3">Damaged</th>
                            <th class="px-5 py-3">Usable</th>
                            <th class="px-5 py-3">Condition</th>
                            <th class="px-5 py-3">Damage Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($delivery->items as $item)
                            @php
                                $condInfo = \App\Models\DeliveryItem::CONDITIONS[$item->condition] ?? ['label'=>$item->condition,'color'=>'bg-gray-100 text-gray-700'];
                                $usable = $item->quantity_delivered - $item->quantity_damaged;
                            @endphp
                            <tr class="{{ $item->quantity_damaged > 0 ? 'bg-red-50' : '' }}">
                                <td class="px-5 py-3 font-semibold">{{ $item->item_name }}</td>
                                <td class="px-5 py-3 text-slate-500">
                                    {{ \App\Models\Supplier::CATEGORIES[$item->category] ?? $item->category }}
                                </td>
                                <td class="px-5 py-3">{{ $item->quantity_ordered }} {{ $item->unit }}</td>
                                <td class="px-5 py-3 font-semibold text-green-700">{{ $item->quantity_delivered }} {{ $item->unit }}</td>
                                <td class="px-5 py-3 font-semibold {{ $item->quantity_damaged > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                    {{ $item->quantity_damaged > 0 ? $item->quantity_damaged . ' ' . $item->unit : '—' }}
                                </td>
                                <td class="px-5 py-3 font-bold text-[#0f2d52]">{{ $usable }} {{ $item->unit }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $condInfo['color'] }}">
                                        {{ $condInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs">
                                    {{ $item->damage_notes ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Damage Summary Alert -->
            @if($delivery->items->where('quantity_damaged', '>', 0)->count())
                <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h4 class="font-bold text-red-700">Damage Report</h4>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-red-600 text-xs">
                                <th class="pb-2">Item</th>
                                <th class="pb-2">Damaged Qty</th>
                                <th class="pb-2">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($delivery->items->where('quantity_damaged', '>', 0) as $item)
                                <tr>
                                    <td class="py-1 font-semibold text-red-800">{{ $item->item_name }}</td>
                                    <td class="py-1 text-red-700">{{ $item->quantity_damaged }} {{ $item->unit }}</td>
                                    <td class="py-1 text-red-600">{{ $item->damage_notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <a href="{{ route('deliveries.index') }}" class="inline-block text-[#0f2d52] font-semibold hover:underline text-sm">
                ← Back to Deliveries
            </a>

        </div>
    </div>
</x-app-layout>