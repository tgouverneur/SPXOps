<?php
/**
 * Check object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class Check extends mysqlObj
{
  use logTrait;
  public static $RIGHT = 'CHK';

  public $id = -1;
  public $name = '';
  public $description = '';
  public $frequency = 0;
  public $lua = <<<CODE
  function check()
    return 0;
  end
CODE;
  public $m_error = '';
  public $m_warn = '';
  public $f_root = 0;
  public $t_add = -1;
  public $t_upd = -1;

  public $a_sgroup = array();
  public $f_except = array();

  public function link() {
    return '<a href="/view/w/check/i/'.$this->id.'">'.$this.'</a>';
  }


  public function valid($new = true) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->name)) {
      $ret[] = 'Missing Name';
    } else {
      if ($new) { /* check for already-exist */
        $check = new Check();
        $check->name = $this->name;
        if (!$check->fetchFromField('name')) {
          $this->name = '';
          $ret[] = 'Check Name already exist';
          $check = null;
        }
      }
    }

    /* @TODO: Add LUA Validation code */

    if (count($ret)) {
      return $ret;
    } else {
      return null;
    }
  }


  /* Call the proper method */
  public function doCheck(&$s) {

    if ($this->isLocked($s))
      return -1;

    if ($this->lockCheck($s)) {
      return -1;
    }
    $lua = new Lua();
    $lua->registerCallback('exec', array(&$s, 'exec'));
    $lua->registerCallback('findBin', array(&$s, 'findBin'));
    $lua->registerCallback('isFile', array(&$s, 'isFile'));
    try {

      $lua->eval($this->lua);
      $ret = $lua->call("check");

      $rc = -1;
      $msg = 'Check failed';
      if (!empty($ret)) {
        if (is_numeric($ret)) {
          $rc = $ret;
  	  $msg = '';
        } else if (preg_match('/^([^:]*):([^$]*)/', $ret, $match)) {
          if (is_numeric($match[1])) {
            $rc = $match[1];
            if (isset($match[2])) {
              $msg = $match[2];
            }
          } else {
            $rc = -1;
          }
        }
      } else {
        $rc = 0;
        $msg = '';
      }
      $s->log("$this check on $s returned value: $rc ($msg)", LLOG_DEBUG);

      $r = new Result();
      $r->fk_check = $this->id;
      $r->fk_server = $s->id;
      $r->rc = $rc;
      // update message accordingly
      switch($r->rc) {
        case 0:
	  $r->f_ack = 1;
	  $r->message = '';
	  $r->details = $msg;
	break;
	case -1: // WARNING
	  $r->f_ack = 0;
	  $r->message = $this->m_warn;
	  $r->details = $msg;
	break;
	case -2: // ERROR
	  $r->f_ack = 0;
	  $r->message = $this->m_error;
	  $r->details = $msg;
	break;
	default:
	  $r->f_ack = 0;
	  $r->message = 'Unexpected return code, please check deeper...';
	  $r->details = $msg;
	break;
      }
      $done = false;
      if (isset($s->a_lr[$this->id]) &&
	  $s->a_lr[$this->id]) {
	$s->log("Last result found for $this / $s", LLOG_DEBUG);
        if ($r->equals($s->a_lr[$this->id])) { /* same result, only update t_upd */
	  $s->a_lr[$this->id]->update();
	  $done = true;
	  $s->log("We only updated check result for $this / $s", LLOG_DEBUG);
	}
      } else {
	$s->log("Check result not found for $this / $s", LLOG_DEBUG);
      }
      if (!$done) { // new check result
        $s->a_lr[$this->id] = $r;
	$s->a_lr[$this->id]->insert();
      }

    } catch (Exception $e) {
      $s->log("Error with LUA code: $e", LLOG_ERR);
      $rc = -1;
    }

    $this->unlockCheck($s);
    return $rc;
  }

  /* Check locking */
  public function lockCheck(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    $pid = Pid::getMyPid();
    if ($pid) {
      $cl->fk_pid = $pid->id;
    }
    return $cl->insert();
  }

  public function unlockCheck(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    if (!$cl->fetchFromFields(array('fk_check', 'fk_server'))) {
      return $cl->delete();
    }
    return -1;
  }

  public function isLocked(&$obj) {
    $cl = new Lock();
    $cl->fk_check = $this->id;
    $cl->fk_server = $obj->id;
    if ($cl->fetchFromFields(array('fk_check', 'fk_server'))) {
      return false;
    }
    return true;
  }


  public function fetchAll($all = 1) {

    try {
/*      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }
*/
      echo "";

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    $rc = $this->name;
    return $rc;
  }

  public function dump($s) {
    
  }

  public static function printCols() {
    return array('Name' => 'name',
                 'Description' => 'description',
                 'Frequency' => 'frequency',
                 'Need Root' => 'f_root',
                );
  }

  public function toArray() {
    global $config;
    @include_once($config['rootpath'].'/libs/functions.lib.php');
    return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 'frequency' => parseFrequency($this->frequency),
                 'f_root' => $this->f_root,
                );
  }

  public function htmlDump() {
    global $config;
    @include_once($config['rootpath'].'/libs/functions.lib.php');
    $ret = array(
        'Name' => $this->name,
        'Description' => $this->description,
        'Error Message' => $this->m_error,
        'Warning Message' => $this->m_warn,
        'Frequency' => parseFrequency($this->frequency),
        'Need root?' => ($this->f_root)?'<span class="glyphicon glyphicon-ok-sign"></span>':'<span class="icon-remove-circle"></span>',
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
    return $ret;
  }

  public function delete() {

    parent::_delAllJT();
    parent::delete();
  }

  public static function server(&$s) {
    if (!count($s->a_check))
      return;

    foreach($s->a_check as $c) {
      $s->log("Launching $c ...", LLOG_INFO);
      $c->doCheck($s);
      $s->log("$c done", LLOG_DEBUG);
    }
  }

  public static function jobServer(&$job, $sid) {

    $s = new Server($sid);
    if ($s->fetchFromId()) {
      throw new SPXException('Server not found in database');
    }
    $s->_job = $job;
    $s->fetchJT('a_sgroup');
    $s->buildCheckList();

    if (!count($s->a_check)) {
      $s->log("No checks to be done on $s, skipping...", LLOG_INFO);
      return;
    }

    try {
      $s->log("Connecting to $s", LLOG_INFO);
      $s->connect();
      $s->log("Launching the checks", LLOG_DEBUG);
      Check::server($s);
      $s->log("Disconnecting from server", LLOG_INFO);
      $s->disconnect();
    } catch (Exception $e) {
      throw($e);
    }
  }

  public static function serverChecks(&$job) {
    $table = "`list_server`";
    $index = "`id`";
    $cindex = "COUNT(`id`)";
    $where = "WHERE `f_upd`='1'";
    $it = new mIterator('Server', $index, $table, $where, $cindex);
    $slog = new Server();
    $slog->_job = $job;

    while(($s = $it->next())) {
      $s->fetchFromId();
      $j = new Job();
      $j->class = 'Check';
      $j->fct = 'jobServer';
      $j->arg = $s->id;
      $j->state = S_NEW;
      $j->insert();
      Logger::log("Added job to check server $s", $slog, LLOG_INFO);
    }
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_check';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'frequency' => SQL_PROPE,
                        'lua' => SQL_PROPE,
                        'm_error' => SQL_PROPE,
                        'm_warn' => SQL_PROPE,
                        'f_root' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'description' => 'description',
                        'frequency' => 'frequency',
                        'lua' => 'lua',
                        'm_error' => 'm_error',
                        'm_warn' => 'm_warn',
                        'f_root' => 'f_root',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

//    $this->_addFK("fk_server", "o_server", "Server");

    $this->_log = Logger::getInstance();

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_sgroup', 'SGroup', 'jt_check_sgroup', array('id' => 'fk_check'), array('id' => 'fk_sgroup'), array('f_except'));

  }

}
?>
