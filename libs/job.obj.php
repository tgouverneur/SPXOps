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
  public $fk_owner = -1;
  public $t_start = -1;
  public $t_stop = -1;
  public $pid = -1;
  public $fk_log = -1;
  public $arg = '';
  public $t_add = -1;
  public $state = S_NONE;

  public $o_owner = null;
  private $_icmid = null;
  private $_jlog = null;

  public function __toString() {
    $out = $this->id.' ('.$this->o_owner.') - '.$this->class.'::'.$this->fct.'('.$this->arg.')';
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

  /* process fct */
  public function fetchLogin() {
    $this->o_owner = new Login($this->fk_owner);
    return $this->o_owner->fetchFromId();
  }

  public function runJob()
  {
    $this->_jlog = new JobLog();
    $this->_jlog->insert();
    $this->fk_log = $this->_jlog->id;
    $this->t_start = time();
    $this->state = S_RUN;
    $this->pid = getmypid();
    $this->update();

    if (!class_exists($this->class) || !method_exists($this->class, $this->fct)) {
      $this->state = S_FAIL;
      $this->_jlog->log = 'Error, can't find class or method '.$this->class.'::'.$this->fct."\n";
      $this->_jlog->rc = -1;
      $this->_jlog->insert();
      $this->fk_log = $this->_jlog->id;
      $this->update();
      return;
    }

    $c = $this->class;
    $f = $this->fct;
    $ret = $c::$f($this,$this->arg);

    $this->t_stop = time();
    $this->_jlog->rc = $ret;

    $this->_icmid->log('finished: $this');

    if ($ret) {
      $this->state = S_FAIL;
    } else {
      $this->state = S_DONE;
    }

    /* Update job log */
    $r = $this->_jlog->update();
    $this->update();
  }

  public function log($str) {
    if ($this->_jlog) $this->_jlog->log .= $str."\n";
    $this->_jlog->update();
  }

  /* ctor */
  public function __construct($id=-1, $daemon=null)
  { 
    $this->id = $id;
    $this->_table = 'jobs';
    $this->_icmid = $daemon;

    $this->t_add = time();

    $this->_my = array( 
			'id' => SQL_INDEX, 
		        'class' => SQL_PROPE,
			'fct' => SQL_PROPE,
			'fk_owner' => SQL_PROPE,
			't_start' => SQL_PROPE,
			't_stop' => SQL_PROPE,
			'pid' => SQL_PROPE,
			'fk_log' => SQL_PROPE,
			'arg' => SQL_PROPE,
			't_add' => SQL_PROPE,
			'state' => SQL_PROPE
 		 );


    $this->_myc = array( /* mysql => class */
			'id' => 'id', 
			'class' => 'class',
			'fct' => 'fct',
			'pid' => 'pid',
			'arg' => 'arg',
			'state' => 'state'
			'fk_owner' => 'fk_owner',
			'fk_log' => 'fk_log',
			't_start' => 't_start',
			't_stop' => 't_stop',
			't_add' => 't_add',
 		 );
  }
}

?>
