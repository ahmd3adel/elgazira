<div class="modal fade" id="editGovernorateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit ml-2"></i> تعديل بيانات المحافظة
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editGovernorateForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>اسم المحافظة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>كود المحافظة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_code" name="code" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المدير / المسؤول</label>
                                <input type="text" class="form-control" id="edit_manager_name" name="manager_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم هاتف التواصل</label>
                                <input type="text" class="form-control" id="edit_manager_phone" name="manager_phone">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الحالة</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="1">نشط</option>
                                    <option value="0">معطل</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ملاحظات</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary px-4" id="btnUpdateGovernorate">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</div>