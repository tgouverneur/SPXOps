<?php
/**
 * Login object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Login extends mysqlObj
{
  public $id = -1;
  public $username = '';
  public $password = '';
  public $fullname = '';
  public $f_admin = 0;
  public $f_ldap = 0;
  public $t_last = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public function __toString() {
    return $this->username;
  }

  public function bcrypt($input, $rounds = 7)
  {
    $salt = "";
    $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
    for($i=0; $i < 22; $i++) {
      $salt .= $salt_chars[array_rand($salt_chars)];
    }
    $this->password = crypt($input, sprintf('$2y$%02d$', $rounds) . $salt);
  }

  public function auth($pwd) {
    if (crypt($pwd, $this->password) == $this->password) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function printCols() {
    return array('Username' => 'username',
                 'Fullname' => 'fullname',
                 'Admin' => 'f_admin',
                 'LDAP' => 'f_ldap',
                 'Added' => 't_add',
                );
  }

  public function toArray() {

    return array(
                 'username' => $this->username,
                 'fullname' => $this->fullname,
                 'f_admin' => $this->f_admin,
                 'f_ldap' => $this->f_ldap,
                 't_add' => date('d-m-Y', $this->t_add),
                );
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_login';
    $this->_nfotable = NULL;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'username' => SQL_PROPE|SQL_EXIST,
                        'password' => SQL_PROPE,
                        'fullname' => SQL_PROPE,
                        'f_admin' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'username' => 'username',
                        'password' => 'password',
                        'fullname' => 'fullname',
                        'f_admin' => 'f_admin',
                        'f_ldap' => 'f_ldap',
                        't_last' => 't_last',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );
  }

}
?>
