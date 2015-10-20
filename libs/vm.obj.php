<?php
/**
 * VM object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class VM extends MySqlObj
{
    use logTrait;
    use sshTrait;
    use checkTrait;
    public static $RIGHT = 'SRV';

    public $id = -1;
    public $name = '';
    public $hostname = '';
    public $status = '';
    public $xml = '';
    public $fk_server = -1;
    public $fk_os = -1;
    public $fk_suser = -1;
    public $f_upd = 0;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;
    public $o_os = null;
    public $o_suser = null;
    public $o_xml = null;

    public $a_net = array();
    public $a_disk = array();

    public $a_hostnet = array();
    public $a_hostdisk = array();
    public $a_pkg = array();
    public $a_lock = array();
    public $a_nfss = array();
    public $a_nfsm = array();
    public $a_result = array();

    public $a_sgroup = array();

    public function jsonSerialize() {
        $this->fetchAll();
        $this->getDisks();
        $ret = array(
                'name' => $this->name,
                'status' => $this->status,
                'updated' => $this->t_upd,
                'added' => $this->t_add,
                'fk_server' => $this->fk_server,
        );
        if ($this->o_server) {
            $ret['o_server'] = $this->o_server->jsonSerialize();
        }
        $ret['disks'] = array();
        foreach($this->a_disk as $disk) {
            $ret['disks'][] = $disk->file;
        }
        return $ret;
    }

    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name)) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }

            if ($all) {

                $this->fetchRL('a_pkg');
                $this->fetchRL('a_hostdisk');
                $this->fetchRL('a_hostnet');
                $this->fetchRL('a_nfss');
                $this->fetchRL('a_nfsm');

                if (!$this->o_suser && $this->fk_suser > 0) {
                    $this->fetchFK('fk_suser');
                }

                if (!$this->o_os && $this->fk_os > 0) {
                    $this->fetchFK('fk_os');
                }
            }

            $this->fetchData();
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function getDisks()
    {
        $this->a_disk = array();
        $disks = $this->data('kvm:disks');
        foreach (explode(';', $disks) as $disk) {
            if (empty($disk)) {
                continue;
            }
            $d = new VMdisk($disk);
            array_push($this->a_disk, $d);
        }

        return 0;
    }

    public function getNets()
    {
        $this->a_net = array();
        $nets = $this->data('kvm:net');
        foreach (explode(';', $nets) as $net) {
            if (empty($net)) {
                continue;
            }
            $f = explode(',', $net);
            $mac = $f[0];
            $net = $f[1];
            $model = $f[2];
            $n = new VMnet($mac, $net, $model);
            array_push($this->a_net, $n);
        }

        return 0;
    }

    public function link()
    {
        return '<a href="/view/w/vm/i/'.$this->id.'">'.$this.'</a>';
    }

    public function __toString()
    {
        return $this->name;
    }

    public function dump($s)
    {
        $s->log(sprintf("\t%15s - %s", $this->name, $this->status), LLOG_INFO);
    }

    public function parseXML()
    {
        if (empty($this->xml)) {
            return -1;
        }
        $xo = simplexml_load_string($this->xml);
        $this->o_xml = $xo;
    }

    public static function printCols($cfs = array())
    {
        return array('Name' => 'name',
                 'Status' => 'status',
                 'Server' => 'server',
                );
    }

    public function toArray($cfs = array())
    {
        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        return array(
                 'name' => $this->name,
                 'status' => $this->status,
                 'server' => ($this->o_server) ? $this->o_server->hostname : 'Unknown',
                );
    }

    public function htmlDump()
    {

        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        $ret = array(
            'Name' => $this->name,
            'Status' => $this->status,
            'Server' => ($this->o_server) ? $this->o_server->link() : 'Unknown',
            '# CPU' => $this->data('kvm:nrcpu'),
            'Memory' => Pool::formatBytes($this->data('kvm:memory') * 1024),
            'Updated on' => date('d-m-Y', $this->t_upd),
            'Added on' => date('d-m-Y', $this->t_add),
        );

        if (!empty($this->hostname)) {
            $ret['Hostname'] = $this->hostname;
        }

        return $ret;
    }

     public static function detectOSes(&$job)
     {
         $s_vm = Setting::get('vm', 'enable');
         $s_tries = Setting::get('vm', 'detect_tries');

         $slog = new VM();
         $slog->_job = $job;

         if (!$s_vm || $s_vm->value != 1) {
            Logger::log("VM Support is not enabled", $slog, LLOG_ERR);
            return -1;
         }

         if (!$s_tries) {
             $s_tries = 3;
         } else {
             $s_tries = $s_tries->value;
         }

         $table = "`list_vm`";
         $index = "`id`";
         $cindex = "COUNT(`id`)";
         $where = "WHERE `status`='running' AND `fk_server` != -1 AND `hostname`!='' AND `fk_os`=-1";
         $it = new mIterator('VM', $index, $table, array('q' => $where, 'a' => array()), $cindex);
 
         while (($s = $it->next())) {
             $s->fetchFromId();
             $s->_job = $job;
             $s->fetchData();
             $c = $s->data('detectOS:try');
             if (!$c) {
                 $c = 1;
             } else {
                 if ($c >= $s_tries) {
                     Logger::log("[!] Max OS Detection tries for $s reached, skipping...", $slog, LLOG_INFO);
                     continue;
                 }
                 $c++;
             }
             $s->setData('detectOS:try', $c);
             $found = false;
             /* Try to detect OS */
             try {
                 Logger::log("[-] Trying to connect SSH to $s", $slog, LLOG_INFO);
                 $s->connect();
                 Logger::log('[-] Trying to detect OS for '.$s, $s, LLOG_INFO);
                 $oso = OS::detect($s);
                 $s->fk_os = $oso->id;
                 $s->f_upd = 1; // switch update flag to 1
                 $s->update();
                 $s->o_os = $oso;
                 Logger::log('[-] Detected OS for '.$s.' is '.$oso, $s, LLOG_INFO);
                 $s->disconnect();

             } catch (Exception $e) {
                 Logger::log('[1] Error while trying to detect OS for '.$s, $s, LLOG_INFO);
                 /*@TODO: more logging */
             }
         }
         return 0;
     }

     public static function detectHostnames(&$job)
     {
         $s_vm = Setting::get('vm', 'enable');
         $s_tries = Setting::get('vm', 'detect_tries');
         $s_dns_search = Setting::get('vm', 'dns_search');

         $slog = new VM();
         $slog->_job = $job;

         if (!$s_vm || $s_vm->value != 1) {
            Logger::log("VM Support is not enabled", $slog, LLOG_ERR);
            return -1;
         }

         if (!$s_tries) {
             $s_tries = 3;
         } else {
             $s_tries = $s_tries->value;
         }

         if ($s_dns_search) {
             $dns_domains = preg_split('/,/', $s_dns_search->value);
         } else {
            Logger::log("No DNS Search specified, please check the settings", $slog, LLOG_ERR);
            return -1;
         }

         $table = "`list_vm`";
         $index = "`id`";
         $cindex = "COUNT(`id`)";
         $where = "WHERE `fk_server` != -1 AND `hostname`=''";
         $it = new mIterator('VM', $index, $table, array('q' => $where, 'a' => array()), $cindex);
 
         while (($s = $it->next())) {
             $s->fetchFromId();
             $s->fetchData();
             $c = $s->data('dns:try');
             if (!$c) {
                 $c = 1;
             } else {
                 if ($c >= $s_tries) {
                     Logger::log("[!] Max DNS tries for $s reached, skipping...", $slog, LLOG_INFO);
                     continue;
                 }
                 $c++;
             }
             $s->setData('dns:try', $c);
             $found = false;
             /* Try to detect domain name */
             foreach($dns_domains as $domain) {
                 $fqdn = $s->name.'.'.$domain;
                 $ret = dns_get_record($fqdn, DNS_A +  DNS_CNAME);
                 if (!$ret || !count($ret)) {
                     continue;
                 }
                 /* found */
                 $found = true;
                 $s->hostname = $fqdn;
                 $s->update();
                 Logger::log("[-] Hostname found for $s: $fqdn.", $slog, LLOG_INFO);
                 break;
             }
             if (!$found) {
                 Logger::log("[!] Hostname not found for $s", $slog, LLOG_INFO);
             }
         }
         return 0;
     }

    public static function dashboardArray($fk_os = null)
    {
        /* Optimization of last check result calculation,
           we are doing the fetch here instead of inside server.obj
           to allow the fetch for all the server at once!
        */
        $a = array();
        $m = MySqlCM::getInstance();
        $index = "`fk_vm`,`fk_check`,`t_upd`,`rc`,`f_ack`";
        $table = "(select `fk_vm`,`fk_check`,`t_upd`,`rc`,`f_ack` from `list_result` order by `t_upd` desc) a";
        /* @TODO implement OS filtering */
        if ($fk_os) {
            $where = "where `fk_vm`!=-1 group by `fk_vm`,`fk_check` order by `t_upd` desc";
        } else {
            $where = "where `fk_vm`!=-1 group by `fk_vm`,`fk_check` order by `t_upd` desc";
        }
        if (($idx = $m->fetchIndex($index, $table, $where))) {
            foreach ($idx as $t) {
                $d = new Result();
                $d->fk_check = $t["fk_check"];
                $d->fk_vm = $t["fk_vm"];
                $d->t_upd = $t["t_upd"];
                $d->f_ack = $t["f_ack"];
                $d->rc = $t["rc"];
                if (!isset($a[$d->fk_vm])) {
                    $a[$d->fk_vm] = new VM($d->fk_vm);
                    $a[$d->fk_vm]->fetchFromId();
                    $a[$d->fk_vm]->ack = false;
                }
                if (!$a[$d->fk_vm]->a_lr) {
                    $a[$d->fk_vm]->a_lr = array();
                }
                if (isset($a[$d->fk_vm]->rc)) {
                    if ($d->rc < $a[$d->fk_vm]->rc && !$d->f_ack) {
                        $a[$d->fk_vm]->rc = $d->rc;
                    }
                } else {
                    $a[$d->fk_vm]->rc = $d->rc;
                }
                if ($d->rc && $d->f_ack) {
                    $a[$d->fk_vm]->ack = true;
                }
                array_push($a[$d->fk_vm]->a_lr, $d);
            }
        }
        return $a;
    }


     public function delete() {
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


  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_vm';
      $this->_nfotable = 'nfo_vm';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'hostname' => SQL_PROPE,
                        'status' => SQL_PROPE,
                        'xml' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_suser' => SQL_PROPE,
                        'fk_os' => SQL_PROPE,
                        'f_upd' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'hostname' => 'hostname',
                        'status' => 'status',
                        'xml' => 'xml',
                        'fk_server' => 'fk_server',
                        'fk_suser' => 'fk_suser',
                        'fk_os' => 'fk_os',
                        'f_upd' => 'f_upd',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
      $this->_addFK("fk_suser", "o_suser", "SUser");
      $this->_addFK("fk_os", "o_os", "OS");

      $this->_addRL("a_result", "Result", array('id' => 'fk_vm'));
      $this->_addRL("a_pkg", "Pkg", array('id' => 'fk_vm'));
      $this->_addRL("a_hostdisk", "Disk", array('id' => 'fk_vm'));
      $this->_addRL("a_hostnet", "Net", array('id' => 'fk_vm'));
      $this->_addRL("a_nfss", "NFS", array('id' => 'fk_vm', 'CST:share' => 'type'));
      $this->_addRL("a_nfsm", "NFS", array('id' => 'fk_vm', 'CST:mount' => 'type'));

                    /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
      $this->_addJT('a_sgroup', 'SGroup', 'jt_vm_sgroup', array('id' => 'fk_vm'), array('id' => 'fk_sgroup'), array());
  }
}

