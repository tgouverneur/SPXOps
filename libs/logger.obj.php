<?php
/**
 * Logger
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

if (!defined('LLOG_NONE')) {
 define ('LLOG_NONE',   0);
 define ('LLOG_ERR',   1);
 define ('LLOG_WARN', 2);
 define ('LLOG_INFO', 4);
 define ('LLOG_DEBUG', 8);
}

class Logger
{
  /**
   * Singleton variable
   */
  protected static $_instance;
  protected static $_level = LLOG_NONE;
  protected static $_logfd = 0;

  public static function logLevel($level = LLOG_ERR) {
    
    $cn = get_called_class();

    $cn::$_level |= $level;
 
    return;
  }

  public static function setLevel($level = LLOG_ERR) {
    
    $cn = get_called_class();

    $cn::$_level = $level;
 
    return;
  }

  public static function log($str, &$obj = null, $level = LLOG_ERR) {

    $cn = get_called_class();

    if (!($cn::$_level & $level)) {
      return;
    }

    if ($obj && isset($obj->_job) && $obj->_job) {
      $obj->_job->log($str);
    } else if ($cn::$_logfd) {
      fprintf($cn::$_logfd, "[%s] %s\n", date("Y-m-d H:m:s"), $str);
    } else {
      echo "$str\n";
    }
    return;
  }

  public static function openLog() {
    global $config;
    $cn = get_called_class();
    $obj = null;

    if (!($cn::$_logfd = fopen($config['spxopsd']['log'], 'w'))) {
      $cn::log("Cannot open ".$config['spxopsd']['log']." for logging!");
      return;
    }
    $cn::log("Opened ".$config['spxopsd']['log']." for logging!", $obj, LLOG_INFO);
  }

  public static function closeLog() {

    global $config;
    $cn = get_called_class();
    $obj = null;
    if ($cn::$_logfd) {
      $cn::log("Closing ".$config['spxopsd']['log']."!", $obj, LLOG_INFO);
      fclose($cn::$_logfd);
      $cn::$_logfd = 0;
    }
  }

  public static function delInstance() {
    self::closeLog();
    self::$_instance = null;
  }


  /**
   * Returns the singleton instance
   */
  public static function getInstance()
  {
 /**
  * JE TAIME JE TAIME JE TAIME
  * SUPER FORT DE LA MORT QUI TUE
  * ET CA DECHIRE A KEL POINT
  * JE TAIME SI FORT MON BEBE DAMOUR
  * hihi pas fache que je fasse des betises ?
  * cai un message damour dans ton super code
  * de super loulou
  * je t'aime de tout mon coeur et corps
  * mon tit bebe
  * et je suis tres heureuse d'etre la future
  * mere de ton enfant
  */ 
    if (!isset(self::$_instance)) {
     $c = __CLASS__;
     self::$_instance = new $c;
    }
    return self::$_instance;
  }

  /**
   * Avoid the call of __clone()
   */
  public function __clone()
  {
    trigger_error('Cannot clone a singlton object, use ::instance()', E_USER_ERROR);
  }
}

?>
