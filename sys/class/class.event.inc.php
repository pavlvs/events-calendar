<?php

declare(strict_types=1);

/**
 * Stores event information
 *
 * PHP version 7
 *
 * LICENSE
 *
 * @author Paul Shaw <pavlvsxavier@gmail.com>
 * @copyright 2020 Canarius
 * @license MIT
 */
class Event
{
  /**
   * The event ID
   *
   * @var int
   */
  public $id;

  /**
   * The event title
   *
   * @var string
   */
  public $title;

  /**
   * The event description
   *
   * @var string
   */
  public $description;

  /**
   * The event start time
   *
   * @var string
   */
  public $start;

  /**
   * The event end time
   *
   * @var string
   */
  public $end;

  /**
   * Accepts an array of event data and stores it
   *
   * @param array  $event Associative array of event data
   * @return void
   */
  public function __construct($event = NULL)
  {

    if (is_array($event)) {
      $id = $event['eventId'];
      $title = $event['eventTitle'];
      $desc = $event['eventDesc'];
      $start = $event['eventStart'];
      $end = $event['eventEnd'];

      $this->id = $id;
      $this->title = $title;
      $this->description = $desc;
      $this->start = $start;
      $this->end = $end;
    } else {
      $this->id = NULL;
      $this->title = "";
      $this->description = "";
      $this->start = "";
      $this->end = "";
    }
  }
}
