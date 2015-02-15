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
 */
class VM extends MySqlObj
{
  use logTrait;
    public static $RIGHT = 'SRV';

    public $id = -1;
    public $name = '';
    public $status = '';
    public $xml = '';
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;
    public $o_xml = null;

    public $a_net = array();
    public $a_disk = array();


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
                        'status' => SQL_PROPE,
                        'xml' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'status' => 'status',
                        'xml' => 'xml',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
  }
}
