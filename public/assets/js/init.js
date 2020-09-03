'use strict'; // enforce variable declarations - safer coding

// Make sure the document is ready before executing scripts
jQuery(function ($) {
    // A quick check to make sure the script loaded properly
    console.log('init.js was loaded successfully');

    // File to which AJAX requests should be sent
    const processFile = 'assets/inc/ajax.inc.php',
        // Functions to manipulate the modal window
        fx = {
            // Checks for a modal window and returns it, or
            // else creates a new one and returns that
            initModal: function () {
                // If no elements are matched, the length
                // property will return 0
                if ($('.modal-window').length == 0) {
                    // Creates a div, adds a class, and
                    // appends it to the body tag
                    return $('<div>')
                        .hide()
                        .addClass('modal-window')
                        .appendTo('body');
                } else {
                    // Returns the modal window if one
                    // already exists in the DOM
                    return $('.modal-window');
                }
            },

            // adds the window to the markup and fades it in
            boxin: function (data, modal) {
                // creates an overlay for the site, adds a
                // class and a click event handler, then
                // appends it to the body element
                $('<div>')
                    .hide()
                    .addClass('modal-overlay')
                    .click(function (event) {
                        // removes event
                        fx.boxout(event);
                    })
                    .appendTo('body');

                // loads data into the modal window and
                // appends it to the body of the element
                modal.hide().append(data).appendTo('body');

                // fades in the modal window and overlay
                $('.modal-window, .modal-overlay').fadeIn('slow');
            },

            // fades out the window and removes it from the DOM
            boxout: function (event) {
                // If an event was triggered by the element
                // that called this function, prevents the
                // default action from firing
                if (event != undefined) {
                    event.preventDefault();
                }
                // removes the active class from all links
                $('a').removeClass('active');

                // fades out the modal window, the removes it
                // from the DOM entirely
                $('.modal-window, .modal-overlay').fadeOut('slow', function () {
                    $(this).remove();
                });
            },

            // adds a new event to the markup after saving
            addevent: function (data, formData) {
                // converts the query string to an object
                let entry = fx.deserialize(formData),
                    // makes a date object for current month
                    cal = new Date(NaN),
                    // Makes a date object for the new event
                    event = new Date(NaN),
                    // Extracts the calendar month from the h2 id
                    cdata = $('h2').attr('id').split('-'),
                    // Extracts the event day, month and year
                    date = entry.eventStart.split(' ')[0],
                    // splits the event data into pieces
                    edata = date.split('-');

                // Sets the date for the calendar date object
                cal.setFullYear(cdata[1], cdata[2], 1);

                // Sets thedate for the event date object
                event.setFullYear(edata[0], edata[1], edata[2]);

                // since the date object is created using
                // GMT, then adjusted for the local time zone,
                // adjust the offset to ensure a proper date
                event.setMinutes(event.getTimezoneOffset());

                // if the year and month match, start the process
                // of adding the new event to the calendar
                if (
                    cal.getFullYear() == event.getFullYear() &&
                    cal.getMonth() == event.getMonth()
                ) {
                    // get the day of the month for event
                    let day = String(event.getDate());

                    // Adds a leading zero to 1-digit days
                    day = day.length == 1 ? '0' + day : day;

                    // adds the new date link
                    $('<a>')
                        .hide()
                        .attr('href', 'view.php?eventId=' + data)
                        .text(entry.eventTitle)
                        .insertAfter($('strong:contains(' + day + ')'))
                        .delay(1000)
                        .fadeIn('slow');
                }
            },

            // removes an event from the markup after deletion
            removeevent: function () {
                // removes any event with the class 'active'
                $('.active').fadeOut('slow', function () {
                    $(this).remove();
                });
            },

            // deserializes the query string and returns
            // an event object
            deserialize: function (str) {
                // breaks apart each name-value pair
                let data = str.split('&'),
                    // declares varibles for use in the loop
                    pairs = [],
                    entry = {},
                    key,
                    val;

                // loops through each name-value pair
                for (const x in data) {
                    // splits each pair into an array
                    pairs = data[x].split('=');

                    // the first element is the name
                    key = pairs[0];

                    // second element is the value
                    val = pairs[1];

                    // reverses the URL encoding and stores
                    // each value as an object property
                    entry[key] = fx.urldecode(val);
                }
                return entry;
            },

            // decodes a query string value
            urldecode: function (str) {
                // converts plus signs to spaces
                let converted = str.replace(/\+/g, ' ');

                // converts any encoded entities back
                return decodeURIComponent(converted);
            },
        };
    // set adefault font-size value for dateZoom
    $.fn.dateZoom.defaults.fontsize = '13px';

    // Pulls up events in a modal window
    $('body')
        .dateZoom()
        .on('click', 'li>a', function (event) {
            // Stops the link from loading view.php
            event.preventDefault();

            // Adds an 'active' class to the link
            $(this).addClass('active');

            // Gets the query string from the link href
            let data = $(this)
                    .attr('href')
                    .replace(/.+?\?(.*)/, '$1'),
                // Checks if the modal window exists and
                // selects it, or creates a new one

                modal = fx.initModal();

            // Creates a button to close the window
            $('<a>')
                .attr('href', '#')
                .addClass('modal-close-btn')
                .html('&times;')
                .click(function (event) {
                    // Removes modal window
                    fx.boxout(event);
                })
                .appendTo(modal);

            // Loads the event data from the DB
            $.ajax({
                type: 'POST',
                url: processFile,
                data: 'action=eventView&' + data,
                success: function (data) {
                    fx.boxin(data, modal);
                },
                error: function (msg) {
                    modal.append(msg);
                },
            });
            //log the link text
            console.log(data);
        });

    // Displays the edit form as a modal window
    $('body').on('click', '.admin-options form, .admin', function (event) {
        // prevents the form from submitting
        event.preventDefault();

        // loads the action for the processing file
        let action = $(event.target).attr('name') || 'editEvent',
            // saves the value of the eventId input
            id = $(event.target).siblings('input[name=eventId]').val();

        // Creates an additional param for the ID if set
        id = id != undefined ? '&eventId=' + id : '';

        // loads the editing form and displays it
        $.ajax({
            type: 'POST',
            url: processFile,
            data: 'action=' + action + id,
            success: function (data) {
                // hides the form
                let form = $(data).hide(),
                    // Make sure the modal window exists
                    modal = fx
                        .initModal()
                        .children(':not(.modal-close-btn)')
                        .remove()
                        .end();

                // call the boxin function to create
                // the modal overlay and fade it in
                fx.boxin(null, modal);

                // Load the form into the window,
                // fades in the content and adds
                // a class to the form
                form.appendTo(modal).addClass('edit-form').fadeIn('slow');
            },
            error: function (msg) {
                alert(msg);
            },
        });
    });

    // make the cancel button on editing form behave like the
    // close button and fade out modal windows and overlays
    $('body').on('click', '.edit-form a:contains(cancel)', function (event) {
        fx.boxout(event);
    });

    // edits events without reloading
    $('body').on('click', '.edit-form input[type=submit]', function (event) {
        // prevents the default form action from executing
        event.preventDefault();

        // serializes the form data for use with $.ajax
        let formData = $(this).parents('form').serialize();

        // stores the value of the submit button
        let submitVal = $(this).val(),
            // determines if the event should be removed
            remove = false,
            // saves the start date input string
            start = $(this).siblings('[name=eventStart]').val(),
            // saves the end date input string
            end = $(this).siblings('[name=eventEnd]').val();

        // if this is the deletion form, appends an action
        if ($(this).attr('name') == 'confirmDelete') {
            // adds necessary info to the query string
            formData += '&action=confirmDelete' + '&confirmDelete=' + submitVal;

            // If the event is really being deleted, sets
            // a flag to remove it from the markup
            if (submitVal == 'Yes, delete it') {
                remove = true;
            }
        }

        // if creating/editing an event, checks for valid dates
        if ($(this).siblings('[name=action]').val() == 'eventEdit') {
            if (!$.validDate(start) || !$.validDate(end)) {
                alert('Valid dates only! (YYYY-MM-DD HH:MM:SS)');
                return false;
            }
        }

        // sends the form data for use with $.ajax()
        $.ajax({
            type: 'POST',
            url: processFile,
            data: formData,
            success: function (data) {
                // if this is a deleted event, removes
                // it form the markup
                if (remove === true) {
                    fx.removeevent();
                }

                // fades out the modal window
                fx.boxout();

                // If this is a new event, adds it to
                // the calendar
                if ($('[name=eventId]').val().length == 0 && remove === false) {
                    fx.addevent(data, formData);
                }
            },
            error: function (msg) {
                alert(msg);
            },
        });
    });
});
