<?php

declare(strict_types=1);

/**
 * Builds and manipulates an event calendar
 *
 * PHP Version 7
 *
 * LICENSE
 *
 * @author Paul Shaw <pavlvsxavier@gmail.com>
 * @copyright 2020 Canarius
 * @license
 */

class Calendar extends DB_Connect
{
  /**
   * The date from which the calendar should be built
   *
   * Stored in YYYY-MM-DD HH:MM:SS format
   * @var string the date to use for the calendar
   */
  private $_useDate;

  /**
   * The month for which the calendar is being bilt
   *
   * @var int the month being used
   */
  private $_m;

  /**
   * The year from which the months' start day is selected
   *
   * @var int the year being used
   */
  private $_y;

  /**
   * The number of days in the month being used
   *
   * @var int the number of days in the nonth
   */
  private $_daysInMonth;

  /**
   * The index of the day of the week the month starts on (0-6)
   *
   * @var int the day of the week the month starst on
   */
  private $_startDay;

  /**
   * Creates a database object and stores relevant data
   *
   * Upon instantiation, this class accepts a database object
   * that, if not null, is stored in the object's private $_db
   * property. If null, a new PDO object is created and stored
   * instead.
   *
   * Additional info is gathered and stored in this method,
   * including the month from which the calendar is to be built,
   * how many days are in said month, what day the month starts
   * on, and what day it is currently.
   *
   * @param object $dbo a database object
   * @param string $useDate the date to use to build the calendar
   * @return void
   */
  public function __construct($dbo = NULL, $useDate = NULL)
  {
    /**
     * Call the parent constructor to check
     * for a database object
     */
    parent::__construct($dbo);

    // gather and store data relevant to the month
    if (isset($useDate)) {
      $this->_useDate = $useDate;
    } else {
      $this->_useDate = date('Y-m-d H:i:s');
    }

    // Convert to a timestamp, then determine the month
    // and the year to use when building the calendar

    $timestamp = strtotime($this->_useDate);
    $this->_m = (int)date('m', $timestamp);
    $this->_y = (int)date('Y', $timestamp);

    // Determine how many days ar in the month
    $this->_daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->_m, $this->_y);

    // Determine what day of the weekday the month starts on
    $timestamp = mktime(0, 0, 0, $this->_m, 1, $this->_y);
    $this->_startDay = (int)date('w', $timestamp);
  }

  /**
   * Loads event(s) info into an array
   *
   * @param int $id an optional event ID to filter resuts
   * @return array an array of events from the database
   */
  private function _loadEventData($id = NULL)
  {
    $sql = "SELECT *
            FROM events";
    // If an event ID is supplied, add a where clause
    // so only that event is returned

    if (!empty($id)) {
      $sql .= " WHERE eventId = :id LIMIT 1";
    }

    // otherwise load all events for the month in use
    else {
      // Find the first and last days of the month
      $startTimestamp = mktime(0, 0, 0, $this->_m, 1, $this->_y);
      $endTimestamp = mktime(23, 59, 59, $this->_m + 1, 0, $this->_y);
      $startDate = date('Y-m-d H:i:s', $startTimestamp);
      $endDate = date('Y-m-d H:i:s', $endTimestamp);

      // Filter events to only happening in the currently selected month

      $sql .= " WHERE eventStart
               BETWEEN '$startDate'
               AND '$endDate'
               ORDER BY eventStart";
    }
    try {
      $sth = $this->db->prepare($sql);
      // Bind the parameter if an ID was passed
      if (!empty($id)) {
        $sth->bind_param(':id',  $id, PDO::PARAM_INT);
      }
      $sth->execute();
      $results = $sth->fetchAll(PDO::FETCH_ASSOC);
      $sth->closeCursor();

      return $results;
    } catch (Exception $e) {
      die($e->getMessage());
    }
  }

  /**
   * Loads all events for the month into an array
   *
   * @return array events info
   */
  private function _createEventObject()
  {
    //Load the events array
    $array = $this->_loadEventData();

    // create a new array then organize the events
    // by the day of the month on which they occur

    $events = [];
    foreach ($array as $event) {
      $day = date('j', strtotime($event['eventStart']));
      try {
        $events[$day][] = new Event($event);
      } catch (Exception $e) {
        die($e->getMessage());
      }
    }
    return $events;
  }

  /**
   * Returns HTML markup to display the calendar and events
   *
   * Using the information stored in class properties, the events for the given month are loaded, the calendar is
   * generated and the whole thing is returned as valid markup
   */
  public function buildCalendar()
  {
    /**
     * Determine the calendar month and creat an array of weekday abbreviations
     *  to label the calendar columns
     */
    $calMonth = date('F Y', strtotime($this->_useDate));
    define('WEEKDAYS', array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'));

    /**
     * Add a header to the calendar markup
     */

    $html = "\n\t<h2>$calMonth</h2>";
    for ($d = 0, $labels = NULL; $d < 7; ++$d) {
      $labels .= "\n\t<li>" . WEEKDAYS[$d] . "</li>";
    }
    $html .= "\n\t<ul class=\"weekdays\">"
      . $labels . "\n\t</ul>";

    /**
     * Load events data
     */
    $events = $this->_createEventObject();

    // Create the calendar markup
    $html .= "\n\t<ul>"; // Start unordered list
    for ($i = 1, $c = 1, $t = date('j'), $m = date('m'), $y = date('Y'); $c <= $this->_daysInMonth; ++$i) {
      // Apply a "fill" class to the boxes occurring before the first of the month
      $class = $i <= $this->_startDay ? 'fill' : NULL;



      // Add a "today" class if the current date matches the current date
      if (
        $c + 1 == $t
        && $m == $this->_m
        && $y == $this->_y
      ) {
        $class = 'today';
      }

      // Build the opening and closing list item tags
      $liStart = sprintf("\n\t<li class=\"%s\">", $class);
      $liEnd = "\n\t\t</li>";

      //Add the day of the month to identify the calendar box
      if ($this->_startDay < $i && $this->_daysInMonth >= $c) {
        // Format events data
        $eventInfo = ''; // clear the variable
        if (isset($events[$c])) {
          foreach ($events[$c] as $event) {
            $link = '<a href="view.php?eventId=' . $event->id . '">' . $event->title . '</a>';
            $eventInfo .= "\n\t\t\t$link";
          }
        }
        $date = sprintf("\n\t\t\t<strong>%02d</strong>", $c++);
      } else {
        $date = "&nbsp;";
      }

      // If the current day is a Saturday, wrap to the next row
      $wrap = $i != 0 && $i % 7 == 0 ? "\n\t</ul><ul>" : NULL;

      // Assemble the pieces into a finished item

      $eventInfo = $eventInfo ?? '';

      $html .= $liStart . $date  . $eventInfo . $liEnd . $wrap;
    }

    // Add filler to finish out the last week
    while ($i % 7 != 1) {
      $html .= "\n\t\t<li class=\"fill\">&nbsp;</li>";
      $i++;
    }

    // close the unordered list
    $html .= "\n\t<ul>\n\n";

    // return the markup for output
    return $html;
  }
}
