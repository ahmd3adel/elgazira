// تصدير Excel
$('#exportExcel').click(function(e) {
    e.preventDefault();
    let data = table.rows({ search: 'applied' }).data().toArray();
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
    
    let csv = [['#', 'اسم المهنة', 'الاسم بالإنجليزية', 'عدد التخصصات', 'الحالة', 'تاريخ الإضافة']];
    data.forEach(row => {
        csv.push([
            row.id,
            row.name_display?.replace(/[&nbsp;↳]/g, '').trim() || '',
            row.name_en || '',
            row.sub_professions_count?.match(/\d+/)?.[0] || '0',
            row.status_html?.includes('نشط') ? 'نشط' : 'معطل',
            row.created_at_formatted || ''
        ]);
    });
    
    let csvContent = "\uFEFF" + csv.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
    let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    let link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `categories_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
    Swal.fire('تم التصدير', `تم تصدير ${data.length} سجل`, 'success');
});

// طباعة
$('#exportPrint').click(function(e) {
    e.preventDefault();
    let data = table.rows({ search: 'applied' }).data().toArray();
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للطباعة', 'warning');
    
    let rows = '';
    data.forEach(row => {
        rows += `<tr>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.id}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:right">${row.name_display?.replace(/[&nbsp;↳]/g, '').trim() || ''}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.sub_professions_count?.match(/\d+/)?.[0] || '0'}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.status_html?.includes('نشط') ? 'نشط' : 'معطل'}</td>
            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.created_at_formatted || ''}</td>
        </tr>`;
    });
    
    let printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html dir="rtl"><head><title>تقرير المهن</title>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>*{font-family:'Cairo',sans-serif;} body{padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:10px;text-align:center;} th{background:#f2f2f2;}</style>
        </head><body>
        <h1 style="text-align:center">📋 قائمة المهن الرئيسية</h1>
        <div style="text-align:center">تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</div>
        <table><thead><tr><th>#</th><th>اسم المهنة</th><th>عدد التخصصات</th><th>الحالة</th><th>تاريخ الإضافة</th></tr></thead><tbody>${rows}</tbody></table>
        <div style="text-align:center;margin-top:30px">تم التصدير من نظام إدارة المهن - ${new Date().toLocaleString('ar-EG')}</div>
        </body></html>
    `);
    printWindow.document.close();
    printWindow.print();
});

// PDF
$('#exportPDF').click(function(e) {
    e.preventDefault();
    let data = table.rows({ search: 'applied' }).data().toArray();
    if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
    
    Swal.fire({ title: 'جاري إنشاء PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    let tableData = data.map(row => [
        row.id,
        row.name_display?.replace(/[&nbsp;↳]/g, '').trim() || '',
        row.sub_professions_count?.match(/\d+/)?.[0] || '0',
        row.status_html?.includes('نشط') ? 'نشط' : 'معطل',
        row.created_at_formatted || ''
    ]);
    
    doc.autoTable({
        head: [['#', 'اسم المهنة', 'عدد التخصصات', 'الحالة', 'تاريخ الإضافة']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [0, 102, 204], textColor: 255, halign: 'center' },
        bodyStyles: { halign: 'right' },
        margin: { top: 30 }
    });
    
    doc.setFontSize(18);
    doc.setTextColor(0, 102, 204);
    doc.text('قائمة المهن الرئيسية', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' });
    doc.save(`categories_${new Date().toISOString().slice(0, 10)}.pdf`);
    Swal.fire('تم التصدير', `تم تصدير ${data.length} سجل`, 'success');
});