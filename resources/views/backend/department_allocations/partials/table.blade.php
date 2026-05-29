<div class="table-responsive">
    <table id="distribution_allocations_table" class="table table-bordered table-striped table-hover">
<thead>
    <tr>
        <th>#</th>
        <th>تاريخ الصرف</th>
        <th>الإدارة</th>
        <th>النوع</th>
        @foreach($products as $product)
            <th>{{ $product->name }}</th>
        @endforeach
        <th>إجمالي الصرف</th>
        <th>إجراءات</th>
    </tr>
</thead>
        <tbody>
            {{-- البيانات تملأ عن طريق DataTables --}}
        </tbody>
    </table>
</div>