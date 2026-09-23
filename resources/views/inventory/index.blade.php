<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-extrabold text-[#0f2d52]">Inventory</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Summary Cards -->
            <div class="grid md:grid-cols-3 gap-4">

                <!-- Total Items -->
                <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-[#f0ad1f]">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Total Items</p>
                    <p class="text-3xl font-extrabold text-[#0f2d52]">
                        {{ $inventories->count() }}
                    </p>
                </div>

                <!-- Total On Hand -->
                <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Total On Hand</p>
                    <p class="text-3xl font-extrabold text-green-700">
                        {{ number_format($inventories->sum('quantity_on_hand'), 0) }}
                    </p>
                </div>

                <!-- Total Damaged -->
                <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Total Damaged</p>
                    <p class="text-3xl font-extrabold text-red-600">
                        {{ number_format($inventories->sum('quantity_damaged'), 0) }}
                    </p>
                </div>

            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left">Item</th>
                            <th class="px-5 py-3 text-left">Category</th>
                            <th class="px-5 py-3 text-left">Stock</th>
                            <th class="px-5 py-3 text-left">Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $item)
                            <tr class="border-t">
                                <td class="px-5 py-3">{{ $item->item_name }}</td>
                                <td class="px-5 py-3">{{ $item->category }}</td>
                                <td class="px-5 py-3">{{ $item->quantity_on_hand }}</td>
                                <td class="px-5 py-3">{{ $item->supplier->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-400">
                                    No inventory found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>