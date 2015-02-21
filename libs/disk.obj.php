<?php
/**
 * Disk object
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
class Disk extends MySqlObj
{
  public $id = -1;
    public $dev = '';
    public $vdev = ''; /* Powerpath dev or other path-aggregated dev */
  public $drv = '';
    public $serial = '';
    public $vendor = '';
    public $product = '';
    public $rev = '';
    public $size = -1;
    public $lunid = '';
    public $f_local = 1;
    public $f_san = 0;
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;

  /* JT Attrs */
  public $slice = array();
    public $role = array();

    public $a_pool = array();

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->dev, $z->dev) && $this->fk_server && $z->fk_server) {
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
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        return $this->dev;
    }

    public function dump(&$s)
    {
        $this->log(sprintf("\t%15s: %s %s GB (%s %s) - %s LUN: %s", 'Disk', $this->dev, round($this->size / 1024 / 1024 / 1024, 1),
                            $this->vendor, $this->product, $this->serial, $this->lunid, LLOG_INFO));
    }

    public static function printCols($cfs = array())
    {
        return array('Device' => 'dev',
                 'Vendor' => 'vendor',
                 'Size' => 'size',
                 'LunID' => 'lunid',
                 'on SAN?' => 'san',
                 'Details' => 'details',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'dev' => $this->dev,
                 'serial' => $this->serial,
                 'vendor' => $this->vendor,
                 'product' => $this->product,
                 'size' => Pool::formatBytes($this->size),
                 'lunid' => $this->lunid,
                 'san' => ($this->f_san) ? '<span class="glyphicon glyphicon-ok-sign"></span>' : '<span class="glyphicon glyphicon-remove-circle"></span>',
                 'details' => '<a href="/view/w/disk/i/'.$this->id.'">View</a>',
                 't_add' => date('d-m-Y', $this->t_add),
                );
    }

    public function delete()
    {
        parent::_delAllJT();
        parent::delete();
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_disk';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'dev' => SQL_PROPE|SQL_EXIST,
                        'vdev' => SQL_PROPE,
                        'drv' => SQL_PROPE,
                        'serial' => SQL_PROPE,
                        'vendor' => SQL_PROPE,
                        'product' => SQL_PROPE,
                        'rev' => SQL_PROPE,
                        'size' => SQL_PROPE,
                        'lunid' => SQL_PROPE,
                        'f_local' => SQL_PROPE,
                        'f_san' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'dev' => 'dev',
                        'vdev' => 'vdev',
                        'drv' => 'drv',
                        'serial' => 'serial',
                        'rev' => 'rev',
                        'vendor' => 'vendor',
                        'product' => 'product',
                        'size' => 'size',
                        'lunid' => 'lunid',
                        'f_local' => 'f_local',
                        'f_san' => 'f_san',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_pool', 'Pool', 'jt_disk_pool', array('id' => 'fk_disk'), array('id' => 'fk_pool'), array('slice', 'role'));
  }
}
