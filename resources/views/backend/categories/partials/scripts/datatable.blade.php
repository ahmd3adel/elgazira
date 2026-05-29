<script>

// قائمة الأيقونات (مشتركة)
const iconsList = [
    'fa-briefcase', 'fa-user-md', 'fa-laptop-code', 'fa-hammer', 'fa-graduation-cap',
    'fa-chart-line', 'fa-wrench', 'fa-paint-brush', 'fa-utensils', 'fa-building',
    'fa-chalkboard-user', 'fa-truck', 'fa-calculator', 'fa-stethoscope', 'fa-gavel',
    'fa-microphone', 'fa-camera', 'fa-heartbeat', 'fa-brain', 'fa-dumbbell',
    'fa-music', 'fa-code', 'fa-database', 'fa-cloud', 'fa-shield-alt', 'fa-robot',
    'fa-shopping-cart', 'fa-tasks', 'fa-users', 'fa-cogs', 'fa-chart-pie'
];

function cleanSlug(str) {
    if (!str) return '';
    return str.toString().trim().toLowerCase()
        .replace(/[^\u0600-\u06FF\u0750-\u077Fa-zA-Z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function renderIcons(container, selectedIcon = 'fa-briefcase') {
    let html = '';
    iconsList.forEach(icon => {
        html += `
            <div class="col-2 col-md-1 mb-2">
                <div class="icon-item ${icon === selectedIcon ? 'active' : ''}" data-icon="${icon}">
                    <i class="fas ${icon} fa-2x"></i>
                </div>
            </div>
        `;
    });
    $(container).html(html);
}

// تهيئة DataTable
let table = $('#professionsTable').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json" },
    ajax: {
        url: "{{ route('admin.categories.index') }}",
        type: "GET",
        error: function() {
            Swal.fire('خطأ!', 'حدث خطأ في تحميل البيانات', 'error');
        }
    },
    columns: [
        { data: 'id', name: 'id' },
        { data: 'icon_html', name: 'icon_html', orderable: false, searchable: false },
        { data: 'name_display', name: 'name_ar' },
        { data: 'sub_professions_count', name: 'sub_professions_count', orderable: false },
        { data: 'status_html', name: 'status_html', orderable: false },
        { data: 'created_at_formatted', name: 'created_at' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "الكل"]]
});