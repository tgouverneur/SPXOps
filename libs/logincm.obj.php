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

/*
 * PBKDF2 functions are ripped from:
 * Password Hashing With PBKDF2 (http://crackstation.net/hashing-security.htm).
 * Copyright (c) 2013, Taylor Hornby
 * All rights reserved.
 */

define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTE_SIZE", 24);
define("PBKDF2_HASH_BYTE_SIZE", 24);
   
define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);


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
        $this->checkEnforceSSL();
        session_start();
        $this->checkLogin();
        $this->checkAPIKey();
        if ($this->o_login) {
            $this->o_login->getAddr();
            $this->o_login->fetchData();
        }
    }

    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function checkEnforceSSL() {
        if ($this->isHTTPS()) {
            return true;
        }
        $enforceSSL = Setting::get('general', 'enforceSSL');
        if (!$enforceSSL || !$enforceSSL->value) {
            return true;
        }
        /* redirects to https */
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("Location: $redirect");
        die();
    }

    public function login($username, $password, $otp = '', $keep = 0)
    {
        $l = new Login();
        $l->username = $username;
        if ($l->fetchFromField("username")) {
            return -1;
        }
        if (!$l->f_active) {
            return -1;
        }
        if ($l->auth($password) === false) {
            return -1;
        }
        if (!LoginCM::_isPBKDF2($l->password)) {
            /**
             * This is not the good hash type, we need to update it now that we know the password is correct
             * This will just silently transition from MD5 to PBKDF2
             **/
            $l->password = LoginCM::_hashPBKDF2($password);
            $l->update();
        }
        if ($l->fk_utoken > 0) {
            $l->fetchFK('fk_utoken');
            $uto = $l->o_utoken;
            if ($uto->f_init > 0) {
                if (!$uto->checkValue($otp)) {
                    return -2;
                }
            }
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

    public static function requestLogin() {
        if (!isset($_SESSION['ORIG_REQUEST']) && empty($_SESSION['ORIG_REQUEST'])) {
            $_SESSION['ORIG_REQUEST'] = $_SERVER['REQUEST_URI'];
        }
        /* Redirect to login page.. */
        HTTP::redirect('/login/r/1');
    }

    public static function getOriginalRequest() {
        if (isset($_SESSION['ORIG_REQUEST']) && !empty($_SESSION['ORIG_REQUEST'])) {
            $uri = $_SESSION['ORIG_REQUEST'];
            unset($_SESSION['ORIG_REQUEST']);
            return $uri;
        }
        return null;
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
                    $l->t_last = time();
                    $l->update();
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
                        $l->t_last = time();
                        $l->update();
                        $_SESSION['username'] = $l->username;
                    }
                }
            }
        }
    }

        
    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */
    private static function _pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
    {
        $algorithm = strtolower($algorithm);
        if(!in_array($algorithm, hash_algos(), true))
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        if($count <= 0 || $key_length <= 0)
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

        if (function_exists("hash_pbkdf2")) {
            // The output length is in NIBBLES (4-bits) if $raw_output is false!
            if (!$raw_output) {
                $key_length = $key_length * 2;
            }
            return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if($raw_output)
            return substr($output, 0, $key_length);
        else
            return bin2hex(substr($output, 0, $key_length));
    }

    public static function _isPBKDF2($hash) {
        if (preg_match('/^'.PBKDF2_HASH_ALGORITHM.'/', $hash)) {
            return true;
        }
        return false;
    }

    public static function _hashPBKDF2($password)
    {
        // format: algorithm:iterations:salt:hash
        $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
        return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
            base64_encode(LoginCM::_pbkdf2(
                PBKDF2_HASH_ALGORITHM,
                $password,
                $salt,
                PBKDF2_ITERATIONS,
                PBKDF2_HASH_BYTE_SIZE,
                true
            ));
    }

    public static function _validatePBKDF2($password, $correct_hash)
    {
        $params = explode(":", $correct_hash);
        if(count($params) < HASH_SECTIONS)
           return false;
        $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
        return LoginCM::_slowEquals(
            $pbkdf2,
            LoginCM::_pbkdf2(
                $params[HASH_ALGORITHM_INDEX],
                $password,
                $params[HASH_SALT_INDEX],
                (int)$params[HASH_ITERATION_INDEX],
                strlen($pbkdf2),
                true
            )
        );
    }

    // Compares two strings $a and $b in length-constant time.
    private static function _slowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
        {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
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

?>
