(function($) {

    $.fn.eqHeights = function() {

        var el = $(this);
        if (el.length > 0 && !el.data('eqHeights')) {
            $(window).bind('resize.eqHeights', function() {
                el.eqHeights();
            });
            el.data('eqHeights', true);
        }
        return el.each(function() {
            var curHighest = 0;
            $(this).children().each(function() {
                var el = $(this),
                    elHeight = el.height('auto').height();
                if (elHeight > curHighest) {
                    curHighest = elHeight;
                }
            }).height(curHighest);
        });
    };

    $('.grid.pocket-equalize ul').eqHeights();

}(jQuery));