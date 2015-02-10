<?php
/**
 * RJob object
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage rjob
 * @category classes
 * @filesource
 */
class RJob extends MySqlObj
{
  public $id = -1;        /* ID in the MySQL table */
  public $class = '';
    public $fct = '';
    public $arg = '';
    public $frequency = -1;
    public $fk_login = -1;
    public $t_last = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_login = null;
    private $_icmid = null;

    public function __toString()
    {
        return $this->class.':'.$this->fct.'('.$this->arg.') every '.$this->frequency;
    }

    public function addIt()
    {
        $jo = new Job();
        $jo->class = $this->class;
        $jo->fct = $this->fct;
        $jo->arg = $this->arg;
        $jo->fk_login = $this->fk_login;
        $jo->state = S_NEW;
        $jo->insert();

        return;
    }

    public function valid($new = true)
    { /* validate form-based fields */
    global $config;
        $ret = array();

        if (empty($this->class)) {
            $ret[] = 'Missing Class';
        } else {
            try {
                if (!class_exists($this->class)) {
                    $ret[] = 'Wrong class specified';
                    $this->class = '';
                } else {
                    if (empty($this->fct)) {
                        $ret[] = 'Missing Function';
                    } else {
                        try {
                            if (!method_exists($this->class, $this->fct)) {
                                $ret[] = 'Wrong Function specified';
                                $this->fct = '';
                            }
                        } catch (Exception $e) {
                            $ret[] = 'Wrong Function specified';
                            $this->fct = '';
                        }
                    }
                }
            } catch (Exception $e) {
                $this->class = '';
                $ret[] = 'Wrong class specified';
            }
        }

        if ($this->frequency <= 0) {
            $ret[] = 'Frequency can not be unspecified';
        }

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public function fetchAll($all = 0)
    {
        try {
            if (!$this->o_login && $this->fk_login > 0) {
                $this->fetchFK('fk_login');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public static function printCols($cfs = array())
    {
        return array('Class' => 'class',
                 'Function' => 'fct',
                 'Frequency' => 'frequency',
                 'Last run' => 't_last',
                 'Next run' => 't_next',
                 'Updated on' => 't_upd',
                );
    }

    public function toArray($cfs = array())
    {
        global $config;
        @include_once $config['rootpath'].'/libs/functions.lib.php';

        return array(
                 'class' => $this->class,
                 'fct' => $this->fct,
                 'frequency' => parseFrequency($this->frequency),
                 't_last' => date('d-m-Y H:m:s', $this->t_last),
                 't_next' => date('d-m-Y H:m:s', $this->t_last + $this->frequency),
                 't_upd' => date('d-m-Y H:m:s', $this->t_upd),
                );
    }

    public function htmlDump()
    {
        global $config;
        @include_once $config['rootpath'].'/libs/functions.lib.php';

        return array(
        'Class' => $this->class,
        'Function' => $this->fct,
        'Argument' => $this->arg,
        'frequency' => parseFrequency($this->frequency),
        'Added by' => ($this->o_login) ? $this->o_login : 'Unknown',
        'Last run at' => date('d-m-Y H:m:s', $this->t_last),
        'Added on' => date('d-m-Y H:m:s', $this->t_add),
        'Updated on' => date('d-m-Y H:m:s', $this->t_upd),
    );
    }

  /* ctor */
  public function __construct($id = -1, $daemon = null)
  {
      $this->id = $id;
      $this->_table = 'list_rjob';
      $this->_icmid = $daemon;
      $this->_my = array(
            'id' => SQL_INDEX,
                'class' => SQL_PROPE,
            'fct' => SQL_PROPE,
            'arg' => SQL_PROPE,
            'frequency' => SQL_PROPE,
            'fk_login' => SQL_PROPE,
            't_last' => SQL_PROPE,
            't_add' => SQL_PROPE,
            't_upd' => SQL_PROPE,
         );

      $this->_myc = array( /* mysql => class */
            'id' => 'id',
            'class' => 'class',
            'fct' => 'fct',
            'arg' => 'arg',
            'frequency' => 'frequency',
            'fk_login' => 'fk_login',
            't_last' => 't_last',
            't_add' => 't_add',
            't_upd' => 't_upd',
         );

      $this->_addFK("fk_login", "o_login", "Login");
  }
}
