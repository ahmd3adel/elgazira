<div class="modal fade" id="addWarehouseModal" tabindex="-1" role="dialog" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addWarehouseModalLabel">
                    <i class="fas fa-warehouse ml-2"></i> إضافة مخزن جديد
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
<form id="addWarehouseForm" method="POST" action="{{ route('admin.warehouses.store') }}">
    @csrf
    <!-- محتوى الـ Row كما هو مع بعض التحسينات أدناه -->
                    <div class="row">
                        <!-- نوع المخزن -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>نوع المخزن <span class="text-danger">*</span></label>
                                <select name="type" id="warehouse_type" class="form-control select2" style="width: 100%;">
                                    <option value="main" selected>رئيسي</option>
                                    <option value="sub">فرعي</option>
                                    <option value="dispatch_point">نقطة توزيع</option>
                                </select>
                            </div>
                        </div>

                        <!-- اسم المخزن -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">اسم المخزن <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="مثال: مخزن المنزلة" required>
                            </div>
                        </div>

                        <!-- المحافظة (تظهر فقط لو النوع رئيسي) -->
                        <div class="col-md-6" id="gov_group">
                            <div class="form-group">
                                <label>المحافظة التابع لها <span class="text-danger">*</span></label>
                                <select name="governorate_id" id="governorate_id" class="form-control select2" style="width: 100%;">
                                    <option value="">اختر المحافظة...</option>
                                    @foreach($governorates as $gov)
                                        <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- المخزن الأب (يظهر لو النوع فرعي أو نقطة توزيع) -->
                        <div class="col-md-6" id="parent_group" style="display: none;">
                            <div class="form-group">
                                <label>المخزن الرئيسي التابع له <span class="text-danger">*</span></label>
                                <select name="parent_id" id="parent_id" class="form-control select2" style="width: 100%;">
                                    <option value="">اختر المخزن الرئيسي...</option>
                                    @foreach($mainWarehouses as $main)
                                        <option value="{{ $main->id }}">{{ $main->name }} ({{ $main->governorate->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- كود المخزن -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">كود المخزن <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control" placeholder="مثال: 101" required>
                            </div>
                        </div>

                        <!-- اسم المسؤول -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_name">اسم المسؤول</label>
                                <input type="text" name="manager_name" id="manager_name" class="form-control" placeholder="اسم الشخص المسؤول">
                            </div>
                        </div>

                        <!-- رقم هاتف المسؤول -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_phone">رقم هاتف المسؤول</label>
                                <input type="text" name="manager_phone" id="manager_phone" class="form-control" placeholder="010xxxxxxx">
                            </div>
                        </div>

                        <!-- الحالة -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">الحالة</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="1" selected>نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>

                        <!-- العنوان -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">العنوان التفصيلي</label>
                                <textarea name="address" id="address" class="form-control" rows="2" placeholder="العنوان بالكامل..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success px-4" id="btnSaveWarehouse">
                    <i class="fas fa-save ml-1"></i> حفظ البيانات
                </button>
            </div>
        </div>
    </div>
</div>