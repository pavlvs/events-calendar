'use strict';

// checks for a valid date string (YYYY-MM-DD HH:MM:SS);
function validDate(date) {
    // define the regex pattern to validate the format
    let foo = '2020-01-14 12:00:00';
    const pattern = /^(\d{4}(-\d{2}){2} (\d{2})(:\d{2}){2})$/;

    // returns true if the date matches, false if it doesn't
    return date.match(pattern) != null;
}
