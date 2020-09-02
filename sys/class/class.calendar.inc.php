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
        $sth->bindParam(':id',  $id, PDO::PARAM_INT);
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
      $labels .= "\n\t\t<li>" . WEEKDAYS[$d] . "</li>";
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
        $c == $t
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
      $wrap = $i != 0 && $i % 7 == 0 ? "\n\t</ul>\n\t<ul>" : NULL;

      // Assemble the pieces into a finished item
      $eventInfo = $eventInfo ?? '';

      $html .= $liStart . $date  . $eventInfo . $liEnd . $wrap;
    }

    // Add filler to finish out the last week
    while ($i % 7 != 1) {
      $html .= "\n\t\t<li class=\"fill\">&nbsp;</li>";
      ++$i;
    }

    // close the unordered list
    $html .= "\n\t</ul>\n\n";

    // If logged in, display the admin options

    $admin = $this->_adminGeneralOptions();

    // return the markup for output
    return $html . $admin;
  }

  /**
   * Displays a given event's information
   *
   * @param int $idthe event ID
   * @return string basic markup to display the event info
   */
  public function displayEvent($id)
  {
    // Make sure an ID was passed
    if (empty($id)) {
      return NULL;
    }

    // Make sure the ID is an integer
    $id = preg_replace('/[^0-9]/', '', $id);

    // Load the event data from the DB
    $event = $this->_loadEventById($id);

    // Generate strings for the date, start, and end time
    $timestamp = strtotime($event->start);
    $date = date('F d, Y', $timestamp);
    $start = date('g:ia', $timestamp);
    $end = date('g:ia', $timestamp);

    // Load admin options if the user is logged in
    $admin = $this->_adminEntryOptions($id);

    // Generate and return the markup
    return "<h2>$event->title</h2>"
      . "\n\t <p class=\"dates\">$date, $start&mdash;$end</p>"
      . "\n\t<p class=\"\">$event->description</p>"
      . "\n\t<p>$event->description</p>$admin";
  }

  /**
   * Generates a form to edit or create events
   * @return string the HTML markup for the editing form
   */
  public function displayForm()
  {
    // Check if an ID was passed

    if (isset($_POST['eventId'])) {
      $id = (int) $_POST["eventId"]; // force integer type to sanitize dat
    } else {
      $id = NULL;
    }

    // Instantiate the headline/submit button text
    $submit = 'Create a new event';

    // if no ID is passed, start with an empty object.
    $event = new Event();

    // Otherwise load the associated event
    if (!empty($id)) {
      $event = $this->_loadEventById($id);

      // If no object is returned, return NULL
      if (!is_object($event)) {
        return NULL;
      }

      $submit = 'Edit This Event';
    }

    // Build the markup
    return <<<FORM_MARKUP
    <form action="assets/inc/process.inc.php" method="post">
      <fieldset>
        <legend>Submit</legend>
        <label for"eventTitle">Event Title</label>
        <input type="text" name="eventTitle" id="eventTitle" value="$event->title">

        <label for"eventStart">Start Time</label>
        <input type="text" name="eventStart" id="eventStart" value="$event->start">

        <label for"eventEnd">End Time</label>
        <input type="text" name="eventEnd" id="eventEnd" value="$event->end">

        <label for"eventDescription">Event Description</label>
        <textarea name="eventDescription" id="eventDescription">$event->description</textarea>
        <input type="hidden" name="eventId" id="" value="$event->id">
        <input type="hidden" name="token" id="" value="$_SESSION[token]">
        <input type="hidden" name="action" id="" value="eventEdit">
        <input type="submit" name="eventSubmit" id="" value="$submit">
        or <a href="./">cancel</a>
      </fieldset>
    </form>
FORM_MARKUP;
  }

  /**
   * Validates the form and saves/edits the event
   *
   * @return mixed TRUE on success, an error message on failure
   */
  public function processForm()
  {
    // Exit if the action isn't set properly
    if ($_POST['action'] != 'eventEdit') {
      return 'The method processForm was accessed incorrectly';
    }
    // Escape data from the form
    $title = htmlentities($_POST["eventTitle"], ENT_QUOTES);
    $description = htmlentities($_POST["eventDescription"], ENT_QUOTES);
    $start = htmlentities($_POST["eventStart"], ENT_QUOTES);
    $end = htmlentities($_POST["eventEnd"], ENT_QUOTES);

    // If no event ID was passed, create a new event
    if (empty($_POST['eventId'])) {
      $sql = "INSERT INTO events (eventTitle, eventDesc, eventStart, eventEnd)
      VALUES (:title, :description, :start, :end)";
    }
    // Update the event if it's being edited
    else {
      // cast the event ID as an integer for security
      $id = (int)$_POST["eventId"];
      $sql = "UPDATE events
      SET eventTitle = :eventTitle,
      eventDesc = :eventDescription,
      eventStart = :eventStart,
      eventEnd = :eventEnd
      WHERE eventId = $id";
    }
    // Execute the create or edit query after binding the data
    try {
      $sth = $this->db->prepare($sql);
      $sth->bindParam(':title', $title, PDO::PARAM_STR);
      $sth->bindParam(':description', $description, PDO::PARAM_STR);
      $sth->bindParam(':start', $start, PDO::PARAM_STR);
      $sth->bindParam(':end', $end, PDO::PARAM_STR);
      $sth->execute();
      $sth->closeCursor();
      return true;
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * Returns a single event object
   *
   * @param int $id an event ID
   * @return object the event object
   */
  private function _loadEventById($id)
  {
    /**
     * If no ID is passed return NULL
     */
    if (empty($id)) {
      return NULL;
    }

    /**
     * Load the events info array
     */
    $event = $this->_loadEventData($id);

    // Return an event object
    if (isset($event[0])) {
      return new Event($event[0]);
    } else {
      return NULL;
    }
  }

  /**
   * Generates markup to display administrative links
   *
   * @return string markup to display the administrative links
   */
  private function _adminGeneralOptions()
  {
    // If the user is logged in, display admin controls
    if (isset($_SESSION['user'])) {
      return <<<ADMIN_OPTIONS

    <a href="admin.php" class="admin" >+ Add a new Event</a>
    <form action="assets/inc/process.inc.php" method="post">
      <div>
        <input type="submit" name="" id="" value="Log Out" class="logout" >
        <input type="hidden" name="token" id="" value="$_SESSION[token]">
        <input type="hidden" name="action" id="" value="userLogout">
      </div>
    </form>
ADMIN_OPTIONS;
    } else {
      return <<<ADMIN_OPTIONS
    <a href="login.php">Login</a>
ADMIN_OPTIONS;
    }
  }

  /**
   * Generates edit and delete options for a given event ID
   *
   * @param int $id the event ID to generate options for
   * @return string the markup for the edit/delete options
   */
  private function _adminEntryOptions($id)
  {
    if (isset($_SESSION['user'])) {
      # code...

      return <<<ADMIN_OPTIONS
    <div class="adminOptions" >
    <form action="admin.php" method="post">
    <p>
    <input type="submit" name="editEvent" id="" value="Edit this Event">
    <input type="hidden" name="eventId" id="" value="$id">
    </p>
    </form>
    <form action="confirmdelete.php" method="post">
      <p>
        <input type="submit" name="deleteEvent" id="" value="Delete this Event">
        <input type="hidden" name="eventId" id="" value="$id">
      </p>
    </form>
    </div><!--end .adminOptions -->
ADMIN_OPTIONS;
    } else {
      return NULL;
    }
  }

  /**
   * Confirms tha an event should be deleted and does so
   *
   * Upon clicking the button to delete an event, this
   * generates a confirmation box. If the user confirms,
   * this deletes the event from the database and sends the user
   * back out to the main clendar view. If the user
   * decides not to delete the event, they're sent back to the main calendar view
   * without deleting anything.
   *
   * @param int $id the event ID
   * @return mixed the form if confirmin, void or error if deleting
   */
  public function confirmDelete($id)
  {
    // Make sure an ID was passed
    if (empty($id)) {
      return NULL;
    }
    // Make sure the ID is an integer
    $id = preg_replace('/[^0-9]/', '', $id);

    // if the confirmation form was submitted and the form
    // has a valid token, check the form submission

    if (
      isset($_POST['confirmDelete'])
      && $_POST['token'] == $_SESSION['token']
    ) {
      // If the deletion is confirmed, remove the event
      // from the database

      if ($_POST['confirmDelete'] == 'Yes, delete it') {
        $sql = "DELETE
        FROM events
        WHERE eventId = :id
        LIMIT 1";

        try {
          $sth = $this->db->prepare($sql);
          $sth->bindParam(':id', $id, PDO::PARAM_INT);
          $sth->execute();
          $sth->closeCursor();
          header('Location: ./');
          return;
        } catch (Exception $e) {
          return $e->getMessage();
        }
      }
      // if not confirmed, sends the user to the main view
      else {
        header('Location: ./');
        return;
      }
    }

    // If the confirmation form has not been submitted, display it
    $event = $this->_loadEventById($id);

    // If no object is returned, return to the main view
    if (!is_object($event)) {
      header('Location: ./');
    }
    return <<<CONFIRM_DELETE
    <form action="confirmdelete.php" method="post">
      <h2>
        Are you sure you want to delete "$event->title"?
      </h2>
      <p>There is <strong>no undo</strong> if you continue.</p>
      <p>
        <input type="submit" name="confirmDelete" id="" value="Yes, delete it">
        <input type="submit" name="confirmDelete" id="" value="Nope, just kidding!">
        <input type="hidden" name="eventId" id="" value="$event->id">
        <input type="hidden" name="token" id="" value="$_SESSION[token]">
      </p>
    </form>
CONFIRM_DELETE;
  }
}
