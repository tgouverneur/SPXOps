<?php
/**
 * Check object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class Check extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $description = '';
  public $frequency = 0;
  public $lua = <<<CODE
  function check()
    return 0;
  end
CODE;
  public $m_error = '';
  public $m_warn = '';
  public $f_root = 0;
  public $t_add = -1;
  public $t_upd = -1;

  public $a_sgroup = array();
  public $f_except = array();


  public function valid($new = true) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->name)) {
      $ret[] = 'Missing Name';
    } else {
      if ($new) { /* check for already-exist */
        $check = new Check();
        $check->name = $this->name;
        if (!$check->fetchFromField('name')) {
          $this->name = '';
          $ret[] = 'Check Name already exist';
          $check = null;
        }
      }
    }

    /* @TODO: Add LUA Validation code */

    if (count($ret)) {
      return $ret;
    } else {
      return null;
    }
  }


  /* Call the proper method */
  public function doCheck(&$s) {

    if ($this->isLocked($s))
      return -1;

    if ($this->lockCheck($s)) {
      return -1;
    }
    $lua = new Lua();
    $lua->registerCallback('exec', array(&$s, 'exec'));
    $lua->registerCallback('findBin', array(&$s, 'findBin'));
    $lua->registerCallback('isFile', array(&$s, 'isFile'));
    try {

      $lua->eval($this->lua);
      $rc = $lua->call("check");

    } catch (Exception $e) {
      $s->log("Error with LUA code: $e", LLOG_ERR);
      $rc = -1;
    }

    $this->unlockCheck($s);
    return $rc;
  }

  /* Check locking */
  public function lockCheck(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    return $cl->insert();
  }

  public function unlockCheck(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    if (!$cl->fetchFromId()) {
      return $cl->delete();
    }
    return -1;
  }

  public function isLocked(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    if ($cl->fetchFromId()) {
      return false;
    }
    return true;
  }


  public function fetchAll($all = 1) {

    try {
/*      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }
*/
      echo "";

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    $rc = $this->name;
    return $rc;
  }

  public function dump($s) {
    
  }

  public static function printCols() {
    return array('Name' => 'name',
                 'Description' => 'description',
                 'Frequency' => 'frequency',
                 'Need Root' => 'f_root',
                );
  }

  public function toArray() {
    return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 'frequency' => $this->frequency,
                 'f_root' => $this->f_root,
                );
  }

  public function htmlDump() {
    $ret = array(
        'Name' => $this->name,
        'Description' => $this->description,
        'Error Message' => $this->m_error,
        'Warning Message' => $this->m_warn,
        'Frequency' => $this->frequency,
        'Need root?' => ($this->f_root)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>',
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
    return $ret;
  }



 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_check';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'frequency' => SQL_PROPE,
                        'lua' => SQL_PROPE,
                        'm_error' => SQL_PROPE,
                        'm_warn' => SQL_PROPE,
                        'f_root' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'description' => 'description',
                        'frequency' => 'frequency',
                        'lua' => 'lua',
                        'm_error' => 'm_error',
                        'm_warn' => 'm_warn',
                        'f_root' => 'f_root',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

//    $this->_addFK("fk_server", "o_server", "Server");

    $this->_log = Logger::getInstance();

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_sgroup', 'SGroup', 'jt_check_sgroup', array('id' => 'fk_check'), array('id' => 'fk_sgroup'), array('f_except'));

  }

}
?>
