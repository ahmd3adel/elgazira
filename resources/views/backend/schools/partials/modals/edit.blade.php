
<div class="modal fade" id="editSchoolModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> تعديل المدرسة
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editSchoolForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم المدرسة <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                        <div class="invalid-feedback name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>نوع المدرسة <span class="text-danger">*</span></label>
                        <select name="type" id="edit_type" class="form-control" required>
                            <option value="">اختر النوع</option>
                            <option value="ابتدائي">ابتدائي</option>
                            <option value="اعدادي">اعدادي</option>
                            <option value="حضانة">حضانة</option>
                            <option value="تعليم مجتمعي">تعليم مجتمعي</option>
                        </select>
                        <div class="invalid-feedback type-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>القسم/المنطقة <span class="text-danger">*</span></label>
                        <select name="department_id" id="edit_department_id" class="form-control" required>
                            <option value="">اختر القسم</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback department_id-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>العنوان</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback address-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> تحديث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>