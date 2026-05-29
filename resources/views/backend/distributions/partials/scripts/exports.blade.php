// تصدير المحافظات لـ Excel (CSV)
$('#exportExcel').click(function(e) {
    e.preventDefault();
    // الحصول على البيانات المفلترة فقط من الجدول
    let data = table.rows({ search: 'applied' }).data().toArray();
    
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
    
    // تعريف عناوين الأعمدة للمحافظات
    let csv = [['#', 'كود المحافظة', 'اسم المحافظة', 'المسؤول', 'الهاتف', 'الحالة']];
    
    data.forEach((row, index) => {
        csv.push([
            index + 1,
            row.code || '',
            row.name || '',
            row.manager_name || 'غير محدد',
            row.manager_phone || '',
            row.status == 1 ? 'نشط' : 'معطل'
        ]);
    });
    
    // استخدام BOM لدعم اللغة العربية في Excel
    let csvContent = "\uFEFF" + csv.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
    let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    let link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `governorates_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
    Swal.fire('تم التصدير', `تم تصدير ${data.length} محافظة بنجاح`, 'success');
});

// طباعة قائمة المحافظات
$('#exportPrint').click(function(e) {
    e.preventDefault();
    let data = table.rows({ search: 'applied' }).data().toArray();
    
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للطباعة', 'warning');
    
    let rows = '';
    data.forEach((row, index) => {
        rows += `<tr>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${index + 1}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.code}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:right">${row.name}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.manager_name || '-'}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.manager_phone || '-'}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.status == 1 ? 'نشط' : 'معطل'}</td>
        </tr>`;
    });
    
    let printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html dir="rtl"><head><title>تقرير المحافظات</title>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
        <style>
            *{font-family:'Cairo',sans-serif;} 
            body{padding:20px; direction:rtl;} 
            table{width:100%;border-collapse:collapse;margin-top:20px;} 
            th,td{border:1px solid #ddd;padding:10px;text-align:center;} 
            th{background:#f2f2f2; font-weight:bold;}
            h1{text-align:center; color:#333;}
        </style>
        </head><body>
            <h1>📋 قائمة المحافظات المسجلة</h1>
            <div style="text-align:center">تاريخ التقرير: ${new Date().toLocaleDateString('ar-EG')}</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكود</th>
                        <th>المحافظة</th>
                        <th>المسؤول</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            <div style="text-align:center;margin-top:30px; font-size:12px">تم استخراج التقرير من نظام الإدارة المركزية</div>
        </body></html>
    `);
    printWindow.document.close();
    // الانتظار قليلاً لضمان تحميل الخطوط قبل الطباعة
    setTimeout(() => {
        printWindow.print();
    }, 500);
});

// تصدير PDF للمحافظات
$('#exportPDF').click(function(e) {
    e.preventDefault();
    let data = table.rows({ search: 'applied' }).data().toArray();
    
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
    
    Swal.fire({ title: 'جاري إنشاء PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    // تأكد من استدعاء المكتبة بشكل صحيح
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    
    let tableData = data.map((row, index) => [
        row.status == 1 ? 'نشط' : 'معطل',
        row.manager_phone || '-',
        row.manager_name || '-',
        row.name,
        row.code,
        index + 1
    ]);
    
    // ملاحظة: في jspdf-autotable، يتم عكس الترتيب أحياناً للغة العربية
    doc.autoTable({
        head: [['الحالة', 'الهاتف', 'المسؤول', 'المحافظة', 'الكود', '#']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [23, 162, 184], textColor: 255, halign: 'center' },
        styles: { halign: 'right', font: 'Cairo' }, // تأكد من دمج الخط العربي في ملف منفصل إذا لزم الأمر
        margin: { top: 25 }
    });
    
    doc.setFontSize(18);
    doc.text('قائمة المحافظات', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' });
    doc.save(`governorates_${new Date().toISOString().slice(0, 10)}.pdf`);
    
    Swal.close();
    Swal.fire('تم التصدير', `تم إنشاء ملف PDF لعدد ${data.length} محافظة`, 'success');
});

 </script>