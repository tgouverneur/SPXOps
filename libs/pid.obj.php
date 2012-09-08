<?php
/**
 * Pid object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Pid extends mysqlObj
{
  public $id = -1;
  public $agent = '';
  public $pid = 0;
  public $ppid = -1;
  public $f_master = 0;
  public $t_add = -1;
  public $t_upd = -1;


  public static function ping(&$d) {
    global $config;
    $pid = new Pid();
    $pid->agent = $config['agentname'];
    $pid->pid = $d->pid;
    $pid->ppid = $d->ppid;
    $pid->f_master = $d->f_master;
    if ($pid->fetchFromFields(array('pid', 'agent'))) {
      $pid->insert(); // New PID
    }
    if ($pid->f_master != $d->f_master) {
      $pid->f_master = $d->f_master;
    }
    $pid->update(); // Ping !
  }

  public static function stop(&$d) {
    global $config;
    $pid = new Pid();
    $pid->agent = $config['agentname'];
    $pid->pid = $d->pid;
    $pid->f_master = $d->f_master;
    if ($pid->fetchFromFields(array('pid', 'agent'))) {
      return; // do nothing as we stop...
    }
    $pid->delete();
    return;
  }

  public static function check(&$d) {
    global $config;
    $pids = Pid::getAll(true, array('agent' => 'CST:'.$config['agentname']));
    $cnt = count($pids);
    foreach($pids as $pid) {
      if (!posix_kill($pid->pid, 0)) {
	/* Get every lock for this pid.. */
        $locks = Lock::getAll(true, array('fk_pid' => 'CST:'.$pid->id));
        foreach($locks as $lock) {
	  Logger::log("Removed dead lock $lock for pid $pid", $d, LLOG_DEBUG);
	  $lock->delete();
	}
        $pid->delete();
	$cnt--;
	Logger::log("Pid $pid has been detected as stopped...", $d, LLOG_DEBUG);
      }
    }
    $d->curProcess = $cnt;
  }

  public function equals($z) {
    if ($z->pid == $this->pid &&
	!strcmp($z->master, $this->master)) {
      return true;
    } 
    return false;
  }

  public function fetchAll($all = 1) {

    try {
      echo '';
    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    return $this->agent.':'.$this->pid;
  }

  public static function printCols() {
    return array('Agent' => 'agent',
                 'PID' => 'pid',
                 'Parent PID' => 'ppid',
                 'Master' => 'f_master',
                 'Last seen' => 't_upd',
                 'Started' => 't_add',
                );
  }

  public function toArray() {

    return array(
	    'agent' => $this->agent,
	    'pid' => $this->pid,
	    'ppid' => $this->ppid,
	    'f_master' => $this->f_master,
	    't_upd' => (time() - $this->t_upd).' sec ago',
	    't_add' => date('Y-m-d H:m:s', $this->t_add),
	   );
  }

  public static function getMyPid() {
    global $config;
    $pid = new Pid();
    $pid->agent = $config['agentname'];
    $pid->pid = posix_getpid();
    if ($pid->fetchFromFields(array('pid', 'agent'))) {
      return null;
    }
    return $pid;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_pid';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'agent' => SQL_PROPE,
                        'pid' => SQL_PROPE,
                        'ppid' => SQL_PROPE,
                        'f_master' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'agent' => 'agent',
                        'pid' => 'pid',
                        'ppid' => 'ppid',
                        'f_master' => 'f_master',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_log = Logger::getInstance();
  }

}
?>
