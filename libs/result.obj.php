<?php
/**
 * Result object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class Result extends mysqlObj
{
  public $id = -1;
  public $rc = 0;
  public $message = '';
  public $details = '';
  public $f_ack = 0;
  public $fk_check = -1;
  public $fk_server = -1;
  public $fk_login = -1;
  public $t_add = -1;
  public $t_upd = -1;



  public function fetchAll($all = 1) {

    try {
      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }

      if (!$this->o_check && $this->fk_check > 0) {
        $this->fetchFK('fk_check');
      }

      if (!$this->o_login && $this->fk_login > 0) {
        $this->fetchFK('fk_login');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    $rc = '';
    return $rc;
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_result';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'rc' => SQL_PROPE,
                        'message' => SQL_PROPE,
                        'details' => SQL_PROPE,
                        'f_ack' => SQL_PROPE,
                        'fk_check' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_login' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'rc' => 'rc',
                        'message' => 'message',
                        'details' => 'details',
                        'f_ack' => 'f_ack',
                        'fk_check' => 'fk_check',
                        'fk_server' => 'fk_server',
                        'fk_login' => 'fk_login',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");
    $this->_addFK("fk_check", "o_check", "Check");
    $this->_addFK("fk_login", "o_login", "Login");

  }

}
?>
