'use strict';

// checks for a valid date string (YYYY-MM-DD HH:MM:SS);
(function ($) {
    $.validDate = function (date, options) {
        // define the regex pattern to validate the format
        let defaultsn = {
                pattern: /^(\d{4}(-\d{2}){2} (\d{2})(:\d{2}){2})$/,
            },
            // extends the defaults with user-supplied options
            opts = $.extend(defaults, options, options);

        // returns true if the date matches, false if it doesn't
        return date.match(opts.pattern) != null;
    };
})(jQuery);
