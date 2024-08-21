<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Widget content --}}
        <div class="p-4 bg-white rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Top Selling Products</h2>
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Product Name</th>
                        <th class="py-2 px-4 border-b">Total Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topSellingProducts as $product)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $product['produk_name'] }}</td>
                        <td class="py-2 px-4 border-b">{{ $product['total_quantity'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>