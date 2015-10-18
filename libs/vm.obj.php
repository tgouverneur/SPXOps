<?php
/**
 * VM object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class VM extends MySqlObj
{
  use logTrait;
    public static $RIGHT = 'SRV';

    public $id = -1;
    public $name = '';
    public $hostname = '';
    public $status = '';
    public $xml = '';
    public $fk_server = -1;
    public $fk_os = -1;
    public $fk_suser = -1;
    public $f_upd = 0;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;
    public $o_xml = null;

    public $a_net = array();
    public $a_disk = array();

    public $a_hostnet = array();
    public $a_hostdisk = array();
    public $a_pkg = array();
    public $a_lock = array();
    public $a_nfss = array();
    public $a_nfsm = array();
    public $a_result = array();

    public $a_sgroup = array();

    public function jsonSerialize() {
        $this->fetchAll();
        $this->getDisks();
        $ret = array(
                'name' => $this->name,
                'status' => $this->status,
                'updated' => $this->t_upd,
                'added' => $this->t_add,
                'fk_server' => $this->fk_server,
        );
        if ($this->o_server) {
            $ret['o_server'] = $this->o_server->jsonSerialize();
        }
        $ret['disks'] = array();
        foreach($this->a_disk as $disk) {
            $ret['disks'][] = $disk->file;
        }
        return $ret;
    }

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name)) {
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

            if ($all) {

                $this->fetchRL('a_pkg');
                $this->fetchRL('a_hostdisk');
                $this->fetchRL('a_hostnet');
                $this->fetchRL('a_nfss');
                $this->fetchRL('a_nfsm');

                if (!$this->o_suser && $this->fk_suser > 0) {
                    $this->fetchFK('fk_suser');
                }

                if (!$this->o_os && $this->fk_os > 0) {
                    $this->fetchFK('fk_os');
                }
            }

            $this->fetchData();
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function getDisks()
    {
        $this->a_disk = array();
        $disks = $this->data('hw:disks');
        foreach (explode(';', $disks) as $disk) {
            if (empty($disk)) {
                continue;
            }
            $d = new VMdisk($disk);
            array_push($this->a_disk, $d);
        }

        return 0;
    }

    public function getNets()
    {
        $this->a_net = array();
        $nets = $this->data('hw:net');
        foreach (explode(';', $nets) as $net) {
            if (empty($net)) {
                continue;
            }
            $f = explode(',', $net);
            $mac = $f[0];
            $net = $f[1];
            $model = $f[2];
            $n = new VMnet($mac, $net, $model);
            array_push($this->a_net, $n);
        }

        return 0;
    }

    public function link()
    {
        return '<a href="/view/w/vm/i/'.$this->id.'">'.$this.'</a>';
    }

    public function __toString()
    {
        return $this->name;
    }

    public function dump($s)
    {
        $s->log(sprintf("\t%15s - %s", $this->name, $this->status), LLOG_INFO);
    }

    public function parseXML()
    {
        if (empty($this->xml)) {
            return -1;
        }
        $xo = simplexml_load_string($this->xml);
        $this->o_xml = $xo;
    }

    public static function printCols($cfs = array())
    {
        return array('Name' => 'name',
                 'Status' => 'status',
                 'Server' => 'server',
                );
    }

    public function toArray($cfs = array())
    {
        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        return array(
                 'name' => $this->name,
                 'status' => $this->status,
                 'server' => ($this->o_server) ? $this->o_server->hostname : 'Unknown',
                );
    }

    public function htmlDump()
    {

        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        $ret = array(
    'Name' => $this->name,
    'Status' => $this->status,
    'Server' => ($this->o_server) ? $this->o_server->link() : 'Unknown',
    '# CPU' => $this->data('hw:nrcpu'),
    'Memory' => Pool::formatBytes($this->data('hw:memory') * 1024),
    'Updated on' => date('d-m-Y', $this->t_upd),
    'Added on' => date('d-m-Y', $this->t_add),
    );

        return $ret;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_vm';
      $this->_nfotable = 'nfo_vm';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'hostname' => SQL_PROPE,
                        'status' => SQL_PROPE,
                        'xml' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_suser' => SQL_PROPE,
                        'fk_os' => SQL_PROPE,
                        'f_upd' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'hostname' => 'hostname',
                        'status' => 'status',
                        'xml' => 'xml',
                        'fk_server' => 'fk_server',
                        'fk_suser' => 'fk_suser',
                        'fk_os' => 'fk_os',
                        'f_upd' => 'f_upd',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");

      $this->_addRL("a_pkg", "Pkg", array('id' => 'fk_vm'));
      $this->_addRL("a_hostdisk", "Disk", array('id' => 'fk_vm'));
      $this->_addRL("a_hostnet", "Net", array('id' => 'fk_vm'));
      $this->_addRL("a_nfss", "NFS", array('id' => 'fk_vm', 'CST:share' => 'type'));
      $this->_addRL("a_nfsm", "NFS", array('id' => 'fk_vm', 'CST:mount' => 'type'));

                    /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
      $this->_addJT('a_sgroup', 'SGroup', 'jt_vm_sgroup', array('id' => 'fk_vm'), array('id' => 'fk_sgroup'), array());
  }
}
