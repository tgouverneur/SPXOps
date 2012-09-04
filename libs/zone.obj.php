<?php
/**
 * Zone object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Zone extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $path = '';
  public $brand = '';
  public $iptype = '';
  public $zoneid = -1;
  public $status = '';
  public $hostname = '';
  public $fk_server = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;

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

      $this->fetchData();

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function link() {
    return '<a href="/view/w/zone/i/'.$this->id.'">'.$this.'</a>';
  }

  public function __toString() {
    return $this->name;
  }

  public function dump($s) {
    $s->log(sprintf("\t%15s (%s) - %s", $this->name, $this->brand, $this->status), LLOG_INFO);
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_zone';
    $this->_nfotable = 'nfo_zone';
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'status' => SQL_PROPE,
                        'path' => SQL_PROPE,
                        'brand' => SQL_PROPE,
                        'iptype' => SQL_PROPE,
                        'zoneid' => SQL_PROPE,
                        'hostname' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'status' => 'status',
                        'path' => 'path',
                        'brand' => 'brand',
                        'iptype' => 'iptype',
                        'zoneid' => 'zoneid',
                        'hostname' => 'hostname',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");

    $this->_log = Logger::getInstance();

  }

}
?>
