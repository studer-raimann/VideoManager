$(window).load(function () {
    $('.il-xvid-card-image img').each(function () {
        var img_class = (this.width / this.height > 1) ? 'wide' : 'tall',
            half = this.height / 2,
            img_src = $(this).attr('src');
        $(this).addClass(img_class);
        $(this).css('margin-top', '-' + half + 'px');
        $(this).hide();
        $(this).css('z-index', '0');
        $(this).fadeIn(300);
    });
    // $('.card').on('mouseenter',function(){
    //     $(this).zoom(2);
    // });
});
