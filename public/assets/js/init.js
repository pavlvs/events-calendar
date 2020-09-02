'use strict'; // enforce variable declarations - safer coding

// Make sure the document is ready before executing scripts
$(function () {
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
        };

    // Pulls up events in a modal window
    $('body').on('click', 'li>a', function (event) {
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
});
