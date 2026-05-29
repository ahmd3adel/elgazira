<!-- Modal: إضافة إذن استلام جديد -->
<div class="modal fade" id="addReceivingOrderModal" tabindex="-1" aria-labelledby="addReceivingOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addReceivingOrderModalLabel">
                    <i class="fas fa-truck"></i> إضافة إذن استلام جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="receivingForm" method="POST" action="{{ route('admin.receiving_orders.store') }}">
                @csrf
                
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="container-fluid">

                        <!-- الصف الأول: رقم الإذن + المورد -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="document_number" class="form-label">
                                    رقم الإذن <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="document_number" 
                                       id="document_number" 
                                       class="form-control" 
                                       placeholder="مثال: PO-2026-001"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label">
                                    المورد <span class="text-danger">*</span>
                                </label>
                                <select name="supplier_id" id="supplier_id" class="form-control" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- الصف الثاني: المخزن + المنتج -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">
                                    المخزن <span class="text-danger">*</span>
                                </label>
                                <select name="warehouse_id" id="warehouse_id" class="form-select form-control" required>
                                    <option value="">-- اختر المخزن --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="product_id" class="form-label">
                                    المنتج <span class="text-danger">*</span>
                                </label>
                                <select name="product_id" id="product_id" class="form-select form-control" required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- الصف الثالث: رقم التشغيلة + الكمية الأساسية -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="batch_number" class="form-label">
                                    رقم التشغيلة (Batch No.) <span class="text-danger"></span>
                                </label>
                                <input type="text" 
                                       name="batch_number" 
                                       id="batch_number" 
                                       class="form-control" 
                                       placeholder="أدخل رقم التشغيلة"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="quantity" class="form-label">
                                    الكمية الأساسية (بالكرتونة) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity" 
                                       class="form-control" 
                                       value="600" 
                                       min="1" 
                                       required>
                            </div>
                        </div>

                        <!-- الصف الرابع: عدد العينات + وقت الوصول -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="samples_quantity" class="form-label">
                                    عدد العينات
                                </label>
                                <input type="number" 
                                       name="samples_quantity" 
                                       id="samples_quantity" 
                                       class="form-control" 
                                       value="0" 
                                       min="0">
                            </div>

                            <div class="col-md-6">
                                <label for="arrival_time" class="form-label">
                                    وقت الوصول
                                </label>
                                <input type="datetime-local" 
                                       name="arrival_time" 
                                       id="arrival_time" 
                                       class="form-control">
                            </div>
                        </div>

                        <!-- الصف الخامس: وقت المغادرة + الملاحظات -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="departure_time" class="form-label">
                                    وقت المغادرة
                                </label>
                                <input type="datetime-local" 
                                       name="departure_time" 
                                       id="departure_time" 
                                       class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="notes" class="form-label">
                                    ملاحظات (رقم الخطة)
                                </label>
                                <input type="text" 
                                       name="notes" 
                                       id="notes" 
                                       class="form-control" 
                                       placeholder="مثال: 1 من 1 من خطة 23-04-2026">
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> حفظ الشحنة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>