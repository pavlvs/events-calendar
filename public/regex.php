<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        em {
            background-color: #ff0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
    </style>
</head>

<body>
    <?php
    /*  // store the sample text to use for the examples of regex

    $string = <<<TEST_DATA

    <h2>Regular Expression Testing</h2>
    <p class=\"\">
    In this document, there is a lot of text that can be matched
    using regex. The benefit of using regular expression is much
    more flexible, albeit complex, syntax for text pattern matching.
    </p>

    <p>
        After you get the hang of regular expressions, also called
         regexes, they will become a powerful tool for pattern matching.
    </p>
    <hr>
TEST_DATA; */

    // set up several test date strings to ensure validation is working

    $date[] = '2020-01-14 12:00:00';
    $date[] = 'Saturday, May 14th at 7pm';
    $date[] = '02/03/10 10:00pm';
    $date[] = '2020-01-14 102:00:00';

    // date validation pattern
    $pattern = '/^(\d{4}(-\d{2}){2} (\d{2})(:\d{2}){2})$/';

    foreach ($date as $d) {
        echo '<p>',  preg_replace($pattern, '<em>$1</em>', $d), "</p>";
    }

    // Output the pattern you just used
    echo "\n<p>Pattern used: <strong>$pattern</strong></p>";

    ?>

</body>

</html>