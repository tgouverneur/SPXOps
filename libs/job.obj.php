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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
if (!defined('S_NONE')) {
    define('S_NONE',   0);
    define('S_NEW', 1);
    define('S_RUN', 2);
    define('S_FAIL', 4);
    define('S_DONE', 8);
    define('S_STALL', 16);
}

class Job extends MySqlObj
{
    public $id = -1;        /* ID in the MySQL table */
    public $class = '';
    public $fct = '';
    public $arg = '';
    public $state = S_NONE;
    public $fk_login = -1;
    public $fk_log = -1;
    public $fk_pid = -1;
    public $t_start = -1;
    public $t_stop = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_login = null;
    public $o_pid = null;
    public $o_log = null;

    public static function fetchFirst(&$daemon)
    {
        $pid = Pid::getMyPid();
        if (!$pid) {
            return;
        }
        $m = MySqlCM::getInstance();
        $args = array('pid' => $pid->id,
        );
        $ret = array('id' => -1);
        if ($m->call('getFirstJob', $args, $ret)) {
            return;
        }
        $j = null;
        if ($ret['id'] > 0) {
            $j = new Job($ret['id'], $daemon);
            $j->fetchFromId();
            $j->fetchAll(1);
            $j->o_pid = $pid;
        }
        return $j;
    }

    public function __toString()
    {
        $out = $this->id.' ('.$this->o_login.') - '.$this->class.'::'.$this->fct.'('.$this->arg.')';
        if ($this->t_start > 0 && $this->t_stop > 0) {
            $out .= ' time spent: '.($this->t_stop-$this->t_start).' s';
        }

        return $out;
    }

    public static function cleanJobs(&$job)
    {
        $t_old = time() - (3600*48); // 24h
        $table = "`list_job`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where['q'] = "WHERE (`fk_login`=-1 OR `fk_login`=2 OR `fk_login`=0) AND `t_add` <= :t_add AND (`state`=".S_FAIL." OR `state`=".S_DONE." OR `state`=".S_STALL.")";
        $where['a'] = array(':t_add' => array(0 => $t_old, 1 => PDO::PARAM_INT));

        $it = new mIterator('Job', $index, $table, $where, $cindex);
        $slog = new Server();
        $slog->_job = $job;

        while (($j = $it->next())) {
            $j->fetchFromId();
            try {
                $j->fetchFK('fk_log');
            } catch (Exception $e) {
                $j->o_log = null; // don't care... clean anyway
            }
            Logger::log("Removing job $j and its log", $slog, LLOG_INFO);
            if ($j->o_log) {
                $j->o_log->delete();
            }
            $j->delete();
        }
    }

  /* Display fct */

  public function stateStr()
  {
      switch ($this->state) {
          case S_NONE:
              return 'NONE';
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
          case S_STALL:
              return 'STALLED';
              break;
      }
      return 'UNKNOWN';
  }

    public function notify() {
        if ($this->state != S_FAIL && $this->state != S_STALL) {
            return; // No need, job's okay
        }
        $this->fetchOwner();
        $notifyFailedJobs = Setting::get('daemon', 'notifyFailedJobs');
        $notifyFailedSystemJobs = Setting::get('daemon', 'notifyFailedSystemJobs');
        if ($this->fk_login <= 0 || !$this->o_login) {
            if (!$notifyFailedSystemJobs->value) { /* nopenopenope */
                return;
            }
            Notification::sendJobFailure($this);
        } else {
            if (!$notifyFailedJobs->value) { /* nopenopenope */
                return;
            }
            Notification::sendJobFailure($this);
        }
        return;
    }

    public function runJob()
    {
        $this->o_log = new JobLog();
        $this->o_log->fk_job = $this->id;
        $this->o_log->o_job = $this;
        $this->o_log->insert();
        $this->fk_log = $this->o_log->id;
        $this->t_start = time();
        if ($this->state != S_RUN) {
            throw new SPXException('Job::runJob(): state is not RUNNING, aborting.');
        }
        if (!$this->o_pid) {
            $this->o_pid = Pid::getMyPid();
        }
        if ($this->fk_pid <= 0) {
            throw new SPXException('Job::runJob(): fk_pid was not set, aborting.');
        }
        if ($this->fk_pid != $this->o_pid->id) {
            throw new SPXException('Job::runJob(): fk_pid is not our pid!, aborting.');
        }
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
            $ret = $c::$f($this, $this->arg);
        } catch (Exception $e) {
            $this->log($e);
            $ret = -1;
        }

