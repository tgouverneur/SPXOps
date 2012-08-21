<?php
/**
 * Pool object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Pool extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $type = '';
  public $size = -1;
  public $used = -1;
  public $f_cluster = 0;
  public $fk_server = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;
  public $a_dataset = array();

  /* JT attrs */
  public $slice = array();

  /* Logging */
  private $_log = null;
 
  public function log($str) {
    Logger::log($str, $this);
  }

  public function equals($z) {
    if (!strcmp($this->name, $z->name) && $this->fk_server && $z->fk_server) {
      return true;
    }
    return false;
  }

  public function fetchAll($all = 1) {

    try {
      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    return $this->name;
  }

  public function dump(&$s) {
  }

  public static function formatSize($size) {
    $unit = strtoupper($size[strlen($size) - 1]);
    if (is_numeric($unit)) {
      return $size;
    }
    $size[strlen($size) - 1] = ' ';
    switch ($unit) {
      case "K":
        return round($size * 1024);
      break;
      case "M":
        return round($size * 1024 * 1024);
      break;
      case "G":
        return round($size * 1024 * 1024 * 1024);
      break;
      case "T":
        return round($size * 1024 * 1024 * 1024 * 1024);
      break;
      default:
        return -1;
      break;
    }
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_pool';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'type' => SQL_PROPE,
                        'size' => SQL_PROPE,
                        'used' => SQL_PROPE,
                        'f_cluster' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'type' => 'type',
                        'size' => 'size',
                        'used' => 'used',
                        'f_cluster' => 'f_cluster',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");

    $this->_addRL("a_dataset", "Dataset", array('id' => 'fk_pool'));

   	        /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_disk', 'Disk', 'jt_disk_pool', array('id' => 'fk_pool'), array('id' => 'fk_disk'), array('slice'));

    $this->_log = Logger::getInstance();

  }

}
?>
