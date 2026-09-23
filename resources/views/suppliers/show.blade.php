<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supplier Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header -->
                <div class="p-6 bg-gradient-to-r from-orange-500 to-amber-500">
                    <div class="flex items-center">
                        <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center">
                            <span class="text-orange-600 font-bold text-xl">
                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="ml-4 text-white">
                            <h3 class="text-2xl font-bold">{{ $supplier->name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 mt-1">
                                {{ \App\Models\Supplier::CATEGORIES[$supplier->category] ?? 'Uncategorized' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:underline font-medium">
                                {{ $supplier->email }}
                            </a>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Phone</p>
                            <p class="font-medium text-gray-900">{{ $supplier->phone }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Address</p>
                        <p class="font-medium text-gray-900">{{ $supplier->address }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Created</p>
                            <p class="font-medium text-gray-900">{{ $supplier->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                            <p class="font-medium text-gray-900">{{ $supplier->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <a href="{{ route('suppliers.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to List
                    </a>
                    @if(auth()->user()->isAdmin())
                        <div class="flex space-x-2">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Edit
                            </a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
