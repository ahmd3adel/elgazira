<script src="{{ asset('assets/backend/plugins/chart.js/Chart.min.js')}}"></script>

<script>
$(document).ready(function() {
    // الرسم البياني الخطي (إحصائيات المنصة)
    var platformCtx = document.getElementById('platformChart').getContext('2d');
    new Chart(platformCtx, {
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
                legend: { position: 'top', rtl: true }
            }
        }
    });
    
    // الرسم البياني الدائري (توزيع الخبراء حسب التخصص)
    var specialtiesCtx = document.getElementById('specialtiesChart').getContext('2d');
    new Chart(specialtiesCtx, {
        type: 'pie',
        data: {
            labels: ['الطب', 'الهندسة', 'المحاماة', 'التقنية', 'التعليم', 'المال والأعمال'],
            datasets: [{
                data: [35, 25, 15, 12, 8, 5],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'right', rtl: true }
            }
        }
    });
});
</script>