<div class="table-responsive">
    <!-- أضفت كلاس text-nowrap لضمان عدم انكسار السطور وكلاس w-100 لتوسيع الجدول -->
    <table id="receiving-orders-table" class="table table-bordered table-striped text-nowrap w-100">
        <thead class="bg-light">
            <tr>
                <th width="30px">#</th>
                <th>التاريخ</th>
                <th>رقم الإذن</th>
                <th>المورد</th>
                <th>الصنف </th>
                <th>المخزن المستلم</th>
                <th>الكمية</th>
                
                <th>عينات</th>
                <th width="100px">العمليات</th>
            </tr>
        </thead>
        <tbody>
            <!-- سيتم ملء البيانات بواسطة DataTables AJAX -->
        </tbody>
        {{-- <tfoot>
           <tr>
                <th width="30px">#</th>
                <th>التاريخ</th>
                <th>رقم الإذن</th>
                <th>المورد</th>
                <th>الصنف </th>
                <th>المخزن المستلم</th>
                <th>الكمية</th>
                
                <th>عينات</th>
                <th width="100px">العمليات</th>
            </tr>
        </tfoot> --}}
    </table>
</div>