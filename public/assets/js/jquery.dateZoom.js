'use strict';

(function ($) {
    // a plugin that enlarges the text of an element when moused
    // over, then returns it to its original size on mouse  out
    $.fn.dateZoom = function (options) {
        // only overwrite values that were explicitly passed by
        // the user in options
        let opts = $.extend($.fn.dateZoom.defaults, options);

        // loops through each matched element and returns the
        // modified jQuery object to maintain chainability
        return this.each(function () {
            // stores the original font size of the element
            let originalSize = $(opts.selector).css('font-size');

            // binds functions to the hover event. The first is
            // triggered when the user hovers over the element, and
            // the second when the user stops hovering
            $(opts.selector).hover(
                function () {
                    $.fn.dateZoom.zoom(opts.selector, opts.fontsize, opts);
                },
                function () {
                    $.fn.dateZoom.zoom(opts.selector, originalSize, opts);
                }
            );
        });
    };
    // defines default values for the plugin
    $.fn.dateZoom.defaults = {
        fontsize: '110%',
        easing: 'swing',
        duration: '600',
        selector: 'li>a',
        // match: 'href',
        callback: null,
    };

    // defines a utility function that is available outside of the
    // plugin if a user is so inclined to use it
    $.fn.dateZoom.zoom = function (element, size, opts) {
        // zoom the elements
        if (opts.match) {
            element = $.grep($(element), function (elem) {
                return elem[opts.match] === $('a:hover')[0][opts.match];
            });
        }
        $(element)
            .animate(
                {
                    'font-size': size,
                },
                {
                    duration: opts.duration,
                    easing: opts.easing,
                    complete: opts.callback,
                }
            )
            .dequeue() // prevents jumpy animation
            .clearQueue(); // ensures only one animation occurs
    };
})(jQuery);
