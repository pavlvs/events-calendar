<?php

declare(strict_types=1);

/**
 * Manages administrative actions
 *
 * PHP version 7
 *
 * LICENSE
 *
 * @author Paul Shaw <pavlvsxavier@gmail.com>
 * @copyright 2020 Canarius
 * @license MIT
 */
class Admin extends DB_Connect
{
  /**
   * Determines the length of the salt to use in hashed passwords
   *
   * @var int the length of the password salt to use
   */
  private $_saltLength = 7;

  /**
   * Store or creates a DB object and set the salt length
   *
   * @param object $db a database object
   * @param int $saltLength length for the password salt
   */
  public function __construct($db = NULL, $saltLength = NULL)
  {
    parent::__construct($db);

    // If an int was passed, set the length of the salt
    if (is_int($saltLength)) {
      $this->_saltLength = $saltLength;
    }
  }

  /**
   * Check login credentials for a valid user
   *
   * @return mixed TRUE on success, message on eror
   */
  public function processLoginForm()
  {
    //Fails if the proper action was not submitted
    if ($_POST['action'] != 'userLogin') {
      return 'Invalid action supplied for processloginForm';
    }

    // Escapes the user input for security
    $username = htmlentities($_POST['username'], ENT_QUOTES);
    $password = htmlentities($_POST['password'], ENT_QUOTES);

    // Retrieves the matching info from the DB if it exists
    $sql = "SELECT *
            FROM users
            WHERE username = :username
            LIMIT 1";
    try {
      $sth = $this->db->prepare($sql);
      $sth->bindParam(':username', $username, PDO::PARAM_STR);
      $sth->execute();
      $result = $sth->fetchAll();
      $user = array_shift($result);
      $sth->closeCursor();
    } catch (Exception $e) {
      die($e->getMessage());
    }
    // Fails if username doesn't match a DB entry
    if (!isset($user)) {
      return 'Your username or password is invalid';
    }

    // Get the hash of the user-supplied password
    $hash = $this->_getSaltedHash($password, $user['userPass']);

    // Checks if the hashed password matches the stored hash
    if ($user['userPass'] == $hash) {
      //Stores user info in the session as an array
      $_SESSION['user'] = [
        'id' => $user['userId'],
        'name' => $user['userName'],
        'email' => $user['userEmail']
      ];
      return true;
    } else {
      return 'Your username or password is invalid';
    }
  }

  /**
   * Logs out the user
   *
   * @return mixed TRUE on success or message on failure
   */
  public function processLogout()
  {
    //Fails if the proper action was not submitted
    if ($_POST['action'] != 'userLogout') {
      return "Invalid action suplied for processLogout";
    }
    // removes the user array from the current session
    session_destroy();
    return TRUE;
  }

  /**
   * Generates a salted hash of a supplied string
   *
   * @param string $string to be hashed
   * @param string $salt extract the hash from here
   * @return string the salted hash
   */
  private function _getSaltedHash($string, $salt = NULL)
  {
    // Generate a salt if no salt was passed
    if ($salt == NULL) {
      $salt = substr(md5((string)time()), 0, $this->_saltLength);
    }
    // Extract the salt from the string if one is passed
    else {
      $salt = substr($salt, 0, $this->_saltLength);
    }

    // Add the salt to the hash and return it
    return $salt . sha1($salt . $string);
  }

  public function testSaltedHash($string, $salt = NULL)
  {
    return $this->_getSaltedHash($string, $salt);
  }
}
