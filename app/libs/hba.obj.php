<?php
/**
 * Hba object
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
class Hba extends MySqlObj
{
  public $id = -1;
    public $wwn = '';
    public $vendor = '';
    public $model = '';
    public $firmware = '';
    public $fcode = '';
    public $serial = '';
    public $drv = '';
    public $drv_ver = '';
    public $state = '';
    public $osdev = '';
    public $curspeed = '';
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->wwn, $z->wwn) && $this->fk_server && $z->fk_server) {
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
        return $this->wwn;
    }

    public function dump($s)
    {
        $s->log(sprintf("\t%15s %s", '['.$this->vendor.']', 'Model '.$this->model.' / WWN: '.$this->wwn), LLOG_INFO);
        $s->log(sprintf("\t\t\t %s", 'Firmware: '.$this->firmware.' / FC: '.$this->fcode.' / Driver: '.$this->drv.' v'.$this->drv_ver), LLOG_INFO);
        $s->log(sprintf("\t\t\t %s", 'State: '.$this->state.' / Speed: '.$this->curspeed), LLOG_INFO);
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_hba';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'wwn' => SQL_PROPE|SQL_EXIST,
                        'vendor' => SQL_PROPE,
                        'model' => SQL_PROPE,
                        'firmware' => SQL_PROPE,
                        'fcode' => SQL_PROPE,
                        'serial' => SQL_PROPE,
                        'drv' => SQL_PROPE,
                        'drv_ver' => SQL_PROPE,
                        'state' => SQL_PROPE,
                        'osdev' => SQL_PROPE,
                        'curspeed' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'wwn' => 'wwn',
                        'vendor' => 'vendor',
                        'model' => 'model',
                        'firmware' => 'firmware',
                        'fcode' => 'fcode',
                        'serial' => 'serial',
                        'drv' => 'drv',
                        'drv_ver' => 'drv_ver',
                        'state' => 'state',
                        'osdev' => 'osdev',
                        'curspeed' => 'curspeed',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");

      $this->_log = Logger::getInstance();
  }
}
