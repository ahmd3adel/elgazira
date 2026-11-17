<!-- إضافة حاوية أكثر تحكماً -->
<div class="table-container" style="position: relative; width: 100%; overflow-x: auto;">
    <div class="table-responsive w-100">
        <table id="warehouses-table" class="table table-bordered table-striped" style="width: 100%; min-width: 800px;">
            <thead>
                <tr>
                    <th style="min-width: 50px;">#</th>
                    <th style="min-width: 150px;">اسم المخزن</th>
                    <th style="min-width: 100px;">كود المخزن</th>
                    <th style="min-width: 150px;">التبعية</th>
                    <th style="min-width: 120px;">المسؤول</th>
                    <th style="min-width: 120px;">الهاتف</th>
                    <th style="min-width: 80px;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                {{-- البيانات تُحمل عبر Ajax --}}
            </tbody>
        </table>
    </div>
</div>

<style>
    /* CSS إضافي لحل مشاكل المحاذاة */
    #warehouses-table {
        width: 100% !important;
        margin: 0 !important;
    }
    
    #warehouses-table thead th,
    #warehouses-table tbody td {
        white-space: nowrap;
    }
    
    /* تحسين التمرير */
    .table-container {
        margin: 20px 0;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    
    /* إصلاح مشكلة ارتفاع الصفوف */
    #warehouses-table tbody tr {
        height: auto !important;
    }
    
    /* تحسين العرض للشاشات الصغيرة */
    @media (max-width: 768px) {
        #warehouses-table thead th,
        #warehouses-table tbody td {
            font-size: 12px;
            padding: 8px 4px;
        }
    }
    
    /* منع تداخل الأعمدة */
    .dataTables_scrollHeadInner,
    .dataTables_scrollBody {
        width: 100% !important;
    }
    
    .dataTables_scrollBody table {
        width: 100% !important;
    }
</style>