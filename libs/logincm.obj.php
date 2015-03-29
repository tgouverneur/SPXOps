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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class LoginCM
{
  /**
   * Singleton variable
   */
  private static $_instance;
    public $username = "";
    public $isLogged = 0;
    public $o_login = null;

    public function startSession()
    {
        session_start();
        $this->checkLogin();
        $this->checkAPIKey();
        if ($this->o_login) {
            $this->o_login->getAddr();
            $this->o_login->fetchData();
        }
    }

    public function login($username, $password, $keep = 0)
    {
        $l = new Login();
        $l->username = $username;
        if ($l->fetchFromField("username")) {
            return -1;
        }
        if ($l->auth($password) === false) {
            return -1;
        }
        $this->isLogged = 1;
        $this->o_login = $l;
        $l->t_last = time();
        $l->update();
        $this->username = $l->username;
        if (isset($_SESSION)) {
            $_SESSION['username'] = $l->username;
        }
        if ($keep) { // keep you logged in
            $vstr = md5($l->username.Config::$sitename.$l->password);
            $vstr = 'username='.$l->username.'&vstr='.$vstr;
            setcookie(Config::$sitename, $vstr, time() + (24*3600*31)); // logged in for 1 month
        }
        $this->o_login->getAddr();
        Act::add("Logged in from ".$this->o_login->i_raddr, $this->o_login);
    return 0;
    }

    public function logout()
    {
        if ($this->isLogged) {
            if ($this->o_login) {
                $this->o_login->getAddr();
                Act::add("Logged out from ".$this->o_login->i_raddr, $this->o_login);
            }
            $this->isLogged = 0;
            if (isset($_SESSION['username'])) {
                unset($_SESSION['username']);
            }
            if (isset($_COOKIE[Config::$sitename])) {
                unset($_COOKIE[Config::$sitename]);
                // destroy cookie
                setcookie(Config::$sitename, "", time() - 3600);
            }
            $this->o_login = null;
            $this->username = "";
        }
    }

    public function checkAPIKey() 
    {
        if (isset($_POST['username']) && isset($_POST['apikey']) &&
            !empty($_POST['username']) && !empty($_POST['apikey'])) {

            $username = $_POST['username'];
            $apikey = $_POST['apikey'];
            $l = new Login();
            $l->username = $username;
            if ($l->fetchFromField('username')) {
                $this->isLogged = 0;
                $this->username = '';
                $_SESSION['username'] = '';
                $this->o_login = null;
                return false;
            }
            if ($l->f_api && !strcmp($apikey, $l->getAPIKey())) {

                $this->o_login = $l;
                $this->isLogged = 1;
                return true;
            }
        }
        return false;
    }

    public function checkLogin()
    {
        if (isset($_SESSION['username']) || isset($_COOKIE[Config::$sitename])) {
            if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
                $this->username = $_SESSION['username'];
                $l = new Login();
                $l->username = $_SESSION['username'];
                if ($l->fetchFromField("username")) {
                    $this->isLogged = 0;
                    $this->username = "";
                    $_SESSION['username'] = "";
                    $this->o_login = null;
                } else {
                    $this->o_login = $l;
                    $this->isLogged = 1;
                }
            } elseif (isset($_COOKIE[Config::$sitename])) {
                $v = array();
                parse_str($_COOKIE[Config::$sitename], $v);
                $l = new Login();
                $l->username = $v['username'];
                if ($l->fetchFromField("username")) {
                    $this->isLogged = 0;
                    $this->username = "";
                    $_SESSION['username'] = "";
                    $this->o_login = null;
                } else {
                    $vstr = $l->username.Config::$sitename.$l->password;
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
          self::$_instance = new $c();
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
