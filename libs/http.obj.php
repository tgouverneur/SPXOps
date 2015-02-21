<?php
/**
 * HTTP class
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2008, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class HTTP
{
  private static $_instance;    /* instance of the class */

  public $argc;
    public $argv;
    public $css;

    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
            return true;
        }

        return false;
    }

    public function parseUrl()
    {
        $url = filter_input(INPUT_SERVER, 'PATH_INFO');
        if (!$url) {
            return;
        }
        $url = explode('/', $url);
        $g = array();
        $idx = "";
        $val = "";
        $c = count($url);
        for ($i = 1, $s = 0; $i<$c; $i++) {
            if ($s == 0) {
                $idx = $url[$i];
                $g[$idx] = "";
                $s++;
            } else {
                $val = $url[$i];
                $g[$idx] = $val;
                $idx = "";
                $val = "";
                $s = 0;
            }
        }
        $_GET = array_merge($_GET, $g);

        return;
    }

    public static function redirect($url)
    {
        header("Status: 301 Moved Permanently");
        header("Location: ".$url);
    }

  /**
   * return the instance of HTTP object
   */
  public static function getInstance()
  {
      if (!isset(self::$_instance)) {
          $c = __CLASS__;
          self::$_instance = new $c();
      }

      return self::$_instance;
  }

  /**
   * Avoid the __clone method to be called
   */
  public function __clone()
  {
      trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }


    public static function checkEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    public static function getDateTimeFormat()
    {
        $df = Setting::get('display', 'timeFormat');
        if ($df) {
            return $df->value;
        }
        /* defaults */
        return 'Y-m-d H:m:s';
    }

    public static function getDateFormat()
    {
        $df = Setting::get('display', 'dateFormat');
        if ($df) {
            return $df->value;
        }
        /* defaults */
        return 'Y-m-d';
    }

}
