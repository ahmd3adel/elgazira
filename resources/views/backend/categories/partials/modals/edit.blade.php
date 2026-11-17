<div class="modal fade" id="editProfessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit ml-2"></i> تعديل المهنة
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editProfessionForm">
                    @csrf @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <!-- نفس حقول الإضافة ولكن بمعرفات edit_ -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المهنة (بالعربي) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name_ar" name="name_ar" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المهنة (English)</label>
                                <input type="text" class="form-control" id="edit_name_en" name="name_en">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الرابط المخصص (عربي)</label>
                                <input type="text" class="form-control" id="edit_slug_ar" name="slug_ar">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الرابط المخصص (English)</label>
                                <input type="text" class="form-control" id="edit_slug_en" name="slug_en">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-sitemap"></i> القسم الأب</label>
                        <select class="form-control select2-parent-edit" id="edit_parent_id" name="parent_id">
                            <option value="">-- قسم رئيسي (بدون أب) --</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>الحالة</label>
                            <select class="form-control" id="edit_is_active" name="is_active">
                                <option value="1">نشط</option>
                                <option value="0">معطل</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>ترتيب العرض</label>
                            <input type="number" class="form-control" id="edit_order" name="order">
                        </div>
                        <div class="col-md-4">
                            <label>لون الأيقونة</label>
                            <input type="color" class="form-control" id="edit_icon_color" name="icon_color" value="#0066cc">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>اختر أيقونة المهنة</label>
                        <div class="row icon-selector-edit border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;"></div>
                        <input type="hidden" id="edit_selectedIcon" name="icon">
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary px-4" id="btnUpdateProfession">تحديث المهنة</button>
            </div>
        </div>
    </div>
</div>