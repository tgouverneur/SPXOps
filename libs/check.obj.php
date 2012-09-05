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
    return "OK"
  end
CODE;
  public $m_error = '';
  public $m_warn = '';
  public $f_root = 0;
  public $t_add = -1;
  public $t_upd = -1;


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
  }

}
?>
