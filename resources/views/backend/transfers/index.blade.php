@extends('backend.app')
@section('title', 'إدارة التحويلات المخزنية')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التحويلات بين المخازن</li>
@endsection

@push('custom-css')
    <style>
        .card-title i {
            color: #007bff;
        }

        .item-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
        }

        .btn-remove {
            margin-top: 32px;
        }

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
                    <div class="alert alert-info balance-info" id="balance-info-box">
                        <i class="fas fa-info-circle ml-2"></i>
                        <strong>تنبيه هام:</strong> يجب أن تتساوى كمية <strong>الصنف الأساسي (سادة 40)</strong> مع
                        <strong>مجموع كميات الأصناف التامة</strong>.
                        <br>
                        <small class="text-muted">مثال: إذا أرسلت 100 كرتونة سادة 40، يجب أن ترسل 100 وجبة تامة موزعة على
                            الأصناف التامة.</small>
                        <br>
                        <small class="text-success font-weight-bold">
                            <i class="fas fa-check-circle ml-1"></i>
                            ملاحظة: عند التحويل إلى <strong>مخزن فرعي</strong>، يمكنك تحويل صنف واحد (سادة أو ويفر أو عجوة) دون شرط التوازن.
                        </small>
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
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}" data-type="{{ $w->type }}">
                                            {{ $w->name }} ({{ __($w->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>إلى (الوجهة) <span class="text-danger">*</span></label>
                                <select name="to_warehouse_id" class="form-control select2" required>
                                    <option value="">اختر الوجهة (مخزن أو مدرسة)</option>
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}" data-type="{{ $w->type }}">
                                            {{ $w->name }} ({{ __($w->type) }})
                                        </option>
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
                                            @foreach ($products as $p)
                                                <option value="{{ $p->id }}"
                                                    data-is-base="{{ $p->is_base ? 'true' : 'false' }}">
                                                    {{ $p->name }} @if ($p->is_base)
                                                        [أساسي]
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>الكمية (كرتونة)</label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input"
                                            min="1" required>
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
                                        <span
                                            class="badge {{ $transfer->type == 'permanent' ? 'badge-info' : 'badge-warning' }}">
                                            {{ $transfer->type == 'permanent' ? 'تحويل نهائي' : 'نقل عهدة' }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>

                                    {{-- عرض الأصناف والكميات --}}
                                    <td class="text-right">
                                        @if ($transfer->items && $transfer->items->count() > 0)
                                            <div style="min-width: 200px;">
                                                @foreach ($transfer->items as $item)
                                                    <div class="d-flex justify-content-between border-bottom py-1">
                                                        <span
                                                            class="font-weight-bold">{{ $item->product->name ?? 'منتج غير معروف' }}</span>
                                                        <span class="badge badge-primary">
                                                            {{ number_format($item->quantity) }}
                                                            <small>كرتونة</small>
                                                        </span>
                                                    </div>
                                                    {{-- ✅ badge العينات --}}
                                                    @if ($item->samples_deducted > 0)
                                                        <div class="text-warning small mt-1" style="font-size: 0.75rem;">
                                                            ⚠️ تم سحب {{ number_format($item->samples_deducted) }} من العينات
                                                        </div>
                                                    @endif
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
                        @if ($transfers->count() > 0)
                            <tfoot class="bg-light">
                                <tr class="font-weight-bold">
                                    <td colspan="5" class="text-center">الإجمالي العام</td>
                                    <td class="text-right">--</td>
                                    <td class="bg-info text-white">
                                        {{ number_format($transfers->sum(function ($t) {return $t->items->sum('quantity');})) }}
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
        window.stocksData = @json($stocks);
        window.productsData = @json($products->mapWithKeys(fn($p) => [$p->id => $p->name]));
    </script>
        <script>
        window.stocksData = @json($stocks);
        window.productsData = @json($products->mapWithKeys(fn($p) => [$p->id => $p->name]));
        window.subWarehouseTypes = ['sub']; // ✅ السطر الناقص
    </script>
    <script>
        $(document).ready(function() {
            let itemIdx = 1;
            let isConfirmed = false;

            // ✅ هل الوجهة مخزن فرعي؟
function isSubWarehouseTarget() {
    let selectedOption = $('select[name="to_warehouse_id"] option:selected');
    let type = selectedOption.data('type');
    if (!type) return false;
    let allowed = window.subWarehouseTypes || ['sub']; // ✅ fallback
    return allowed.includes(String(type));
}

            // ✅ إظهار/إخفاء تنبيه التوازن حسب نوع الوجهة
            function updateBalanceInfoVisibility() {
                if (isSubWarehouseTarget()) {
                    $('#balance-info-box').addClass('d-none');
                    $('#balance-status').addClass('d-none');
                } else {
                    $('#balance-info-box').removeClass('d-none');
                }
            }

            // دالة حساب التوازن
            function calculateBalance() {
                // ✅ لو الوجهة مخزن فرعي، نتخطى التحقق
                if (isSubWarehouseTarget()) {
                    $('#balance-status').addClass('d-none');
                    return true;
                }

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
                        statusDiv.removeClass('alert-success alert-danger').addClass(
                            'alert-warning balance-warning');
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

            // عرض الرصيد المتاح عند اختيار المخزن المصدر أو المنتج
            function showAvailableStock() {
                let fromWarehouse = $('select[name="from_warehouse_id"]').val();

                $('#stock-info').remove();

                if (!fromWarehouse) return;

                let warehouseStocks = window.stocksData[fromWarehouse] || {};
                let productNames = window.productsData || {};

                let rows = [];
                let totalMain = 0,
                    totalSamples = 0,
                    totalAll = 0;

                Object.keys(productNames).forEach(function(productId) {
                    let stock = warehouseStocks[productId] || {
                        main: 0,
                        samples: 0,
                        total: 0
                    };

                    let productName = productNames[productId] || ('منتج #' + productId);

                    totalMain += stock.main;
                    totalSamples += stock.samples;
                    totalAll += stock.total;

                    let mainClass = stock.main > 0 ? 'badge-primary' : 'badge-secondary';
                    let samplesClass = stock.samples > 0 ? 'badge-warning' : 'badge-secondary';
                    let totalClass = stock.total > 0 ? 'badge-success' : 'badge-danger';

                    rows.push(`
                        <tr>
                            <td class="text-right font-weight-bold">${productName}</td>
                            <td class="text-center"><span class="badge ${mainClass}">${stock.main}</span></td>
                            <td class="text-center"><span class="badge ${samplesClass}">${stock.samples}</span></td>
                            <td class="text-center"><span class="badge ${totalClass}">${stock.total}</span></td>
                        </tr>
                    `);
                });

                if (rows.length === 0) {
                    $('#items-container').before(`
                        <div id="stock-info" class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle ml-2"></i>
                            لا توجد منتجات مسجلة في النظام.
                        </div>
                    `);
                    return;
                }

                let html = `
                    <div id="stock-info" class="alert alert-secondary mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas fa-boxes ml-2"></i>
                                <strong>رصيد كل الأصناف المتاحة في المخزن المصدر:</strong>
                            </div>
                            <div>
                                <span class="badge badge-primary">${totalMain} أساسي</span>
                                <span class="badge badge-warning">${totalSamples} عينات</span>
                                <span class="badge badge-success">${totalAll} إجمالي</span>
                            </div>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="thead-light" style="position: sticky; top: 0; z-index: 2;">
                                    <tr>
                                        <th class="text-right">الصنف</th>
                                        <th class="text-center" style="width: 100px;">أساسي</th>
                                        <th class="text-center" style="width: 100px;">عينات</th>
                                        <th class="text-center" style="width: 100px;">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows.join('')}
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td class="text-right">الإجمالي</td>
                                        <td class="text-center">${totalMain}</td>
                                        <td class="text-center">${totalSamples}</td>
                                        <td class="text-center">${totalAll}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;

                $('#items-container').before(html);
            }

            // ربط الأحداث
            $(document).on('change', 'select[name="from_warehouse_id"]', showAvailableStock);
            $(document).on('change', 'select[name*="[product_id]"]', showAvailableStock);

            // ✅ ربط حدث تغيير الوجهة لإعادة حساب التوازن وإظهار/إخفاء التنبيه
            $(document).on('change', 'select[name="to_warehouse_id"]', function() {
                updateBalanceInfoVisibility();
                calculateBalance();
            });

            // إضافة صف صنف جديد
            $('.add-item-btn').click(function() {
                let newItem = `
                    <div class="row item-row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <select name="items[${itemIdx}][product_id]" class="form-control" required>
                                    <option value="">اختر المنتج</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}" data-is-base="{{ $p->is_base ? 'true' : 'false' }}">
                                            {{ $p->name }} @if ($p->is_base)[أساسي]@endif
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

            // ✅ جمع الأصناف
            function collectItems() {
                let items = [];
                $('.item-row').each(function(index) {
                    let productId = $(this).find('select[name*="[product_id]"]').val();
                    let quantity = parseInt($(this).find('input[name*="[quantity]"]').val()) || 0;
                    if (productId && quantity > 0) {
                        items.push({
                            product_id: productId,
                            quantity: quantity
                        });
                    }
                });
                return items;
            }

            // ✅ التحقق قبل الإرسال
            $('#transfer-form').on('submit', function(e) {
                e.preventDefault();
                let form = this;

                // لو المستخدم وافق بالفعل، نكمل
                if (isConfirmed) {
                    form.submit();
                    return;
                }

                let fromWarehouseId = $('select[name="from_warehouse_id"]').val();
                let toWarehouseId = $('select[name="to_warehouse_id"]').val();
                let items = collectItems();

                if (!fromWarehouseId) {
                    Swal.fire('تنبيه', 'اختر المخزن المصدر أولاً', 'warning');
                    return;
                }
                if (!toWarehouseId) {
                    Swal.fire('تنبيه', 'اختر الوجهة أولاً', 'warning');
                    return;
                }
                if (items.length === 0) {
                    Swal.fire('تنبيه', 'أضف صنف واحد على الأقل', 'warning');
                    return;
                }

                // ✅ لو مش مخزن فرعي، لازم يكون متوازن
                if (!isSubWarehouseTarget()) {
                    let hasBase = false;
                    let baseQty = 0;
                    let otherTotal = 0;
                    $('.item-row').each(function() {
                        let select = $(this).find('select[name*="[product_id]"]');
                        let qty = parseInt($(this).find('input[name*="[quantity]"]').val()) || 0;
                        if (select.val() && qty > 0) {
                            let isBase = select.find('option:selected').data('is-base') || false;
                            if (isBase) {
                                hasBase = true;
                                baseQty = qty;
                            } else {
                                otherTotal += qty;
                            }
                        }
                    });
                    if (!hasBase) {
                        Swal.fire('تنبيه', 'يجب إضافة الصنف الأساسي (سادة 40) لعملية التحويل', 'warning');
                        return;
                    }
                    if (baseQty !== otherTotal) {
                        Swal.fire('تنبيه', 'يجب أن تتساوى كمية الصنف الأساسي مع مجموع الأصناف التامة', 'warning');
                        return;
                    }
                }

                // AJAX check-stock
                $.ajax({
                    url: "{{ route('admin.transfers.check-stock') }}",
                    method: 'GET',
                    data: {
                        from_warehouse_id: fromWarehouseId,
                        items: items
                    },
                    success: function(response) {
                        let problems = response.items.filter(i => !i.is_available);
                        if (problems.length > 0) {
                            let msg = problems.map(p =>
                                `• ${p.product_name}: المتاح ${p.available}، المطلوب ${p.requested}`
                            ).join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'رصيد غير كافٍ',
                                html: `الأصناف التالية ليس لها رصيد كافٍ:<br><br>${msg}`,
                            });
                            return;
                        }

                        let needsSamples = response.items.filter(i => i.needs_samples > 0);
                        if (needsSamples.length > 0) {
                            let msg = needsSamples.map(p =>
                                `• ${p.product_name}: الرصيد الأساسي ${p.main} — سيتم سحب ${p.needs_samples} من العينات`
                            ).join('<br>');

                            Swal.fire({
                                icon: 'warning',
                                title: '⚠️ الرصيد الأساسي غير كافٍ',
                                html: `الأصناف التالية سيتم سحب جزء من العينات:<br><br>${msg}<br><br>هل توافق؟`,
                                showCancelButton: true,
                                confirmButtonText: 'نعم، وافق واحفظ',
                                cancelButtonText: 'إلغاء',
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#dc3545',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    if (!$('input[name="confirm_samples"]').length) {
                                        $('<input>').attr({
                                            type: 'hidden',
                                            name: 'confirm_samples',
                                            value: '1'
                                        }).appendTo(form);
                                    }
                                    isConfirmed = true;
                                    $(form).submit();
                                }
                            });
                            return;
                        }

                        // مفيش مشاكل → submit عادي
                        isConfirmed = true;
                        $(form).submit();
                    },
                    error: function(xhr) {
                        Swal.fire('خطأ', 'تعذر التحقق من الأرصدة. حاول مرة أخرى.', 'error');
                    }
                });
            });

            // تهيئة أولية
            updateBalanceInfoVisibility();
            calculateBalance();
        });
    </script>
@endpush