
    // Chart.js scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Platform statistics chart
        var ctx = document.getElementById('platformChart').getContext('2d');
        var platformChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [
                    {
                        label: 'خبراء جدد',
                        data: [65, 78, 90, 85, 102, 120],
                        borderColor: 'rgba(30, 58, 95, 1)',
                        backgroundColor: 'rgba(30, 58, 95, 0.1)',
                        fill: true,
                    },
                    {
                        label: 'استشارات مكتملة',
                        data: [45, 62, 78, 88, 95, 110],
                        borderColor: 'rgba(230, 126, 34, 1)',
                        backgroundColor: 'rgba(230, 126, 34, 0.1)',
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Cairo, Source Sans Pro'
                            }
                        }
                    }
                }
            }
        });

        // Specialties distribution chart
        var ctx2 = document.getElementById('specialtiesChart').getContext('2d');
        var specialtiesChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['الطب', 'القانون', 'الهندسة', 'استشارات مالية', 'تدريب'],
                datasets: [{
                    data: [45, 18, 25, 7, 5],
                    backgroundColor: ['#1e3a5f', '#e67e22', '#27ae60', '#3498db', '#f1c40f'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Cairo, Source Sans Pro'
                            }
                        }
                    }
                }
            }
        });
    });
