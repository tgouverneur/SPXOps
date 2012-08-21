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
  public $t_add = -1;
  public $t_upd = -1;

  public function __toString() {
    return $this->username;
  }

  public function auth($pwd) {
    $pwd_sha1 = sha1($pwd);
    if (!strcmp($pwd_sha1, $this->password)) {
      return TRUE;
    } else {
      return FALSE;
    }
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
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );
  }

}
?>
