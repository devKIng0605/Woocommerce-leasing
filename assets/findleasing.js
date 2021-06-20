jQuery(function($){
    $(window).load(function() {
        let $sliderEle = $('#fl-image-slider');
        if( find_leasing_object.gallery_slider == 'lightslider' ) {
            $sliderEle.lightSlider({
                adaptiveHeight:true,
                item:1,
                slideMargin:0,
                loop:true
            });
        } else if ( find_leasing_object.gallery_slider == 'slick' ) {
            $sliderEle.slick({dots: true, lazyLoad: 'ondemand'});
        }
        $sliderEle.show();
    });

    $('.fl-bs .ownership-buttons .ownership-button').on('click', function(e) {
        $('.fl-bs .ownership-buttons .ownership-button').removeClass('active');
        $(this).addClass('active');
        if( $(this).val() === 'private-tab' ) {
            $('.fl-bs .leasing-tab#private-tab').removeClass('hidden');
            $('.fl-bs .leasing-tab#business-tab').addClass('hidden');
        } else {
            $('.fl-bs .leasing-tab#private-tab').addClass('hidden');
            $('.fl-bs .leasing-tab#business-tab').removeClass('hidden');
        }
    })
});