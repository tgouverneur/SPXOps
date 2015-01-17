<?php
/**
 * Login Manager
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class loginCM
{
  /**
   * Singleton variable
   */
  private static $_instance;
  public $username = "";
  public $isLogged = 0;
  public $o_login = NULL;

  public function startSession() {
    global $_SERVER;
    session_start();
    $this->checkLogin();
    if ($this->o_login) {
      $this->o_login->getAddr();
      $this->o_login->fetchData();
    }
  }

  public function login($username, $password, $keep = 0) {
    global $_COOKIE;
    global $_SESSION;
    global $_SERVER;
    global $config;

    $l = new Login();
    $l->username = $username;
    if ($l->fetchFromField("username")) {
      return -1;
    }
    if ($l->auth($password) == FALSE) {
      return -1;
    }
    $this->isLogged = 1;
    $this->o_login = $l;
    $l->t_last = time();
    $l->update();
    $this->username = $l->username;
    if (isset($_SESSION)) $_SESSION['username'] = $l->username;
    if ($keep) { // keep you logged in
      // @TODO: Change MD5 to Blowfish
      $vstr = md5($l->username.$config['sitename'].$l->password);
      $vstr = 'username='.$l->username.'&vstr='.$vstr;
      setcookie($config['sitename'], $vstr, time() + (24*3600*31)); // logged in for 1 month
    }
    $this->o_login->getAddr();
    Act::add("Logged in from ".$this->o_login->i_raddr, $this->o_login);
    //$at = new AlertType();
    //$at->short = 'LOGIN';
    //$at->fetchFromField('short');
    //Notification::sendAlert($at, 'User Login', $this->o_login.' Logged in from '.$this->o_login->i_raddr);
    return 0;
  }

  public function logout() {
    global $config;
    global $_SESSION;
    global $_COOKIE;
    global $_SERVER;
    if ($this->isLogged) {
      if ($this->o_login) {
        $this->o_login->getAddr();
        Act::add("Logged out from ".$this->o_login->i_raddr, $this->o_login);
      }
      $this->isLogged = 0;
      if (isset($_SESSION['username'])) {
        unset($_SESSION['username']);
      }
      if (isset($_COOKIE[$config['sitename']])) {
 	unset($_COOKIE[$config['sitename']]);
	// destroy cookie
	setcookie ($config['sitename'], "", time() - 3600);
      }
      $this->o_login = NULL;
      $this->username = ""; 
    }
  }

  public function checkLogin() {
    global $_SESSION;
    global $_COOKIE;
    global $config;
    if (isset($_SESSION['username']) || isset($_COOKIE[$config['sitename']])) {
      if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $this->username = $_SESSION['username'];
        $l = new Login();
        $l->username = $_SESSION['username'];
        if ($l->fetchFromField("username")) {
          $this->isLogged = 0;
  	  $this->username = "";
  	  $_SESSION['username'] = "";
	  $this->o_login = NULL;
        } else {
          $this->o_login = $l;
          $this->isLogged = 1;
        }
      } else if (isset($_COOKIE[$config['sitename']])) {
 	$v = array();
        parse_str($_COOKIE[$config['sitename']], $v);
        $l = new Login();
        $l->username = $v['username'];
        if ($l->fetchFromField("username")) {
          $this->isLogged = 0;
          $this->username = "";
          $_SESSION['username'] = "";
          $this->o_login = NULL;
        } else {
          $vstr = $l->username.$config['sitename'].$l->password;
          // @TODO: Change MD5 to Blowfish
          $vstr = md5($vstr);
	  if (!strcmp($v['vstr'], $vstr)) {
            $this->o_login = $l;
            $this->isLogged = 1;
            $l->last_seen = time();
            $l->update();
            $_SESSION['username'] = $l->username;
	  }
        }
      }
    }
  }

  /**
   * Returns the singleton instance
   */
  public static function getInstance()
  {
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
    trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }
}

?>
