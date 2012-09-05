<?php
/**
 * Act object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Act extends mysqlObj
{
  public $id = -1;
  public $msg = '';
  public $fk_server = -1;
  public $fk_zone = -1;
  public $fk_cluster = -1;
  public $fk_login = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;
  public $o_zone = null;
  public $o_cluster = null;
  public $o_login = null;

  public function equals($z) {
    return false;
  }

  public function fetchAll($all = 1) {

    try {

      if (!$this->o_login && $this->fk_login > 0) {
        $this->fetchFK('fk_login');
      }

      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }

      if (!$this->o_zone && $this->fk_zone > 0) {
        $this->fetchFK('fk_zone');
      }

      if (!$this->o_cluster && $this->fk_cluster > 0) {
        $this->fetchFK('fk_cluster');
      }


    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    $rc = $this->msg;
    return $rc;
  }

  public function dump($s) {
  //    $s->log(sprintf("\t%15s - %s", '[layer3]', ''.$this), LLOG_INFO);
  }

  public static function add($msg, $type='', $obj=null) {
    $act = new Act();
    $act->msg = $msg;
    switch($type) {
      case 'zone':
	$act->fk_zone = $obj->id;
      break;
      case 'login':
	$act->fk_login = $obj->id;
      break;
      case 'cluster':
	$act->fk_cluster = $obj->id;
      break;
      case 'server':
	$act->fk_server = $obj->id;
      break;
      default:
      break;
    }
    $act->insert();
    return $act;
  }

  public static function printCols() {
    return array('Who' => 'who',
                 'Message' => 'msg',
                 'What' => 'what',
                 'When' => 't_add',
                );
  }

  public function toArray() {

    $rc = array();

    try {
      $this->fetchAll();
    } catch (Exception $e) {
      // do nothing
    }

    if ($this->o_login) {
      $rc['who'] = ''.$this->o_login->link();
    } else {
      $rc['who'] = 'unknown';
    }

    $rc['msg'] = $this->msg;
    $rc['t_add'] = date('d-m-Y H:m:s', $this->t_add);
    
    $rc['what'] = '-';
    if ($this->o_server) {
      $rc['what'] = $this->o_server->link();
    }
    if ($this->o_zone) {
      $rc['what'] = $this->o_zone->link();
    }
    if ($this->o_cluster) {
      $rc['what'] = $this->o_cluster->link();
    }
    return $rc;
  }

  public function html() {

    $rc = '';
    try {
      $this->fetchAll();
    } catch (Exception $e) {
      // do nothing
    }

    if ($this->o_login) {
      $rc .= '['.$this->o_login->link().'] ';
    }
    $rc .= $this->msg;

    if ($this->o_server) {
      $rc .= 'Server '.$this->o_server->link();
    }
    if ($this->o_zone) {
      $rc .= 'Zone '.$this->o_zone->link();
    }
    if ($this->o_cluster) {
      $rc .= 'Cluster '.$this->o_cluster->link();
    }
    return $rc;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_act';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'msg' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_zone' => SQL_PROPE,
                        'fk_cluster' => SQL_PROPE,
                        'fk_login' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'msg' => 'msg',
                        'fk_server' => 'fk_server',
                        'fk_zone' => 'fk_zone',
                        'fk_cluster' => 'fk_cluster',
                        'fk_login' => 'fk_login',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");
    $this->_addFK("fk_zone", "o_zone", "Zone");
    $this->_addFK("fk_cluster", "o_cluster", "Cluster");
    $this->_addFK("fk_login", "o_login", "Login");

    $this->_log = Logger::getInstance();

  }

}
?>
