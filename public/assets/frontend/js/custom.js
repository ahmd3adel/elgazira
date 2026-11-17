
        AOS.init({ once: true, offset: 10, disable: 'mobile' }); // smoother on mobile

        // ---------- قاعدة بيانات الخبراء (SEO friendly) ----------
        let professionals = [
            { id: 1, name: "د. ليلى المنصور", category: "أطباء", city: "القاهرة", phone: "01001234567", specialty: "استشارية قلب وأوعية دموية", verified: true },
            { id: 2, name: "مهندس كريم عبدالله", category: "مهندسون", city: "الإسكندرية", phone: "01223344556", specialty: "هندسة معمارية وتراخيص", verified: true },
            { id: 3, name: "صيدلية الأمين", category: "صيدليات", city: "الجيزة", phone: "0223456789", specialty: "خدمات دوائية معتمدة", verified: true },
            { id: 4, name: "المحامية سارة حمدي", category: "محامون", city: "الرياض", phone: "0501234567", specialty: "قضايا تجارية وتحكيم", verified: true },
            { id: 5, name: "مكتب العدل للمحاسبة", category: "محاسبون", city: "دبي", phone: "0456789123", specialty: "تدقيق وضرائب دولية", verified: true },
            { id: 6, name: "أكاديمية تك للتقنية", category: "خبراء تقنية", city: "جدة", phone: "0567891234", specialty: "استشارات أمن سيبراني", verified: true },
            { id: 7, name: "د. نور الدين يوسف", category: "أطباء", city: "القاهرة", phone: "01098765432", specialty: "جراحة عظام ومفاصل", verified: true },
            { id: 8, name: "مهندسة رنا الخطيب", category: "مهندسون", city: "دبي", phone: "0554321789", specialty: "هندسة برمجيات واستشارات", verified: true },
            { id: 9, name: "استشارية تسويق هبة عادل", category: "استشاريون", city: "الرياض", phone: "0561122334", specialty: "تسويق رقمي واستراتيجيات", verified: true },
            { id: 10, name: "مكتب الخبراء المحاسبين", category: "محاسبون", city: "الجيزة", phone: "01002223344", specialty: "مراجعة وتقييم مالي", verified: true }
        ];
        let nextId = 11;
        let currentCategoryFilter = "";

        // DOM
        const searchName = document.getElementById('searchNameInput');
        const searchCity = document.getElementById('searchCitySelect');
        const searchSpecialty = document.getElementById('searchSpecialtyInput');
        const searchBtn = document.getElementById('searchExpertsBtn');
        const gridContainer = document.getElementById('expertsGridContainer');
        const resultBadge = document.getElementById('resultCountBadge');
        const catPills = document.querySelectorAll('.category-pill');
        const modalEl = document.getElementById('profileBuildModal');
        const profileModal = new bootstrap.Modal(modalEl);
        const openBtns = [document.getElementById('openProfileHeroBtn'), document.getElementById('openProfileNavBtn'), document.getElementById('buildProfileHeaderLink')];
        const saveProfileBtn = document.getElementById('saveProfileBtnModal');

        // Toast
        function showMessage(msg, isSuccess = true) {
            let existing = document.querySelector('.floating-toast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.className = 'floating-toast';
            toast.innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i> ${msg}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2800);
        }

        // Render مع تحسين SEO: نص بديل للصور
        function renderExperts() {
            let filtered = professionals.filter(prof => {
                let match = true;
                const nameVal = searchName.value.trim().toLowerCase();
                if (nameVal && !prof.name.toLowerCase().includes(nameVal) && !prof.specialty.toLowerCase().includes(nameVal)) match = false;
                const cityVal = searchCity.value;
                if (cityVal && prof.city !== cityVal) match = false;
                const specVal = searchSpecialty.value.trim().toLowerCase();
                if (specVal && !prof.specialty.toLowerCase().includes(specVal)) match = false;
                if (currentCategoryFilter && prof.category !== currentCategoryFilter) match = false;
                return match;
            });

            resultBadge.innerText = `${filtered.length} مختص موثوق`;
            if (filtered.length === 0) {
                gridContainer.innerHTML = `<div class="col-12 text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3 empty-state-icon"></i><p class="text-muted">لم نعثر على مختصين. كن أول من يضيف بروفايله الآن!</p><button class="btn btn-outline-primary rounded-pill mt-2" id="emptyStateAddBtn"><i class="fas fa-plus"></i> أضف بروفايلك</button></div>`;
                const emptyBtn = document.getElementById('emptyStateAddBtn');
                if (emptyBtn) emptyBtn.addEventListener('click', () => profileModal.show());
                return;
            }

            let cards = '';
            filtered.forEach(prof => {
                cards += `
                    <div class="col-md-6 col-lg-4">
                        <div class="expert-card h-100">
                            <div class="verified-badge"><i class="fas fa-badge-check"></i> موثّق</div>
                            <div class="expert-name">${escapeHtml(prof.name)}</div>
                            <div class="text-muted small mb-2"><i class="fas fa-tag"></i> ${prof.category} • ${escapeHtml(prof.specialty)}</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                <div><i class="fas fa-map-marker-alt text-primary"></i> ${prof.city}</div>
                                <div><i class="fas fa-phone-alt text-primary"></i> ${prof.phone}</div>
                            </div>
                            <button class="contact-btn-card ripple-effect" data-contact="${prof.phone}" data-name="${prof.name}"><i class="fas fa-comment-dots"></i> تواصل مع المختص</button>
                        </div>
                    </div>
                `;
            });
            gridContainer.innerHTML = cards;
            document.querySelectorAll('.contact-btn-card').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const phone = btn.getAttribute('data-contact');
                    const name = btn.getAttribute('data-name');
                    showMessage(`📞 تم إرسال طلب تواصل إلى ${name} (رقم آمن)`, true);
                });
            });
        }

        function escapeHtml(str) { return str.replace(/[&<>]/g, function(m){if(m === '&') return '&amp;'; if(m === '<') return '&lt;'; if(m === '>') return '&gt;'; return m;}); }

        function triggerSearch() {
            currentCategoryFilter = "";
            updateActivePill("");
            renderExperts();
            showMessage("🔍 عرض جميع المختصين حسب البحث", true);
        }

        function setCategoryFilter(cat) {
            if (currentCategoryFilter === cat) {
                currentCategoryFilter = "";
                updateActivePill("");
            } else {
                currentCategoryFilter = cat;
                updateActivePill(cat);
            }
            renderExperts();
            if (currentCategoryFilter) showMessage(`🏷️ تصفية حسب ${currentCategoryFilter}`, true);
            else showMessage(`✨ إلغاء التصفية - عرض الكل`, true);
        }

        function updateActivePill(activeCat) {
            catPills.forEach(pill => {
                const pillCat = pill.getAttribute('data-cat');
                if (pillCat === activeCat) pill.classList.add('active-category');
                else pill.classList.remove('active-category');
            });
        }

        // إضافة بروفايل جديد
        function addNewProfile() {
            const name = document.getElementById('profileName').value.trim();
            const category = document.getElementById('profileCategory').value;
            const city = document.getElementById('profileCity').value.trim();
            const phone = document.getElementById('profilePhone').value.trim();
            const bio = document.getElementById('profileBio').value.trim();

            if (!name || !city || !phone) {
                showMessage("⚠️ الاسم والمدينة ورقم الجوال مطلوبة", false);
                return;
            }
            const newSpecialty = bio.length ? bio : "خبير معتمد";
            const newProfile = {
                id: nextId++,
                name: name,
                category: category,
                city: city,
                phone: phone,
                specialty: newSpecialty,
                verified: true
            };
            professionals.unshift(newProfile);
            renderExperts();
            profileModal.hide();
            document.getElementById('profileName').value = '';
            document.getElementById('profileCity').value = '';
            document.getElementById('profilePhone').value = '';
            document.getElementById('profileBio').value = '';
            showMessage(`🎉 تم إضافة بروفايل ${name} بنجاح! سيظهر للعملاء فوراً.`, true);
            // scroll gently
            setTimeout(() => document.querySelector('.search-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
        }

        // Event bindings
        searchBtn.addEventListener('click', triggerSearch);
        [searchName, searchCity, searchSpecialty].forEach(inp => inp.addEventListener('keypress', (e) => { if (e.key === 'Enter') triggerSearch(); }));

        catPills.forEach(pill => {
            pill.addEventListener('click', () => {
                const cat = pill.getAttribute('data-cat');
                setCategoryFilter(cat);
            });
        });

        openBtns.forEach(btn => { if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); profileModal.show(); }); });
        saveProfileBtn.addEventListener('click', addNewProfile);

        // initial
        renderExperts();

        // إضافة class ripple ديناميكي
        document.querySelectorAll('.ripple-effect').forEach(el => {
            el.classList.add('ripple-effect');
        });
  