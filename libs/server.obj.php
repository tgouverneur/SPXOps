<?php
/**
 * Server object
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
class Server extends MySqlObj implements JsonSerializable
{
    use logTrait;
    use sshTrait;
    use checkTrait;
    public static $RIGHT = 'SRV';

    public $id = -1;
    public $hostname = '';
    public $description = '';
    public $fk_pserver = -1;
    public $fk_os = -1;
    public $fk_suser = -1;
    public $fk_cluster = -1;
    public $f_rce = 0;
    public $f_upd = 0;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_pserver = null;
    public $o_os = null;
    public $o_suser = null;
    public $o_cluster = null;
    public $fk_zone = array(); // JT attribute for cluster's rg

    public $a_sgroup = array();
    public $a_zone = array();
    public $a_vm = array();
    public $a_patch = array();
    public $a_pkg = array();
    public $a_net = array();
    public $a_prj = array();
    public $a_hba = array();
    public $a_disk = array();
    public $a_pool = array();
    public $a_rrd = array();
    public $a_result = array();

    public $a_nfss = array(); /* nfs shares */
    public $a_nfsm = array(); /* nfs mount */

    /* Logging */
    public $_job = null;

    /* VM Stats */
    public $vm_nb = 0;
    public $vm_cores = 0;
    public $vm_mem = 0;

    public function getSQDN() {
        $ret = $this->hostname;
        if (preg_match('/\./', $this->hostname)) {
            $ret = preg_split('/\./', $this->hostname);
            $ret = $ret[0];
        }
        return $ret;
    }

    public function getRRD($path)
    {
        foreach ($this->a_rrd as $rrd) {
            if (!strcmp($rrd->path, $path)) {
                return $rrd;
            }
        }

        return;
    }

    public function vmStats()
    {
        $this->vm_nb = count($this->a_vm);
        $this->vm_core = 0;
        $this->vm_mem = 0;
        foreach ($this->a_vm as $vm) {
            //$vm->fetchData();
            $this->vm_core += $vm->data('kvm:nrcpu', true);
            $this->vm_mem += $vm->data('kvm:memory', true);
        }
    }

    public function getExtraActions()
    {
        if ($this->o_os) {
            $class = $this->o_os->class;

            return $class::$extraActions;
        }

        return array();
    }

    public function equals($z)
    {
        if (!strcmp($this->hostname, $z->hostname)) {
            return true;
        }

        return false;
    }

    public function valid($new = true)
    { /* validate form-based fields */
        $ret = array();

        if (empty($this->hostname)) {
            $ret[] = 'Missing Hostname';
        } else {
            if ($new) { /* check for already-exist */
                $check = new Server();
                $check->hostname = $this->hostname;
                if (!$check->fetchFromField('hostname')) {
                    $this->hostname = '';
                    $ret[] = 'Server Hostname already exist';
                    $check = null;
                }
            }
        }

        if (empty($this->fk_pserver)) {
            $ret[] = 'Missing Physical Server specification';
        } else {
            if ($this->fk_pserver == -1) {
                $check = new PServer();
                $check->name = $this->hostname;
                if ($check->fetchFromField('name')) {
                    $this->fk_pserver = -2;
                } else {
                    $this->fk_pserver = $check->id;
                }
            } else {
                $check = new PServer($this->fk_pserver);
                if ($check->fetchFromId()) {
                    $this->fk_pserver = -1;
                    $ret[] = 'Physical Server not found in database';
                    $check = null;
                }
            }
        }

        if (empty($this->fk_suser)) {
            $ret[] = 'Missing SSH User specification';
        } else {
            $check = new SUser($this->fk_suser);
            if ($check->fetchFromId()) {
                $this->fk_suser = -1;
                $ret[] = 'Specified SSH User not found in database';
                $check = null;
            }
        }

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public function log($str, $level)
    {
        Logger::log($str, $this, $level);
    }

    public function getNetworks()
    {
        $ret = array();

        foreach ($this->a_net as $net) {
            if (!$net->layer == 2) {
                continue;
            }
            if (!isset($ret[$net->ifname])) {
                $ret[$net->ifname] = $net;
                $net->fetchAll();
            }
        }
        foreach ($this->a_net as $net) {
            if ($net->layer == 2) {
                continue;
            }
            $net->fetchAll();
            if (isset($ret[$net->ifname])) {
                $ret[$net->ifname]->a_addr[] = $net;
            }
        }

        return $ret;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_os && $this->fk_os > 0) {
                $this->fetchFK('fk_os');
            }

            if (!$this->o_pserver && $this->fk_pserver > 0) {
                $this->fetchFK('fk_pserver');
                if ($all && $this->o_pserver) {
                    $this->o_pserver->fetchAll($all);
                }
            }

            if (!$this->o_suser && $this->fk_suser > 0) {
                $this->fetchFK('fk_suser');
            }

            if (!$this->o_cluster && $this->fk_cluster > 0) {
                $this->fetchFK('fk_cluster');
            }

            if ($all) {
                $this->fetchRL('a_zone');
                $this->fetchRL('a_vm');
                $this->fetchRL('a_patch');
                $this->fetchRL('a_pkg');
                $this->fetchRL('a_nfss');
                $this->fetchRL('a_nfsm');
                $this->fetchRL('a_net');
                $this->fetchRL('a_prj');
                $this->fetchRL('a_hba');
                $this->fetchRL('a_disk');
                $this->fetchRL('a_pool');
                $this->fetchRL('a_rrd');
            }

            $this->fetchData();
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function delete()
    {
        $this->fetchAll(1);
        $this->fetchRL('a_result');
        foreach ($this->_rel as $r) {
            if ($this->{$r->ar} && count($this->{$r->ar})) {
                foreach ($this->{$r->ar} as $e) {
                    $e->delete();
                }
            }
        }

        parent::_delAllJT();
        parent::delete();
    }

    public function link()
    {
        return '<a href="/view/w/server/i/'.$this->id.'">'.$this.'</a>';
    }

    public function __toString()
    {
        return $this->hostname;
    }

    public function countDiskSpace($exclSan = false, $exclLocal = false)
    {
        $size = 0;
        foreach ($this->a_disk as $disk) {
            if ($exclSan && !$disk->f_local) {
                continue;
            }
            if ($exclLocal && $disk->f_local) {
                continue;
            }

            $size += $disk->size;
        }

        return $size;
    }

    public function dump()
    {

        /* echo basic infos first */
        $this->log(sprintf("%15s: %s", 'Server', $this->hostname.' ('.$this->id.')'), LLOG_INFO);
        $this->log(sprintf("%15s: %s", 'Description', $this->description), LLOG_INFO);
        $this->log(sprintf("%15s: %s", 'RCE', ($this->f_rce) ? "enabled" : "disabled"), LLOG_INFO);

        if ($this->o_os) {
            $this->o_os->dump($this);
        }

        /* dump FKs */
        if ($this->o_pserver) {
            $this->o_pserver->dump($this);
        }

        if ($this->o_suser) {
            $this->o_suser->dump($this);
        }

        foreach(array(
                        'a_zone' => 'Zones',
                        'a_rrd' => 'RRDs',
                        'a_vm' => 'VMs',
                     ) as $v => $n) {

            if (count($this->{$v})) {
                $this->log('', LLOG_INFO);
                $this->log(sprintf("%15s:", $n), LLOG_INFO);
                foreach ($this->{$v} as $z) {
                    $z->dump($this);
                }
            }
        }

        /* Network */
        $defrouter = $this->data('net:defrouter');
        if (!$defrouter || empty($defrouter)) {
            $defrouter = null;
        }
        $this->log('', LLOG_INFO);
        $this->log(sprintf("%15s: %s", "Network", ($defrouter) ? ' (GW='.$defrouter.')' : ''), LLOG_INFO);

        foreach(array(
                        'a_net' => 'Net Ifs',
                        'a_hba' => 'HBAs',
                        'a_disk' => 'Disks',
                        'a_nfsm' => 'NFS Mounts',
                        'a_nfss' => 'NFS Shares',
                     ) as $v => $n) {

            if (count($this->{$v})) {
                $this->log('', LLOG_INFO);
                $this->log(sprintf("%15s:", $n), LLOG_INFO);
                foreach ($this->{$v} as $z) {
                    $z->dump($this);
                }
            }
        }

        $this->log('', LLOG_INFO);
        $this->log(sprintf("%15s: Total internal capacity: %d GBytes", "Disks", round($this->countDiskSpace(true) / 1024 / 1024 / 1024, 1)), LLOG_INFO);
        $this->log(sprintf("%15s: Total SAN provisionned: %d GBytes", "Disks", round($this->countDiskSpace(false, true) / 1024 / 1024 / 1024, 1)), LLOG_INFO);

    }

    public static function printCols($cfs = array())
    {
        $defaults = array('Hostname' => 'hostname',
                 'Description' => 'description',
                 'OS' => 'os',
                 'Update?' => 'f_upd',
                 'RCE' => 'f_rce',
                );

        $optional = array(
            '# VM' => 'nrvms',
            '# VM Cores' => 'nrvmscores',
            '# VM Memory' => 'nrvmsram',
            'OS Version' => 'osver',
            'OS Kernel' => 'oskernel',
        );

        if (!is_array($cfs) && !strcmp($cfs, 'all')) {
            return array_merge($defaults, $optional);
        }

        if (!count($cfs)) {
            return $defaults;
        }

        $ret = array();
        foreach ($cfs as $col) {
            foreach ($defaults as $n => $v) {
                if (!strcmp($col, $v)) {
                    $ret[$n] = $v;
                }
            }
            foreach ($optional as $n => $v) {
                if (!strcmp($col, $v)) {
                    $ret[$n] = $v;
                }
            }
        }

        return $ret;
    }

    public function toArray($cfs = array())
    {
        $ret = array();

        foreach ($cfs as $c) {
            switch ($c) {
                case 'hostname':
                case 'description':
                case 'f_rce':
                case 'f_upd':
                      $ret[$c] = $this->{$c};
                break;
                case 'osver':
                  if (!$this->dataCount()) {
                      $this->fetchData();
                  }
                  if (!$this->o_os && $this->fk_os > 0) {
                      $this->fetchFK('fk_os');
                  }
                  if ($this->o_os) {
                      $spec = $this->o_os->htmlDump($this);

                      if (isset($spec['Version'])) {
                          $ret['osver'] = $spec['Version'];
                      } else {
                          $ret['osver'] = 'N/A';
                      }
                  }
                break;
                case 'oskernel':
                  if (!$this->dataCount()) {
                      $this->fetchData();
                  }
                  if (!$this->o_os && $this->fk_os > 0) {
                      $this->fetchFK('fk_os');
                  }
                  if ($this->o_os) {
                      $spec = $this->o_os->htmlDump($this);
                      if (isset($spec['Kernel'])) {
                          $ret['oskernel'] = $spec['Kernel'];
                      } else {
                          $ret['oskernel'] = 'N/A';
                      }
                  }
                break;
                case 'os':
                  if (!$this->o_os && $this->fk_os > 0) {
                      $this->fetchFK('fk_os');
                  }
                  if ($this->o_os) {
                      $ret['os'] = ($this->o_os) ? $this->o_os->name : 'Unknown';
                  }
                break;
                case 'nrvms':
                  if (!count($this->a_vm)) {
                      $this->fetchRL('a_vm');
                      $this->vmStats();
                  }
                  $ret[$c] = $this->vm_nb;
                break;
                case 'nrvmscores':
                      if (!count($this->a_vm)) {
                          $this->fetchRL('a_vm');
                          $this->vmStats();
                      }
                      $ret[$c] = $this->vm_core;
                break;
                case 'nrvmsram':
                  if (!count($this->a_vm)) {
                      $this->fetchRL('a_vm');
                      $this->vmStats();
                  }
                  $ret[$c] = Pool::formatBytes($this->vm_mem * 1024);
                break;
          }
        }
        return $ret;
    }

    public function htmlDump()
    {
        if (count($this->a_vm)) {
            $this->vmStats();
        }
        $ret = array(
            'Hostname' => $this->hostname,
            'Description' => $this->description,
            'Update?' => ($this->f_upd) ? '<span class="glyphicon glyphicon-ok-sign"></span>' : '<span class="glyphicon glyphicon-remove-circle"></span>',
            'RCE' => ($this->f_rce) ? '<span class="glyphicon glyphicon-ok-sign"></span>' : '<span class="glyphicon glyphicon-remove-circle"></span>',
            'Updated on' => date('d-m-Y', $this->t_upd),
            'Added on' => date('d-m-Y', $this->t_add),
        );
        if ($this->vm_nb) {
            $ret['# VM'] = $this->vm_nb;
            $ret['# VM Cores'] = $this->vm_core;
            $ret['# VM Memory'] = Pool::formatBytes($this->vm_mem * 1024);
        }
        $l_up = $this->data('os:boottime');
        if ($l_up) {
            $now = time();
            $l_up = $now - $l_up;
            $l_days = floor($l_up / 86400);
            $l_up -= ($l_days * 86400);
            $l_hours = floor(($l_up / 3600));
            $l_up -= ($l_hours * 3600);
            $l_min = floor(($l_up / 60));
            $l_up -= ($l_min * 60);
            $l_sec = $l_up;
            $msg = '%d days, %d:%d:%d';
            $msg = sprintf($msg, $l_days, $l_hours, $l_min, $l_sec);
            $ret['Uptime'] = $msg;
        }
        if ($this->o_cluster) {
            $ret['Cluster'] = '<a href="/view/w/cluster/i/'.$this->o_cluster->id.'">'.$this->o_cluster.'</a>';
        }

        return $ret;
    }

    public function jsonSerialize()
    {
        $ret = array(
                'id' => $this->id,
                'hostname' => $this->hostname,
                
           );
        $ret['fk_os'] = $this->fk_os;

        if ($this->o_os) {
            $ret['os_name'] = $this->o_os->name;
        }
        if ($this->a_pool && count($this->a_pool)) {
            $ret['a_pool'] = array();
            foreach($this->a_pool as $pool) {
                $ret['a_pool'][] = $pool->jsonSerialize();
            }
        }
        return $ret;
    }

    public static function dashboardArray($fk_os = null)
    {
        /* Optimization of last check result calculation,
       we are doing the fetch here instead of inside server.obj
       to allow the fetch for all the server at once!
    */
    $a = array();
        $m = MySqlCM::getInstance();
        $index = "`fk_server`,`fk_check`,`t_upd`,`rc`,`f_ack`";
        $table = "(select `fk_server`,`fk_check`,`t_upd`,`rc`,`f_ack` from `list_result` order by `t_upd` desc) a";
        /* @TODO implement OS filtering */
        if ($fk_os) {
            $where = "where `fk_server`!=-1 group by `fk_server`,`fk_check` order by `t_upd` desc";
        } else {
            $where = "where `fk_server`!=-1 group by `fk_server`,`fk_check` order by `t_upd` desc";
        }
        if (($idx = $m->fetchIndex($index, $table, $where))) {
            foreach ($idx as $t) {
                $d = new Result();
                $d->fk_check = $t["fk_check"];
                $d->fk_server = $t["fk_server"];
                $d->t_upd = $t["t_upd"];
                $d->f_ack = $t["f_ack"];
                $d->rc = $t["rc"];
                if (!isset($a[$d->fk_server])) {
                    $a[$d->fk_server] = new Server($d->fk_server);
                    $a[$d->fk_server]->fetchFromId();
                    $a[$d->fk_server]->ack = false;
                }
                if (!$a[$d->fk_server]->a_lr) {
                    $a[$d->fk_server]->a_lr = array();
                }
                if (isset($a[$d->fk_server]->rc)) {
                    if ($d->rc < $a[$d->fk_server]->rc && !$d->f_ack) {
                        $a[$d->fk_server]->rc = $d->rc;
                    }
                } else {
                    $a[$d->fk_server]->rc = $d->rc;
                }
                if ($d->rc && $d->f_ack) {
                    $a[$d->fk_server]->ack = true;
                }
                array_push($a[$d->fk_server]->a_lr, $d);
            }
        }
        return $a;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_server';
      $this->_nfotable = 'nfo_server';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'hostname' => SQL_PROPE|SQL_EXIST,
                        'description' => SQL_PROPE,
                        'fk_pserver' => SQL_PROPE,
                        'fk_os' => SQL_PROPE,
                        'fk_suser' => SQL_PROPE,
                        'fk_cluster' => SQL_PROPE,
                        'f_rce' => SQL_PROPE,
                        'f_upd' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'hostname' => 'hostname',
                        'description' => 'description',
                        'fk_cluster' => 'fk_cluster',
                        'fk_pserver' => 'fk_pserver',
                        'fk_os' => 'fk_os',
                        'fk_suser' => 'fk_suser',
                        'f_rce' => 'f_rce',
                        'f_upd' => 'f_upd',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_suser", "o_suser", "SUser");
      $this->_addFK("fk_os", "o_os", "OS");
      $this->_addFK("fk_pserver", "o_pserver", "PServer");
      $this->_addFK("fk_cluster", "o_cluster", "Cluster");

      $this->_addRL("a_zone", "Zone", array('id' => 'fk_server'));
      $this->_addRL("a_vm", "VM", array('id' => 'fk_server'));
      $this->_addRL("a_patch", "Patch", array('id' => 'fk_server'));
      $this->_addRL("a_pkg", "Pkg", array('id' => 'fk_server'));
      $this->_addRL("a_net", "Net", array('id' => 'fk_server'));
      $this->_addRL("a_prj", "Prj", array('id' => 'fk_server'));
      $this->_addRL("a_hba", "Hba", array('id' => 'fk_server'));
      $this->_addRL("a_disk", "Disk", array('id' => 'fk_server'));
      $this->_addRL("a_pool", "Pool", array('id' => 'fk_server'));
      $this->_addRL("a_rrd", "RRD", array('id' => 'fk_server'));
      $this->_addRL("a_result", "Result", array('id' => 'fk_server'));

      $this->_addRL("a_nfss", "NFS", array('id' => 'fk_server', 'CST:share' => 'type'));
      $this->_addRL("a_nfsm", "NFS", array('id' => 'fk_server', 'CST:mount' => 'type'));

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_sgroup', 'SGroup', 'jt_server_sgroup', array('id' => 'fk_server'), array('id' => 'fk_sgroup'), array());

  }
}
