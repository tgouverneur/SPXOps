<?php
/**
 * Update class
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
class Update
{

  public static function jobVM(&$job, $sid)
  {
      $s = new VM($sid);
      if ($s->fetchFromId()) {
          throw new SPXException('VM not found in database');
      }
      $s->_job = $job;

      if (!strcmp($s->status, 'running')) {
          $s->log($s.' is not in running state, exiting', LLOG_ERR);
          return;
      }

      try {
          $s->log("Connecting to $s", LLOG_INFO);
          $s->connect();
          $s->log("Launching the Update", LLOG_DEBUG);
          Update::vm($s);
          $s->log("Disconnecting from $s", LLOG_INFO);
          $s->disconnect();
      } catch (Exception $e) {
          throw($e);
      }
  }


  public static function jobServer(&$job, $sid)
  {
      $s = new Server($sid);
      if ($s->fetchFromId()) {
          throw new SPXException('Server not found in database');
      }
      $s->_job = $job;

      try {
          $s->log("Connecting to $s", LLOG_INFO);
          $s->connect();
          $s->log("Launching the Update", LLOG_DEBUG);
          Update::server($s);
          $s->log("Disconnecting from $s", LLOG_INFO);
          $s->disconnect();
      } catch (Exception $e) {
          throw($e);
      }
  }

    public static function vm($s, $f = null)
    {
        if (!$s) {
            throw new SPXException("Update::vm: $s is null");
        }

        if (!$s->fk_os || $s->fk_os == -1) {
            Logger::log('[!] OS for vm '.$s.' is unknown, aborting', $s, LLOG_ERR);
            return -1;
        }

        $s->fetchAll(1);

        $classname = $s->o_os->class;
        if (class_exists($classname)) {
            if ($f) {
                return $classname::update($s, $f);
            } else {
                Logger::log('Launching '.$classname.'::update', $s, LLOG_INFO);
                return $classname::update($s);
            }
        }

        return -1;
    }


    public static function server($s, $f = null)
    {
        if (!$s) {
            throw new SPXException("Update::server: $s is null");
        }

        if (!$s->fk_os || $s->fk_os == -1) {
            Logger::log('Trying to detect OS for '.$s, $s, LLOG_INFO);
            $oso = OS::detect($s);
            $s->fk_os = $oso->id;
            $s->update();
            $s->o_os = $oso;
            Logger::log('Detected OS for '.$s.' is '.$oso, $s, LLOG_INFO);
        }

        $s->fetchAll();

        $classname = $s->o_os->class;
        if (class_exists($classname)) {
            if ($f) {
                return $classname::update($s, $f);
            } else {
                Logger::log('Launching '.$classname.'::update', $s, LLOG_INFO);
                return $classname::update($s);
            }
        }

        return -1;
    }

    public static function jobCluster(&$job, $cid)
    {
        $c = new Cluster($cid);
        if ($c->fetchFromId()) {
            throw new SPXException('Cluster not found in database');
        }
        $c->_job = $job;
        $c->fetchRL('a_server');

        try {
            $c->log("Connecting to cluster $c", LLOG_INFO);
            $c->connect();
            $c->log("Launching the Update", LLOG_DEBUG);
            Update::cluster($c);
            $c->log("Disconnecting from cluster $c", LLOG_INFO);
            $c->disconnect();
        } catch (Exception $e) {
            throw($e);
        }
    }

    public static function cluster($c, $f = null)
    {
        if (!$c) {
            throw new SPXException("Update::cluster: $c is null");
        }

        if (!$c->fk_clver || $c->fk_clver == -1) {
            Logger::log('Trying to detect Cluster Version for '.$c, $c, LLOG_INFO);
            $oclv = CLVer::detect($c);
            $c->fk_clver = $oclv->id;
            $c->update();
            $c->o_clver = $oclv;
            Logger::log('Detected Cluster Version for '.$c.' is '.$oclv, $c, LLOG_INFO);
        }

        $c->fetchAll();

        $classname = $c->o_clver->class;
        if (class_exists($classname)) {
            if ($f) {
                return $classname::update($c, $f);
            } else {
                Logger::log('Launching '.$classname.'::update', $c, LLOG_INFO);

                return $classname::update($c);
            }
        }

        return -1;
    }

    public static function cleanVMs(Job &$job)
    {
        $table = "`list_vm`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `fk_server`='-1'";
        $it = new mIterator('VM', $index, $table, array('q' => $where, 'a' => array()), $cindex);
        $slog = new Server();
        $slog->_job = $job;

        while (($vm = $it->next())) {
            $vm->fetchFromId();
            if ($vm->t_upd < (time() - (3600*24*10))) {
                $job->log("Removing $vm, has not been updated in last 10 days", null, LLOG_INFO);
                $vm->delete();
            }
        }
    }

    public static function allVMs(&$job)
    {
        $s_vm = Setting::get('vm', 'enable');

        $slog = new VM();
        $slog->_job = $job;
               
        if (!$s_vm || $s_vm->value != 1) {
            Logger::log("VM Support is not enabled", $slog, LLOG_ERR);
            return -1;
        }
               
        $table = "`list_vm`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `f_upd`='1' AND `hostname`!='' AND `fk_suser`!=-1 AND `status`='running' AND `fk_os`!=-1";
        $it = new mIterator('VM', $index, $table, array('q' => $where, 'a' => array()), $cindex);
        Logger::log("[-] Looping through all relevant VMs and launching update", $slog, LLOG_INFO);

        while (($s = $it->next())) {
            $s->fetchFromId();
            $j = new Job();
            $j->class = 'Update';
            $j->fct = 'jobVM';
            $j->arg = $s->id;
            $j->state = S_NEW;
            $j->insert();
            Logger::log("Added job to update VM $s", $slog, LLOG_INFO);
        }
    }

    public static function allServers(&$job)
    {
        $table = "`list_server`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `f_upd`='1'";
        $it = new mIterator('Server', $index, $table, array('q' => $where, 'a' => array()), $cindex);
        $slog = new Server();
        $slog->_job = $job;

        while (($s = $it->next())) {
            $s->fetchFromId();
            $j = new Job();
            $j->class = 'Update';
            $j->fct = 'jobServer';
            $j->arg = $s->id;
            $j->state = S_NEW;
            $j->insert();
            Logger::log("Added job to update server $s", $slog, LLOG_INFO);
        }
    }

    public static function allClusters(&$job)
    {
        $table = "`list_cluster`";
        $index = "`id`";
        $cindex = "COUNT(`id`)";
        $where = "WHERE `f_upd`='1'";
        $it = new mIterator('Cluster', $index, $table, array('q' => $where, 'a' => array()), $cindex);
        $clog = new Cluster();
        $clog->_job = $job;

        while (($s = $it->next())) {
            $s->fetchFromId();
            $j = new Job();
            $j->class = 'Update';
            $j->fct = 'jobCluster';
            $j->arg = $s->id;
            $j->state = S_NEW;
            $j->insert();
            Logger::log("Added job to update cluster $s", $clog, LLOG_INFO);
        }
    }

    public function __construct()
    {
        throw new SPXException("Cannot instanciate Update class!");
    }
}
