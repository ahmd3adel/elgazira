<div class="modal fade" id="addGovernorateModal" tabindex="-1" role="dialog" aria-labelledby="addGovernorateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addGovernorateModalLabel">
                    <i class="fas fa-plus-circle ml-2"></i> إضافة محافظة جديدة
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addGovernorateForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">اسم المحافظة <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="مثال: الدقهلية" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">كود المحافظة <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control" placeholder="مثال: DK" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_name">اسم المسؤول</label>
                                <input type="text" name="manager_name" id="manager_name" class="form-control" placeholder="اسم الشخص المسؤول">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_phone">رقم هاتف المسؤول</label>
                                <input type="text" name="manager_phone" id="manager_phone" class="form-control" placeholder="010xxxxxxx">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="status">الحالة</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="1" selected>نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">ملاحظات</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success px-4" id="btnSaveGovernorate">
                    <i class="fas fa-save ml-1"></i> حفظ المحافظة
                </button>
            </div>
        </div>
    </div>
</div>