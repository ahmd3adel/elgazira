// تحسين التجاوب
function adjustTableResponsive() {
    let $wrapper = $('.table-responsive-wrapper');
    let $table = $('#professionsTable');
    
    if ($(window).width() <= 767) {
        $wrapper.css({ 'overflow-x': 'auto', '-webkit-overflow-scrolling': 'touch' });
        $table.css('min-width', '650px');
    } else if ($(window).width() <= 991) {
        $table.css('min-width', '750px');
    } else {
        $table.css('min-width', '');
    }
}

$(window).on('resize', adjustTableResponsive).trigger('resize');
table.on('draw.dt', adjustTableResponsive);

// تلميح السكرول للموبايل
function showScrollHint() {
    if ($(window).width() <= 767) {
        let $wrapper = $('.table-responsive-wrapper');
        if ($wrapper.length && $wrapper[0].scrollWidth > $wrapper[0].clientWidth) {
            $('.scroll-hint').remove();
            let hint = $('<div class="scroll-hint" style="text-align:center;padding:8px;background:#e9ecef;color:#4e73df;font-size:12px"><i class="fas fa-arrows-alt-h"></i> اسحب لليمين لعرض جميع الأعمدة <i class="fas fa-arrow-right"></i></div>');
            $wrapper.after(hint);
            setTimeout(() => hint.fadeOut(1000, function() { $(this).remove(); }), 3000);
        }
    }
}

setTimeout(showScrollHint, 1500);
$(window).on('resize', showScrollHint);
table.on('draw.dt', showScrollHint);

if ('ontouchstart' in window) {
    $('.table-responsive-wrapper').css('-webkit-overflow-scrolling', 'touch');
}

</script>