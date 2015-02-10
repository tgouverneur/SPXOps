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
    public $fk_login = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_login = null;

    public function equals($z)
    {
        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_login && $this->fk_login > 0) {
                $this->fetchFK('fk_login');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        $rc = $this->msg;

        return $rc;
    }

    public function dump($s)
    {
        //    $s->log(sprintf("\t%15s - %s", '[layer3]', ''.$this), LLOG_INFO);
    }

    public static function add($msg, $obj = null)
    {
        $act = new Act();
        $act->msg = $msg;
        $act->fk_login = $obj->id;
        $act->insert();

        return $act;
    }

    public static function printCols($cfs = array())
    {
        return array('Who' => 'who',
                 'Message' => 'msg',
                 'When' => 't_add',
                );
    }

    public function toArray($cfs = array())
    {
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

        return $rc;
    }

    public function html()
    {
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

        return $rc;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_act';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'msg' => SQL_PROPE,
                        'fk_login' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'msg' => 'msg',
                        'fk_login' => 'fk_login',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_login", "o_login", "Login");

      $this->_log = Logger::getInstance();
  }
}
