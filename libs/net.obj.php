<?php
/**
 * Net object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Net extends mysqlObj
{
  public $id = -1;
  public $ifname = '';
  public $alias = '';
  public $layer = 3; /* 3 == ip, 2 == ether */
  public $version = 4;
  public $address = '';
  public $netmask = '';
  public $group = '';
  public $flags = '';
  public $f_ipmp = 0;
  public $fk_server = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;

  public function equals($z) {
    if ($this->version == $z->version &&
        $this->layer == $z->layer &&
        !strcmp($this->ifname, $z->ifname) &&
        !strcmp($this->netmask, $z->netmask) &&
        !strcmp($this->address, $z->address)) {
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
    $rc = $this->ifname;
    if (!empty($this->alias)) {
      $rc .= ':'.$this->alias;
    }
    $rc .= '/'.$this->address;
    if (!empty($this->netmask)) $rc .= '/'.$this->netmask;
    return $rc;
  }

  public function dump($s) {
    $s->log(sprintf("\t%15s - %s", ($this->layer == 2)?'[layer2]':'[layer3]', ''.$this), LLOG_INFO);
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_net';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'ifname' => SQL_PROPE,
                        'alias' => SQL_PROPE,
                        'layer' => SQL_PROPE,
                        'version' => SQL_PROPE,
                        'address' => SQL_PROPE,
                        'netmask' => SQL_PROPE,
                        'group' => SQL_PROPE,
                        'flags' => SQL_PROPE,
                        'f_ipmp' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'ifname' => 'ifname',
                        'alias' => 'alias',
                        'layer' => 'layer',
                        'version' => 'version',
                        'address' => 'address',
                        'netmask' => 'netmask',
                        'group' => 'group',
                        'flags' => 'flags',
                        'f_ipmp' => 'f_ipmp',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");

    $this->_log = Logger::getInstance();

  }

}
?>
