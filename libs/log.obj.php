<?php
/**
 * Log object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
class Log extends MySqlObj
{
  public $id = -1;
    public $msg = '';
    public $fk_login = -1;
    public $fk_what = -1;
    public $o_class = '';
    public $t_add = -1;
    public $t_upd = -1;

    public $o_login = null;
    public $o_what = null;

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

            if (!empty($this->o_class) &&
      class_exists($this->o_class) &&
      $this->fk_what > 0) {
                $oc = $this->o_class;
                $this->o_what = new $oc($this->fk_what);
                $this->o_what->fetchFromId();
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
    }

    public static function printCols($cfs = array())
    {
        return array('Who' => 'who',
                 'Message' => 'msg',
                 'On' => 'on',
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
        if ($this->o_what) {
            $rc['on'] = ''.$this->o_what->link();
        } else {
            $rc['on'] = 'unknown';
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
      $this->_table = 'list_log';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'msg' => SQL_PROPE,
                        'o_class' => SQL_PROPE,
                        'fk_login' => SQL_PROPE,
                        'fk_what' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'msg' => 'msg',
                        'o_class' => 'o_class',
                        'fk_login' => 'fk_login',
                        'fk_what' => 'fk_what',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_login", "o_login", "Login");

      $this->_log = Logger::getInstance();
  }
}
