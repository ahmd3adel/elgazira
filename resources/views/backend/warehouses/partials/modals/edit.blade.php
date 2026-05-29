<div class="modal fade" id="editWarehouseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-edit ml-2"></i> تعديل بيانات المخزن</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editWarehouseForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_warehouse_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- النوع (قراءة فقط غالباً في التعديل لمنع مشاكل الشجرة) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>نوع المخزن</label>
                                <select name="type" id="edit_type" class="form-control select2" style="width: 100%;">
                                    <option value="main">رئيسي</option>
                                    <option value="sub">فرعي</option>
                                    <option value="dispatch_point">نقطة توزيع</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المخزن</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                        </div>

                        <!-- المحافظة -->
                        <div class="col-md-6 edit_gov_group">
                            <div class="form-group">
                                <label>المحافظة</label>
                                <select name="governorate_id" id="edit_governorate_id" class="form-control select2" style="width: 100%;">
                                    @foreach($governorates as $gov)
                                        <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- الأب -->
                        <div class="col-md-6 edit_parent_group" style="display:none;">
                            <div class="form-group">
                                <label>المخزن الرئيسي</label>
                                <select name="parent_id" id="edit_parent_id" class="form-control select2" style="width: 100%;">
                                    @foreach($mainWarehouses as $main)
                                        <option value="{{ $main->id }}">{{ $main->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>كود المخزن</label>
                                <input type="text" name="code" id="edit_code" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المسؤول</label>
                                <input type="text" name="manager_name" id="edit_manager_name" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="text" name="manager_phone" id="edit_manager_phone" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الحالة</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغاء</button>
                    <button type="submit" class="btn btn-info shadow">تحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>