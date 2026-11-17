@extends('backend.app')
@section('title', 'لوحة تحكم الأدمن')
@section('breadcrumb-title', 'لوحة تحكم الأدمن')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard v1</li>    
@endsection

@push('custom-css')
    {{-- أي CSS إضافي للـ Dashboard --}}
@endpush

@section('content')
    {{-- المحتوى الحالي كما هو --}}
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-chalkboard-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">إجمالي الخبراء</span>
                            <span class="info-box-number">1,234</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">إجمالي المستخدمين</span>
                            <span class="info-box-number">5,678</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">المواعيد المحجوزة</span>
                            <span class="info-box-number">892</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">التقييمات</span>
                            <span class="info-box-number">4,521</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Small boxes -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>234</h3>
                            <p>خبراء جدد هذا الشهر</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <a href="#" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>156</h3>
                            <p>استشارات مكتملة</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>42</h3>
                            <p>طلبات انتظار</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>8</h3>
                            <p>تقييمات سلبية</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-thumbs-down"></i>
                        </div>
                        <a href="#" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
                    </div>
                </div>
            </div>

            <!-- Chart and Quick Info -->
            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                إحصائيات المنصة
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="platformChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                توزيع الخبراء حسب التخصص
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="specialtiesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- باقي الجداول كما هي --}}
            <!-- Recent Experts Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">آخر الخبراء المنضمين</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 150px;">
                                    <input type="text" name="table_search" class="form-control float-right" placeholder="بحث">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>التخصص</th>
                                        <th>المدينة</th>
                                        <th>تاريخ الانضمام</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>د. أحمد المنصور</td>
                                        <td>أمراض القلب</td>
                                        <td>الرياض</td>
                                        <td>2024-01-15</td>
                                        <td><span class="badge badge-success">نشط</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    {{-- باقي الصفوف --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">آخر المواعيد المحجوزة</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العميل</th>
                                        <th>الخبير</th>
                                        <th>التخصص</th>
                                        <th>التاريخ</th>
                                        <th>الوقت</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- صفوف المواعيد --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">إضافة خبير جديد</h3>
                        </div>
                        <div class="card-body">
                            <p>أضف خبراء جدد إلى المنصة بسهولة من خلال نموذج إضافة خبير شامل.</p>
                            <button class="btn btn-primary btn-block">إضافة خبير <i class="fas fa-plus mr-1"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">إدارة التخصصات</h3>
                        </div>
                        <div class="card-body">
                            <p>أضف أو عدل التخصصات المهنية المتاحة على المنصة لتغطية جميع المجالات.</p>
                            <button class="btn btn-success btn-block">إدارة التخصصات <i class="fas fa-cog mr-1"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">إعدادات عامة</h3>
                        </div>
                        <div class="card-body">
                            <p>تحكم في إعدادات المنصة العامة، العناوين، وسائل التواصل، والمزيد.</p>
                            <button class="btn btn-warning btn-block">الإعدادات <i class="fas fa-sliders-h mr-1"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('custom-js')
    {{-- تحميل مكتبات الـ Charts --}}
    <script src="{{ asset('assets/backend/plugins/chart.js/Chart.min.js')}}"></script>
    
    {{-- كود الـ Charts الخاص بالصفحة --}}
    <script>
        $(document).ready(function() {
            // الرسم البياني الخطي (إحصائيات المنصة)
            var platformCtx = document.getElementById('platformChart').getContext('2d');
            var platformChart = new Chart(platformCtx, {
                type: 'line',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                    datasets: [{
                        label: 'المستخدمين الجدد',
                        data: [65, 78, 90, 120, 150, 180, 210, 240, 270, 300, 330, 360],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'الاستشارات المكتملة',
                        data: [45, 60, 75, 95, 120, 145, 170, 195, 220, 245, 270, 295],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            rtl: true
                        }
                    }
                }
            });
            
            // الرسم البياني الدائري (توزيع الخبراء حسب التخصص)
            var specialtiesCtx = document.getElementById('specialtiesChart').getContext('2d');
            var specialtiesChart = new Chart(specialtiesCtx, {
                type: 'pie',
                data: {
                    labels: ['الطب', 'الهندسة', 'المحاماة', 'التقنية', 'التعليم', 'المال والأعمال'],
                    datasets: [{
                        data: [35, 25, 15, 12, 8, 5],
                        backgroundColor: [
                            '#007bff',
                            '#28a745',
                            '#ffc107',
                            '#17a2b8',
                            '#6c757d',
                            '#dc3545'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            rtl: true
                        }
                    }
                }
            });
        });
    </script>
@endpush