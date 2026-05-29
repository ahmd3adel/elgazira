<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="VeriTrust - منصة الخبراء الموثوقين، ابنِ بروفايلك المهني وازدد ظهوراً في محركات البحث. تواصل مع عملاء جدد بثقة.">
    <meta name="keywords" content="بروفايل مهني, خبراء, منصة توثيق, بناء سمعة, مختصون, استشارات">
    <meta name="author" content="VeriTrust">
    <title>VeriTrust | ابنِ بروفايلك - منصة الخبراء الموثوقين</title>
    <!-- Bootstrap 5 + RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome 6 (optimized) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Cairo + system fallback -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS lightweight -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="{{ asset('assets/frontend/css/custom.css') }}" rel="stylesheet">
</head>

<body>

    <!-- Header سلس وخفيف -->
    <div class="light-header">
        <div class="container py-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="logo-light">
                    <h2>VeriTrust</h2>
                    <span>منصة الخبراء الموثوقين</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="#" class="nav-link-custom text-decoration-none d-none d-sm-inline-block" id="buildProfileHeaderLink"><i class="fas fa-id-card"></i> ابنِ بروفايلك</a>
                    <button class="btn-outline-light-nav" id="openProfileNavBtn"><i class="fas fa-user-plus"></i> سجل بروفايلك</button>
                    <button class="btn-primary-sm"><i class="fas fa-sign-in-alt"></i> دخول</button>
                </div>
            </div>
        </div>
    </div>

    <main>
        <div class="container">
            <!-- Hero تبسيط مع SEO -->
            <div class="hero-section" data-aos="fade-up" data-aos-duration="500">
                <h1 class="hero-title">ابنِ بروفايلك المهني <i class="fas fa-chart-line" style="color:#0A5C9E;"></i></h1>
                <p class="hero-sub">سجل في منصة الخبراء الموثوقين، زِد ظهورك في محركات البحث، واستقبل العملاء بثقة.</p>
                <button class="btn-build-profile" id="openProfileHeroBtn"><i class="fas fa-edit me-2"></i> أنشئ بروفايلك مجاناً</button>
            </div>

            <!-- بطاقة بحث متطورة للموبايل وديسكتوب -->
            <div class="search-card p-4" data-aos="fade-up" data-aos-delay="80">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-search fa-lg" style="color:#0A5C9E;"></i>
                    <h5 class="mb-0 fw-bold">ابحث عن مختص معتمد</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchNameInput" placeholder="الاسم أو التخصص">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="searchCitySelect">
                            <option value="">كل المدن</option>
                            <option value="القاهرة">القاهرة</option>
                            <option value="الإسكندرية">الإسكندرية</option>
                            <option value="الجيزة">الجيزة</option>
                            <option value="الرياض">الرياض</option>
                            <option value="دبي">دبي</option>
                            <option value="جدة">جدة</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchSpecialtyInput" placeholder="التخصص الدقيق">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-search-primary text-white w-100" id="searchExpertsBtn"><i class="fas fa-sliders-h me-1"></i> بحث</button>
                    </div>
                </div>
                <!-- فلاتر سريعة - احترافية -->
                <div class="d-flex flex-wrap gap-2 mt-4 justify-content-start" id="quickCategoriesContainer">
                    <div class="category-pill" data-cat="أطباء"><i class="fas fa-stethoscope"></i> أطباء</div>
                    <div class="category-pill" data-cat="مهندسون"><i class="fas fa-drafting-compass"></i> مهندسون</div>
                    <div class="category-pill" data-cat="صيدليات"><i class="fas fa-prescription-bottle"></i> صيدليات</div>
                    <div class="category-pill" data-cat="محامون"><i class="fas fa-gavel"></i> محامون</div>
                    <div class="category-pill" data-cat="محاسبون"><i class="fas fa-chart-line"></i> محاسبون</div>
                    <div class="category-pill" data-cat="خبراء تقنية"><i class="fas fa-laptop-code"></i> تقنية</div>
                    <div class="category-pill" data-cat="استشاريون"><i class="fas fa-chalkboard-user"></i> استشاريون</div>
                </div>
            </div>

            <!-- نتائج الخبراء -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <h3 class="fw-bold fs-5 fs-md-4"><i class="fas fa-user-check" style="color:#0A5C9E;"></i> المختصون الموثوقون</h3>
                    <span class="badge bg-light text-dark rounded-pill px-3 py-2" id="resultCountBadge">0 نتيجة</span>
                </div>
                <div class="row g-4" id="expertsGridContainer">
                    <!-- cards injected -->
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center">
        <div class="container">
            <p class="small mb-0">© 2025 VeriTrust — بناء بروفايلات مهنية موثقة، تعرّف على عملاء جدد بسهولة.</p>
        </div>
    </footer>

    <!-- Modal إنشاء البروفايل (محسّن) -->
    <div class="modal fade" id="profileBuildModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-4" id="profileModalLabel"><i class="fas fa-id-card text-primary"></i> أضف بروفايلك الآن</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small">بياناتك ستظهر في منصة الخبراء لتصل إلى عملاء جدد.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم الكامل *</label>
                        <input type="text" class="form-control" id="profileName" placeholder="د. أحمد المنصوري">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المجال الرئيسي *</label>
                        <select class="form-select" id="profileCategory">
                            <option value="أطباء">أطباء</option>
                            <option value="مهندسون">مهندسون</option>
                            <option value="صيدليات">صيدليات</option>
                            <option value="محامون">محامون</option>
                            <option value="محاسبون">محاسبون</option>
                            <option value="خبراء تقنية">خبراء تقنية</option>
                            <option value="استشاريون">استشاريون</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المدينة *</label>
                        <input type="text" class="form-control" id="profileCity" placeholder="القاهرة، دبي، الرياض">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رقم الجوال (للتواصل) *</label>
                        <input type="tel" class="form-control" id="profilePhone" placeholder="05xxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الخبرة / التخصص الدقيق</label>
                        <textarea class="form-control" rows="2" id="profileBio" placeholder="استشاري قلب وأوعية دموية - 12 عاماً"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveProfileBtnModal"><i class="fas fa-save"></i> نشر البروفايل</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="{{ asset('assets/frontend/js/custom.js') }}"></script>
</body>
</html>