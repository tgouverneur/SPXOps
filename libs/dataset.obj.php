<?php
/**
 * Dataset object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Dataset extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $size = -1;
  public $used = -1;
  public $fk_pool = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;

  /* Logging */
  private $_log = null;
 
  public function log($str) {
    Logger::log($str, $this);
  }

  public function equals($z) {
    if (!strcmp($this->name, $z->name) && $this->fk_pool && $z->fk_pool) {
      return true;
    }
    return false;
  }

  public function fetchAll($all = 1) {

    try {
      if (!$this->o_pool && $this->fk_pool > 0) {
        $this->fetchFK('fk_pool');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    return $this->name;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_dataset';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'size' => SQL_PROPE,
                        'used' => SQL_PROPE,
                        'fk_pool' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'size' => 'size',
                        'used' => 'used',
                        'fk_pool' => 'fk_pool',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_pool", "o_pool", "Pool");

    $this->_log = Logger::getInstance();

  }

}
?>
