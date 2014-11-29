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
  public $status = "";
  public $f_cluster = 0;
  public $fk_server = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;
  public $a_dataset = array();
  public $a_disk = array();

  /* JT attrs */
  public $slice = array();

  /* Logging */
  private $_log = null;
 
  public function log($str) {
    Logger::log($str, $this);
  }

  public function getTypeStats() {
    $ret = array();
    foreach($this->a_dataset as $ds) {
      if (!isset($ret[$ds->type])) {
        $ret[$ds->type] = $ds->used;
      } else {
        $ret[$ds->type] += $ds->used;
      }
    }
    return $ret;
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

      if ($all) {
        $this->fetchRL('a_dataset');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    return $this->name;
  }

  public function dump(&$s) {
    $this->log(sprintf("%15s: %s", 'ZPool', $this->name), LLOG_INFO);
  }

  public static function printCols() {
    return array('Name' => 'name',
	         'Size' => 'size',
	         'Used' => 'used',
	         'Free' => 'free',
		 'Status' => 'status',
		 'Server' => 'server',
		 'Added' => 't_add',
		 'Updated' => 't_upd',
		);
  }

  public function toArray() {
    if (!$this->o_server && $this->fk_server > 0) {
      $this->fetchFK('fk_server');
    }
    return array(
		'name' => $this->name,
		'size' => $this->size,
		'used' => $this->used,
		'free' => $this->size - $this->used,
		'status' => $this->status,
		'server' => ($this->o_server)?$this->o_server->name:'Unknown',
		't_add' => $this->size,
		't_upd' => $this->size,
		);
  }

  public function htmlDump() {
    if (!$this->o_server && $this->fk_server > 0) {
      $this->fetchFK('fk_server');
    }
    return array(
		'Name' => $this->name,
		'Size' => Pool::formatBytes($this->size),
		'Used' => Pool::formatBytes($this->used),
		'Free' => Pool::formatBytes($this->size - $this->used),
		'Status' => $this->status,
		'Server' => ($this->o_server)?$this->o_server->link():'Unknown',
		'Added on' => date('d-m-Y', $this->t_add),
		'Last Updated' => date('d-m-Y', $this->t_upd),
		);
  }

  public static function formatBytes($k) {
    $k /= 1024;
    if ($k < 1024) { return round($k, 2)." KB"; }
    $k = $k / 1024;
    if ($k < 1024) { return round($k, 2)." MB"; }
    $k = $k / 1024;
    if ($k < 1024) { return round($k, 2)." GB"; }
    $k = $k / 1024;
    if ($k < 1024) { return round($k, 2)." TB"; }
    $k = $k / 1024;
    return round($k, 2)." PB";
  }


  public static function formatSize($size) {
    if (!strcmp($size, 'none')) return 0;
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
      case "P":
        return round($size * 1024 * 1024 * 1024 * 1024 * 1024);
      break;
      default:
        return -1;
      break;
    }
  }

  public function delete() {

    $this->fetchAll(1);
    foreach($this->_rel as $r) {
      if ($this->{$r->ar} && count($this->{$r->ar})) {
        foreach($this->{$r->ar} as $e) {
          $e->delete();
        }
      }
    }

    parent::_delAllJT();
    parent::delete();
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
                        'status' => SQL_PROPE,
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
                        'status' => 'status',
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
