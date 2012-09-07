<?php
/**
 * Lock object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class Lock extends mysqlObj
{
  public $id = -1;
  public $fk_server = -1;
  public $fk_check = -1;
  public $fct = '';
  public $t_add = -1;

  public function fetchAll($all = 1) {

    try {

      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }
      if (!$this->o_check && $this->fk_check > 0) {
        $this->fetchFK('fk_check');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  /* Fct locking */
  public static function lockFct($fct) {
    $cl = new Lock();
    $cl->fct = $fct;
    return $cl->insert();
  }

  public static function unlockFct($fct) {
    $cl = new Lock();
    $cl->fct = $fct;
    if (!$cl->fetchFromFields(array('fk_server', 'fk_check', 'fct'))) {
      return $cl->delete();
    }
    return -1;
  }

  public static function isFctLocked($fct) {
    $cl = new Lock();
    $cl->fct = $fct;
    if ($cl->fetchFromFields(array('fk_server', 'fk_check', 'fct'))) {
      return false;
    }
    return true;
  }

  public function __toString() {
    return '';
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_lock';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'fk_check' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fct' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'fk_check' => 'fk_check',
                        'fk_server' => 'fk_server',
                        'fct' => 'fct',
                        't_add' => 't_add',
                 );

    $this->_addFK("fk_server", "o_server", "Server");
    $this->_addFK("fk_check", "o_check", "Check");

  }

}
?>
