<div class="table-responsive w-100">
<table id="distribution-orders-table" class="table table-bordered table-striped nowrap text-center">
    <thead class="bg-dark text-white">
        <tr>
            <th>#</th>
            <th>التاريخ</th>
            <th>المدرسة</th>
            <th>الإدارة</th>
            
            {{-- أعمدة ديناميكية لكل صنف --}}
            @foreach($products as $product)
                <th class="bg-secondary">{{ $product->name }}</th>
            @endforeach

            <th class="bg-primary">إجمالي الصرف</th>
            <th>المسؤول</th>
            <th>العمليات</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
</div>