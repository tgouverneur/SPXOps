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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class Dataset extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $size = -1;
    public $available = -1;
    public $reserved = -1;
    public $used = -1;
    public $uchild = -1;
    public $type = '';
    public $fk_pool = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_pool = null;

    public function getSnapshotName() {
        if (strcmp($this->type, 'snapshot')) {
            return null;
        }
        $f = preg_split('/@/', $this->name);
        if (count($f) != 2) {
            return null;
        }
        return $f[1];
    }

    public function getFilesystemName($full=0) {
        if (strcmp($this->type, 'snapshot')) {
            if ($full) {
                return $this->getFullName();
            } else {
                return $this->name;
            }
        }
        if ($full) {
            $f = preg_split('/@/', $this->getFullName());
        } else {
            $f = preg_split('/@/', $this->name);
        }
        if (count($f) != 2) {
            return null;
        }
        return $f[0];
    }

    public function getFullName()
    {
        $ret = '';
        if ($this->o_pool) {
            $ret .= $this->o_pool.'/';
        }
        $ret .= $this->name;

        return $ret;
    }

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name) && $this->fk_pool && $z->fk_pool) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            $this->fetchData();

            if (!$this->o_pool && $this->fk_pool > 0) {
                $this->fetchFK('fk_pool');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        return $this->name;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_dataset';
      $this->_nfotable = 'nfo_dataset';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'size' => SQL_PROPE,
                        'available' => SQL_PROPE,
                        'reserved' => SQL_PROPE,
                        'used' => SQL_PROPE,
                        'uchild' => SQL_PROPE,
                        'type' => SQL_PROPE,
                        'fk_pool' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'size' => 'size',
                        'available' => 'available',
                        'reserved' => 'reserved',
                        'used' => 'used',
                        'uchild' => 'uchild',
                        'type' => 'type',
                        'fk_pool' => 'fk_pool',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_pool", "o_pool", "Pool");

  }
}
