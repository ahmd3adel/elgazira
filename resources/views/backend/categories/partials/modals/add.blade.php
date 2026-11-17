<div class="modal fade" id="addProfessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle ml-2"></i> إضافة مهنة رئيسية جديدة
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addProfessionForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المهنة (بالعربي) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name_ar" name="name_ar" required>
                                <small class="text-muted">مثال: تقنية المعلومات</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المهنة (English)</label>
                                <input type="text" class="form-control" id="name_en" name="name_en">
                                <small class="text-muted">مثال: Information Technology</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الرابط المخصص (عربي)</label>
                                <input type="text" class="form-control" id="slug_ar" name="slug_ar">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الرابط المخصص (English)</label>
                                <input type="text" class="form-control" id="slug_en" name="slug_en">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-sitemap"></i> القسم الأب</label>
                        <select class="form-control select2-parent" id="parent_id" name="parent_id">
                            <option value="">-- قسم رئيسي (بدون أب) --</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>الحالة</label>
                                <select class="form-control" name="is_active">
                                    <option value="1" selected>نشط</option>
                                    <option value="0">معطل</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ترتيب العرض</label>
                                <input type="number" class="form-control" name="order" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>لون الأيقونة</label>
                                <input type="color" class="form-control" name="icon_color" value="#0066cc">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>اختر أيقونة المهنة</label>
                        <div class="row icon-selector border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;"></div>
                        <input type="hidden" id="selectedIcon" name="icon" value="fa-briefcase">
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success px-4" id="btnSaveProfession">حفظ المهنة</button>
            </div>
        </div>
    </div>
</div>