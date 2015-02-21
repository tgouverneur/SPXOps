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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class Net extends MySqlObj
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
    public $fk_zone = -1;
    public $fk_net = -1;
    public $fk_switch = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;
    public $o_zone = null;
    public $o_net = null;
    public $o_switch = null;

  /* temp for display */
  public $a_addr = array();

    public function getSwitch()
    {
        if (!$this->o_net && $this->fk_net > 0) {
            $this->fetchFK('fk_net');
        }
        if (!$this->o_net) {
            return;
        }
        $this->o_net->fetchAll();

        $switch = $this->o_net->o_switch;

        return $switch;
    }

    public function equals($z)
    {
        if ($this->version == $z->version &&
        $this->layer == $z->layer &&
        $this->fk_server == $z->fk_server &&
        $this->fk_switch == $z->fk_switch &&
        $this->fk_zone == $z->fk_zone &&
        !strcmp($this->ifname, $z->ifname) &&
        !strcmp($this->netmask, $z->netmask) &&
        !strcmp($this->address, $z->address)) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }

            if (!$this->o_switch && $this->fk_switch > 0) {
                $this->fetchFK('fk_switch');
            }

            if (!$this->o_zone && $this->fk_zone > 0) {
                $this->fetchFK('fk_zone');
            }

            if (!$this->o_net && $this->fk_net > 0) {
                $this->fetchFK('fk_net');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        $rc = $this->ifname;
        if (!empty($this->alias)) {
            $rc .= ':'.$this->alias;
        }
        if ($this->o_zone) {
            $rc .= '/'.$this->o_zone;
        }
        $rc .= '/'.$this->address;
        if (!empty($this->netmask)) {
            $rc .= '/'.$this->netmask;
        }

        return $rc;
    }

    public function dump($s)
    {
        if ($this->layer == 2) {
            $s->log(sprintf("\t%15s - %s", '[layer2]', ''.$this), LLOG_INFO);
            if ($this->o_net) {
                $this->o_net->fetchAll(1);
            }
            if ($this->o_net && $this->o_net->o_switch) {
                $s->log(sprintf("\t\t\t%s", "-> Connected on ".$this->o_net->o_switch."/".$this->o_net), LLOG_INFO);
            }
        } else {
            $s->log(sprintf("\t%15s - %s", '[layer3]', ''.$this), LLOG_INFO);
        }
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
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
                        'fk_zone' => SQL_PROPE,
                        'fk_switch' => SQL_PROPE,
                        'fk_net' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
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
                        'fk_zone' => 'fk_zone',
                        'fk_switch' => 'fk_switch',
                        'fk_net' => 'fk_net',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
      $this->_addFK("fk_net", "o_net", "Net");
      $this->_addFK("fk_switch", "o_switch", "NSwitch");
      $this->_addFK("fk_zone", "o_zone", "Zone");

      $this->_log = Logger::getInstance();
  }
}
