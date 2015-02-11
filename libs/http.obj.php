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

    public static function errMysql()
    {
        global $start_time;
        $index = new Template("../tpl/index.tpl");
        $head = new Template("../tpl/head.tpl");
        $foot = new Template("../tpl/foot.tpl");
        $foot->set("start_time", $start_time);
        $content = new Template("../tpl/error.tpl");
        $content->set('error', "An error has occurred with the SQL Server and we were unable to process your request...");
        $index->set("head", $head);
        $index->set("content", $content);
        $index->set("foot", $foot);
        echo $index->fetch();
        exit(0);
    }

    public static function errWWW($e)
    {
        global $start_time;
        $lm = LoginCM::getInstance();

        $index = new Template("../tpl/index.tpl");
        $head = new Template("../tpl/head.tpl");
        $foot = new Template("../tpl/foot.tpl");
        $foot->set("start_time", $start_time);
        $content = new Template("../tpl/error.tpl");
        $content->set("error", $e);

        $page = array();
        $page['title'] = 'That\'s some bad hat harry';
        if ($lm->o_login) {
            $page['login'] = &$lm->o_login;
        }

        $head->set('page', $page);
        $index->set("head", $head);
        $index->set("content", $content);
        $index->set("foot", $foot);
        echo $index->fetch();
        exit(0);
    }

    public function parseUrl()
    {
        if (!isset($_SERVER['PATH_INFO'])) {
            return;
        }
        $url = explode('/', $_SERVER['PATH_INFO']);
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
    //$_GET = $g;
    $_GET = array_merge($_GET, $g);

        return;
    }

    public static function redirect($url)
    {
        header("Status: 301 Moved Permanently");
        header("Location: ".$url);
        exit();
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

  /**
   * Get the http post/get variable
   * @arg Name of the variable to get
   * @return the variable, with POST->GET priority
   */
  public function getHTTPVar($name)
  {
      global $_GET, $_POST;

    /* first check POST, then fallback on GET */
    if (isset($_POST[$name])) {
        return $_POST[$name];
    }
      if (isset($_GET[$name])) {
          return $_GET[$name];
      }

      return;
  }

  /**
   * Sanitize an array by escaping the strings inside.
   * @arg Name of the variable to sanitize
   */
  public function sanitizeArray(&$var)
  {
      foreach ($var as $name => $value) {
          if (is_array($value)) {
              $this->sanitizeArray($value);
              continue;
          }

          $var[$name] = mysql_escape_string($value);
      }
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
