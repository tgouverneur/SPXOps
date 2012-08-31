<?php
 /**
  * Job object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */

if (!defined('S_NONE')) {
 define ('S_NONE',   0);
 define ('S_NEW', 1);
 define ('S_RUN', 2);  
 define ('S_FAIL', 4);  
 define ('S_DONE', 8);   
}

class JobArg
{
  public $args = array();
  public function add($name, $value) {
    $this->args[$name] = $value;
    return;
  }
  public function get($name) {
    if (isset($this->args[$name])) {
      return $this->args[$name];
    } else {
      return false;
    }
  }
}


class Job extends mysqlObj
{
  public $id = -1;		/* ID in the MySQL table */
  public $class = '';
  public $fct = '';
  public $pid = -1;
  public $arg = '';
  public $state = S_NONE;
  public $fk_login = -1;
  public $fk_log = -1;
  public $t_start = -1;
  public $t_stop = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_login = null;
  public $o_log = null;
  private $_icmid = null;

  public static function fetchFirst(&$daemon) {

    $index = '`id`';
    $table = 'list_job';
    $where = "WHERE state='".S_NEW."' ORDER BY rand() LIMIT 0,1";
    $m = mysqlCM::getInstance();
    if (($idx = $m->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0])) {
	$t = $idx[0];
        $j = new Job($t['id'], $daemon);
        $j->fetchFromId();
        $j->fetchAll(1);
	return $j;
      }
    }
    return null;
  }

  public function __toString() {
    $out = $this->id.' ('.$this->o_login.') - '.$this->class.'::'.$this->fct.'('.$this->arg.')';
    if ($this->t_start > 0 && $this->t_stop > 0) {
      $out .= ' time spent: '.($this->t_stop-$this->t_start).' s';
    }
    return $out;
  }

  /* Display fct */

  public function stateStr() {
    switch($this->state) {
      case S_NONE:
	return 'No state';
	break;
      case S_NEW:
	return 'NEW';
	break;
      case S_RUN:
	return 'RUNNING';
        break;
      case S_FAIL:
	return 'FAILED';
        break;
      case S_DONE:
	return 'DONE';
        break;
    }
    return 'UNKNOWN';
  }

  public function runJob()
  {
    $this->o_log = new JobLog();
    $this->o_log->fk_job = $this->id;
    $this->o_log->o_job = $this;
    $this->o_log->insert();
    $this->fk_log = $this->o_log->id;
    $this->t_start = time();
    $this->state = S_RUN;
    $this->pid = getmypid();
    $this->update();

    if (!class_exists($this->class) || !method_exists($this->class, $this->fct)) {
      $this->state = S_FAIL;
      $this->o_log->log = 'Error, can\'t find class or method '.$this->class.'::'.$this->fct."\n";
      $this->o_log->rc = -1;
      $this->o_log->update();
      $this->update();
      return;
    }

    $c = $this->class;
    $f = $this->fct;
    try {
      $ret = $c::$f($this,$this->arg);
    } catch (Exception $e) {
      $this->log($e);
      $ret = -1;
    }

    $this->t_stop = time();
    $this->o_log->rc = $ret;

    $this->_icmid->log('finished: $this');

    if ($ret) {
      $this->state = S_FAIL;
    } else {
      $this->state = S_DONE;
    }

    /* Update job log */
    $r = $this->o_log->update();
    $this->update();
  }

  public function log($str) {
    if ($this->o_log) $this->o_log->log .= $str."\n";
    $this->o_log->update();
  }

  public function fetchAll($all=0) {

    try {

      if (!$this->o_log && $this->fk_log > 0) {
        $this->fetchFK('fk_log');
        if ($this->o_log) $this->o_log->o_job = &$this;
      }

      if (!$this->o_login && $this->fk_login > 0) {
        $this->fetchFK('fk_login');
      }

    } catch (Exception $e) {
      throw($e);
    }

  }

  public static function printCols() {
    return array('Class' => 'class',
                 'Function' => 'fct',
                 'State' => 'state',
                 'Added on' => 't_add',
                 'Updated on' => 't_upd',
                );
  }

  public function toArray() {

    return array(
                 'class' => $this->class,
                 'fct' => $this->fct,
                 'state' => $this->stateStr(),
                 't_add' => date('d-m-Y H:m:s', $this->t_add),
                 't_upd' => date('d-m-Y H:m:s', $this->t_upd),
                );
  }

  public function htmlDump() {
    return array(
        'Class' => $this->class,
        'Function' => $this->fct,
        'Argument' => $this->arg,
        'State' => $this->stateStr(),
        'PID' => $this->pid,
        'Added by' => ($this->o_login)?$this->o_login:'Unknown',
        'Started at' => date('d-m-Y H:m:s', $this->t_start),
        'Stopped at' => date('d-m-Y H:m:s', $this->t_stop),
        'Added on' => date('d-m-Y H:m:s', $this->t_add),
        'Updated on' => date('d-m-Y H:m:s', $this->t_upd),
    );
  }


  /* ctor */
  public function __construct($id=-1, $daemon=null)
  { 
    $this->id = $id;
    $this->_table = 'list_job';
    $this->_icmid = $daemon;
    $this->_my = array( 
			'id' => SQL_INDEX, 
		        'class' => SQL_PROPE,
			'fct' => SQL_PROPE,
			'pid' => SQL_PROPE,
			'arg' => SQL_PROPE,
			'state' => SQL_PROPE,
			'fk_log' => SQL_PROPE,
			'fk_login' => SQL_PROPE,
			't_start' => SQL_PROPE,
			't_stop' => SQL_PROPE,
			't_add' => SQL_PROPE,
			't_upd' => SQL_PROPE,
 		 );

    $this->_myc = array( /* mysql => class */
			'id' => 'id', 
			'class' => 'class',
			'fct' => 'fct',
			'pid' => 'pid',
			'arg' => 'arg',
			'state' => 'state',
			'fk_login' => 'fk_login',
			'fk_log' => 'fk_log',
			't_start' => 't_start',
			't_stop' => 't_stop',
			't_add' => 't_add',
			't_upd' => 't_upd',
 		 );

    $this->_addFK("fk_login", "o_login", "Login");
    $this->_addFK("fk_log", "o_log", "jobLog");

  }
}

?>
