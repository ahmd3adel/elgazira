@extends('backend.app')
@section('title', 'إدارة التحويلات المخزنية')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التحويلات بين المخازن</li>
@endsection

@push('custom-css')
    <style>
        .card-title i { color: #007bff; }
        .item-row { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #dee2e6; }
        .btn-remove { margin-top: 32px; }
        
        .balance-warning {
            background-color: #fff3cd;
            border-right: 4px solid #ffc107;
        }
        .balance-info {
            background-color: #d1ecf1;
            border-right: 4px solid #17a2b8;
        }
        .balance-matched {
            background-color: #d4edda;
            border-right: 4px solid #28a745;
        }
        .balance-error {
            background-color: #f8d7da;
            border-right: 4px solid #dc3545;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        
        {{-- قسم إضافة تحويل جديد --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exchange-alt ml-2"></i>
                    إنشاء أمر تحويل جديد
                </h3>
            </div>
            
            <form action="{{ route('admin.transfers.store') }}" method="POST" id="transfer-form">
                @csrf
                <div class="card-body">
                    
                    {{-- تنبيه التوازن --}}
                    <div class="alert alert-info balance-info">
                        <i class="fas fa-info-circle ml-2"></i>
                        <strong>تنبيه هام:</strong> يجب أن تتساوى كمية <strong>الصنف الأساسي (سادة 40)</strong> مع <strong>مجموع كميات الأصناف التامة</strong>.
                        <br>
                        <small class="text-muted">مثال: إذا أرسلت 100 كرتونة سادة 40، يجب أن ترسل 100 وجبة تامة موزعة على الأصناف التامة.</small>
                    </div>
                    
                    {{-- إظهار حالة التوازن بشكل ديناميكي --}}
                    <div id="balance-status" class="alert alert-secondary d-none">
                        <i class="fas fa-balance-scale ml-2"></i>
                        <span id="balance-message">...</span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>من مخزن (المصدر) <span class="text-danger">*</span></label>
                                <select name="from_warehouse_id" class="form-control select2" required>
                                    <option value="">اختر المخزن</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ __($w->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>إلى (الوجهة) <span class="text-danger">*</span></label>
                                <select name="to_warehouse_id" class="form-control select2" required>
                                    <option value="">اختر الوجهة (مخزن أو مدرسة)</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ __($w->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>نوع التحويل <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="permanent">تحويل مخزني (تغيير رصيد نهائي)</option>
                                    <option value="custody">نقل عهدة (نقطة توزيع/مدرسة)</option>
                                </select>
                                <small class="text-muted">نقل العهدة لا يخصم من رصيد المخزن في التقرير الرسمي.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-list ml-1"></i> الأصناف والكميات</h5>
                        <div id="items-container">
                            <div class="row item-row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>الصنف</label>
                                        <select name="items[0][product_id]" class="form-control" required>
                                            <option value="">اختر المنتج</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-is-base="{{ $p->is_base ? 'true' : 'false' }}">
                                                    {{ $p->name }} @if($p->is_base)[أساسي]@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>الكمية (كرتونة)</label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-block add-item-btn">
                                        <i class="fas fa-plus"></i> إضافة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-left">
                    <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                        <i class="fas fa-exchange-alt"></i> حفظ وتنفيذ التحويل
                    </button>
                </div>
            </form>
        </div>

        {{-- قسم سجل التحويلات --}}
        <div class="card card-outline card-secondary mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history ml-2"></i>
                    سجل التحويلات الأخيرة
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center datatable">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>رقم العملية</th>
                                <th>من (المصدر)</th>
                                <th>إلى (الوجهة)</th>
                                <th>النوع</th>
                                <th>التاريخ</th>
                                <th>الأصناف والكميات</th>
                                <th>إجمالي الكميات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->transfer_number }}</td>
                                    <td>{{ $transfer->fromWarehouse->name ?? '---' }}</td>
                                    <td>{{ $transfer->toWarehouse->name ?? '---' }}</td>
                                    <td>
                                        <span class="badge {{ $transfer->type == 'permanent' ? 'badge-info' : 'badge-warning' }}">
                                            {{ $transfer->type == 'permanent' ? 'تحويل نهائي' : 'نقل عهدة' }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                                    
                                    {{-- عرض الأصناف والكميات --}}
                                    <td class="text-right">
                                        @if($transfer->items && $transfer->items->count() > 0)
                                            <div style="min-width: 200px;">
                                                @foreach($transfer->items as $item)
                                                    <div class="d-flex justify-content-between border-bottom py-1">
                                                        <span class="font-weight-bold">{{ $item->product->name ?? 'منتج غير معروف' }}</span>
                                                        <span class="badge badge-primary">
                                                            {{ number_format($item->quantity) }} 
                                                            <small>كرتونة</small>
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">لا توجد أصناف</span>
                                        @endif
                                    </td>
                                    
                                    {{-- إجمالي الكميات --}}
                                    <td class="font-weight-bold bg-light">
                                        {{ number_format($transfer->items->sum('quantity') ?? 0) }}
                                        <small class="text-muted">كرتونة</small>
                                    </td>
                                    
                                    <td><span class="badge badge-success">مكتمل</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">لا توجد تحويلات حتى الآن</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($transfers->count() > 0)
                        <tfoot class="bg-light">
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-center">الإجمالي العام</td>
                                <td class="text-right">--</td>
                                <td class="bg-info text-white">
                                    {{ number_format($transfers->sum(function($t) { return $t->items->sum('quantity'); })) }}
                                    <small class="text-white">كرتونة</small>
                                </td>
                                <td>--</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        let itemIdx = 1;
        
        // دالة حساب التوازن
        function calculateBalance() {
            let baseQuantity = 0;
            let otherTotal = 0;
            
            $('.item-row').each(function() {
                let select = $(this).find('select[name*="[product_id]"]');
                let quantityInput = $(this).find('input[name*="[quantity]"]');
                
                let productId = select.val();
                let quantity = parseInt(quantityInput.val()) || 0;
                
                if (productId && quantity > 0) {
                    let isBase = select.find('option:selected').data('is-base') || false;
                    
                    if (isBase) {
                        baseQuantity = quantity;
                    } else {
                        otherTotal += quantity;
                    }
                }
            });
            
            let statusDiv = $('#balance-status');
            let messageSpan = $('#balance-message');
            
            if (baseQuantity === 0 && otherTotal === 0) {
                statusDiv.addClass('d-none');
                return true;
            }
            
            statusDiv.removeClass('d-none');
            
            if (baseQuantity === otherTotal) {
                statusDiv.removeClass('alert-warning alert-danger').addClass('alert-success balance-matched');
                messageSpan.html(`
                    <strong>✓ متوازن:</strong> كمية الأساسي (${baseQuantity}) = مجموع الأصناف التامة (${otherTotal})
                `);
                return true;
            } else {
                let difference = Math.abs(baseQuantity - otherTotal);
                if (baseQuantity > otherTotal) {
                    statusDiv.removeClass('alert-success alert-danger').addClass('alert-warning balance-warning');
                    messageSpan.html(`
                        <strong>⚠️ غير متوازن:</strong> كمية الأساسي (${baseQuantity}) أكبر من مجموع الأصناف التامة (${otherTotal}) 
                        بفارق ${difference}
                    `);
                } else {
                    statusDiv.removeClass('alert-success alert-warning').addClass('alert-danger balance-error');
                    messageSpan.html(`
                        <strong>❌ غير متوازن:</strong> كمية الأساسي (${baseQuantity}) أقل من مجموع الأصناف التامة (${otherTotal}) 
                        بنقص ${difference}
                    `);
                }
                return false;
            }
        }
        
        // إضافة صف صنف جديد
        $('.add-item-btn').click(function() {
            let newItem = `
                <div class="row item-row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <select name="items[${itemIdx}][product_id]" class="form-control" required>
                                <option value="">اختر المنتج</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-is-base="{{ $p->is_base ? 'true' : 'false' }}">
                                        {{ $p->name }} @if($p->is_base)[أساسي]@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="number" name="items[${itemIdx}][quantity]" class="form-control quantity-input" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-block remove-item-btn">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>
                </div>`;
            $('#items-container').append(newItem);
            itemIdx++;
        });
        
        // حذف صف صنف وإعادة حساب التوازن
        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('.item-row').remove();
            calculateBalance();
        });
        
        // ربط أحداث التغيير لحساب التوازن
        $(document).on('change', 'select[name*="[product_id]"]', calculateBalance);
        $(document).on('input', '.quantity-input', calculateBalance);
        
        // التحقق قبل إرسال الفورم
        $('#transfer-form').on('submit', function(e) {
            if (!calculateBalance()) {
                e.preventDefault();
                // استخدام toastr إذا كان موجوداً
                if (typeof toastr !== 'undefined') {
                    toastr.error('⚠️ لا يمكن تنفيذ التحويل: كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                } else {
                    alert('⚠️ لا يمكن تنفيذ التحويل: كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                }
                return false;
            }
        });
        
        // تهيئة أولية
        calculateBalance();
    });
</script>
@endpush