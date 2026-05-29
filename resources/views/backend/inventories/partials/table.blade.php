<table id="inventoryMatrixTable" class="table table-bordered table-striped table-hover w-100 text-center">
    <thead class="bg-dark text-white">
        <!-- الصف الأول -->
        <tr>
            <th rowspan="2" style="vertical-align: middle; width: 180px;">المخازن</th>
            <th colspan="{{ $products->count() }}" class="text-center border-bottom">الأصناف والمنتجات (بالكرتونة)</th>
            <th rowspan="2" style="vertical-align: middle;" class="bg-info">إجمالي الرصيد</th>
            <th rowspan="2" style="vertical-align: middle;" class="bg-secondary">ميزان الوجبة (سادة vs تام)</th>
        </tr>
        <!-- الصف الثاني: أسماء المنتجات -->
        <tr>
            @foreach($products as $product)
                <th style="font-size: 0.85rem; min-width: 100px;">{{ $product->name }}</th>
            @endforeach
        </tr>
    </thead>
    
    <tbody>
        @foreach($warehouses as $warehouse)
            @php
                $totalInWarehouse = 0;
                $mainProductID = 1; // ID المنتج السادة 40جم
                $mainQty = $inventoryMap[$warehouse->id][$mainProductID] ?? 0;
                $otherProductsQty = 0;
            @endphp

            <tr>
                <!-- اسم المخزن -->
                <td class="text-right font-weight-bold">
                    {{ $warehouse->name }}
                    <span class="badge {{ $warehouse->type == 'main' ? 'badge-primary' : 'badge-secondary' }} float-left ml-2">
                        {{ $warehouse->type == 'main' ? 'رئيسي' : 'فرعي' }}
                    </span>
                </td>

                <!-- خلايا الأصناف -->
                @foreach($products as $product)
                    @php
                        $qty = $inventoryMap[$warehouse->id][$product->id] ?? 0;
                        $totalInWarehouse += $qty;
                        
                        // حساب إجمالي الأصناف التامة (كل شيء ماعدا السادة 40)
                        if($product->id != $mainProductID) {
                            $otherProductsQty += $qty;
                        }
                    @endphp
                    <td class="{{ $qty == 0 ? 'text-muted' : 'font-weight-bold text-primary' }}">
                        {{ $qty > 0 ? number_format($qty) : '-' }}
                    </td>
                @endforeach

                <!-- إجمالي الكمية في المخزن -->
                <td class="bg-light font-weight-bold">
                    {{ number_format($totalInWarehouse) }}
                </td>

                <!-- عمود الميزان الذكي -->
                @php
                    $balance = $mainQty - $otherProductsQty;
                @endphp
                <td style="background-color: #f4f6f9;">
                    @if(abs($balance) <= 5) {{-- سماحية 5 كراتين مثلاً --}}
                        <span class="badge badge-success px-3">✅ متوازن</span>
                    @elseif($balance > 0)
                        <div class="text-warning font-weight-bold" style="font-size: 0.8rem;">
                            ⚠️ فائض سادة (+{{ number_format($balance) }})
                            <br><small class="text-dark">يحتاج صنف تام</small>
                        </div>
                    @else
                        <div class="text-danger font-weight-bold" style="font-size: 0.8rem;">
                            🚨 نقص سادة ({{ number_format($balance) }})
                            <br><small class="text-dark">يحتاج سادة 40جم</small>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>

   <tfoot class="bg-light font-weight-bold text-dark">
    <tr>
        <td>إجمالي المنتج (كل المخازن)</td>
        @foreach($products as $product)
            <td class="text-primary">
                {{ number_format($productTotals[$product->id] ?? 0) }}
            </td>
        @endforeach
        <td class="bg-info text-white">
            {{ number_format(collect($productTotals)->sum()) }}
        </td>
        <td class="bg-secondary text-white">--</td>
    </tr>
</tfoot>
</table>