        $this->t_stop = time();
        $this->o_log->rc = $ret;

        if ($ret) {
            $this->state = S_FAIL;
        } else {
            $this->state = S_DONE;
        }

        /* Update job log */
        $this->o_log->update();
        $this->update();
    }

    public function log($str)
    {
        if ($this->o_log) {
            $this->o_log->log .= $str."\n";
            $this->o_log->update();
        }
    }

    public function link($short = '')
    {
        if (empty($short)) {
            return '<a href="/view/w/job/i/'.$this->id.'">'.$this.'</a>';
        } else {
            return '<a href="/view/w/job/i/'.$this->id.'">'.$short.'</a>';
        }
    }

    public function owner() {
        if ($this->o_login) {
            return ''.$this->o_login;
        }
        if ($this->fk_login > 0) {
            return $this->fk_login;
        }
        return 'system';
    }

    public function fetchOwner() {
            if (!$this->o_login && $this->fk_login > 0) {
                $this->fetchFK('fk_login');
            }
    }

    public function fetchAll($all = 0)
    {
        try {
            if (!$this->o_log && $this->fk_log > 0) {
                $this->fetchFK('fk_log');
                if ($this->o_log) {
                    $this->o_log->o_job = &$this;
                }
            }

            if (!$this->o_login && $this->fk_login > 0) {
                $this->fetchFK('fk_login');
            }

            if (!$this->o_pid && $this->fk_pid > 0) {
                $this->fetchFK('fk_pid');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public static function printCols($cfs = array())
    {
        return array('Class' => 'class',
                 'Function' => 'fct',
                 'State' => 'state',
                 'Added on' => 't_add',
                 'Updated on' => 't_upd',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'class' => $this->class,
                 'fct' => $this->fct,
                 'state' => $this->stateStr(),
                 't_add' => date('d-m-Y H:i:s', $this->t_add),
                 't_upd' => date('d-m-Y H:i:s', $this->t_upd),
                );
    }

    public function htmlDump()
    {
        return array(
        'Class' => $this->class,
        'Function' => $this->fct,
        'Argument' => $this->arg,
        'State' => $this->stateStr(),
        'PID' => $this->o_pid,
        'Added by' => ($this->o_login) ? $this->o_login : 'Unknown',
        'Started at' => ($this->t_start == -1)?"-":date('d-m-Y H:i:s', $this->t_start),
        'Stopped at' => ($this->t_stop == -1)?"-":date('d-m-Y H:i:s', $this->t_stop),
        'Added on' => date('d-m-Y H:i:s', $this->t_add),
        'Updated on' => date('d-m-Y H:i:s', $this->t_upd),
    );
    }

  /* ctor */
  public function __construct($id = -1, $daemon = null)
  {
      $this->id = $id;
      $this->_table = 'list_job';
      $this->_my = array(
            'id' => SQL_INDEX,
                'class' => SQL_PROPE,
            'fct' => SQL_PROPE,
            'arg' => SQL_PROPE,
            'state' => SQL_PROPE,
            'fk_log' => SQL_PROPE,
            'fk_login' => SQL_PROPE,
            'fk_pid' => SQL_PROPE,
            't_start' => SQL_PROPE,
            't_stop' => SQL_PROPE,
            't_add' => SQL_PROPE,
            't_upd' => SQL_PROPE,
         );

      $this->_myc = array( /* mysql => class */
            'id' => 'id',
            'class' => 'class',
            'fct' => 'fct',
            'arg' => 'arg',
            'state' => 'state',
            'fk_login' => 'fk_login',
            'fk_log' => 'fk_log',
            'fk_pid' => 'fk_pid',
            't_start' => 't_start',
            't_stop' => 't_stop',
            't_add' => 't_add',
            't_upd' => 't_upd',
         );

      $this->_addFK("fk_login", "o_login", "Login");
      $this->_addFK("fk_log", "o_log", "JobLog");
      $this->_addFK("fk_pid", "o_pid", "Pid");
  }
}
