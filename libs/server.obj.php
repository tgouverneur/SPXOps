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
 */


class Server extends mysqlObj implements JsonSerializable
{
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

  public $a_sgroup = array();
  public $a_zone = array();
  public $a_patch = array();
  public $a_pkg = array();
  public $a_net = array();
  public $a_prj = array();
  public $a_hba = array();
  public $a_disk = array();
  public $a_pool = array();

  public $a_nfss = array(); /* nfs shares */
  public $a_nfsm = array(); /* nfs mount */

  /* SSH */
  private $_ssh = null;
  private $_paths = array();

  /* Logging */
  private $_log = null;
  public $_job = null;

  public function valid($new = true) { /* validate form-based fields */
    global $config;
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
      return null;
    }
  }

 
  public function log($str, $level) {
    Logger::log($str, $this, $level);
  }

  public function getNetworks() {
    $ret = array();

    foreach($this->a_net as $net) {
      if (!$net->layer == 2) {
        continue;
      }
      if (!isset($ret[$net->ifname])) {
        $ret[$net->ifname] = $net;
        $net->fetchAll();
      }
    }
    foreach($this->a_net as $net) {
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

  public function fetchAll($all = 1) {

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
        $this->fetchRL('a_patch');
        $this->fetchRL('a_pkg');
        $this->fetchRL('a_nfss');
        $this->fetchRL('a_nfsm');
        $this->fetchRL('a_net');
        $this->fetchRL('a_prj');
        $this->fetchRL('a_hba');
        $this->fetchRL('a_disk');
        $this->fetchRL('a_pool');
      }

      $this->fetchData();

    } catch (Exception $e) {
      throw($e);
    }
  }

  /* SSH */
  public function connect() {

    try {

      if (!$this->o_suser && $this->fk_suser > 0) {
        $this->_fetchFK('fk_suser');
      }

      $this->_ssh = new SSHSession($this->hostname);
      $this->_ssh->o_user = $this->o_suser;
      $this->_ssh->connect();
      return 0;

    } catch (Exception $e) {
      throw $e;
    }
   
  }

  public function disconnect() {
    $this->_ssh = null;
  }

  public function exec($cmd, $args=null, $timeout=30) {

    $v_cmd = '';

    if ($args) {
      $v_cmd = vsprintf($cmd, $args);
    } else {
      $v_cmd = $cmd;
    }
    try {

      $buf = $this->_ssh->execSecure($v_cmd, $timeout);

    } catch (Exception $e) {
      throw $e;
    }
    return trim($buf);
  }

  public function isFile($path) {
    $found = false;
   
    if (!$this->_ssh) {
      throw new SPXException('SSH Not connected');
    }

    if (empty($path)) {
      throw new SPXException('Path not provided');
    }

    try {
      $r = $this->_ssh->execSecure('test -f '.$path.' && echo 1', 5);
    } catch (Exception $e) {
      throw($e);
    }
    if (!empty($r)) {
      if ($r == 1) {
        return true;
      }
    }
    return false;
  }

  public function findBin($bin, $paths = null) {
    $found = '';
   
    if (!$this->_ssh) {
      throw new SPXException('SSH Not connected');
    }

    if (isset($this->_paths[$bin])) {
      return $this->_paths[$bin];
    }

    /* add the default array of path into $paths or load the one from the OS specific class */
    if (!$paths) {
      if (!$this->fk_os || $this->fk_os == -1) {
        $paths = OS::$binPaths;
      } else {
        if (!$this->o_os) {
	  $this->fetcFK('fk_os');
	}
        $oclass = $this->o_os->class;
        $paths = $oclass::$binPaths;
      }
    }

    foreach($paths as $path) {
      $bpath = $path.'/'.$bin;
      try {
        $r = $this->_ssh->execSecure('test -x '.$bpath.' && echo 1', 5);
      } catch (Exception $e) {
        throw($e);
      }
      if (!empty($r)) {
        if ($r == 1) {
	  /* store it for later use */
	  $this->_paths[$bin] = $bpath;
          return $bpath;
	}
      }
    }
    throw new SPXException($bin.' not found on '.$this);
  }

  public function delete() {

    $this->log("Asked to delete $this", LLOG_DEBUG);
    foreach($this->_rel as $r) {
      $this->log("Treating $r", LLOG_DEBUG);
      if ($this->{$r->ar} && count($this->{$r->ar})) {
	foreach($this->{$r->ar} as $e) {
          $this->log("Deleting $e", LLOG_DEBUG);
	  $e->delete();
	}
      }
    }

    $this->log('Deleting now myself...', LLOG_INFO);
    parent::delete();
  }

  public function __toString() {
    return $this->hostname;
  }

  public function countDiskSpace($exclSan=false, $exclLocal=false) {
    $size = 0;
    foreach($this->a_disk as $disk) {
      if ($exclSan && !$disk->f_local)
        continue;
      if ($exclLocal && $disk->f_local)
        continue;

      $size += $disk->size;
    }
    return $size;
  }


  public function dump() {

    /* echo basic infos first */
    $this->log(sprintf("%15s: %s", 'Server', $this->hostname.' ('.$this->id.')' ), LLOG_INFO);
    $this->log(sprintf("%15s: %s", 'Description', $this->description), LLOG_INFO);
    $this->log(sprintf("%15s: %s", 'RCE', ($this->f_rce)?"enabled":"disabled"), LLOG_INFO);

    
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
    
    /* Dump Relations */


    /* Zones */
    if (count($this->a_zone)) {
      $this->log('', LLOG_INFO);
      $this->log(sprintf("%15s:", 'Zones'), LLOG_INFO);
      foreach($this->a_zone as $z) {
	$z->dump($this);
      }
    }

    /* Network */
    $defrouter = $this->data('net:defrouter');
    if (!$defrouter || empty($defrouter)) $defrouter = null;
    $this->log('', LLOG_INFO);
    $this->log(sprintf("%15s: %s", "Network", ($defrouter)?' (GW='.$defrouter.')':''), LLOG_INFO);
    if (count($this->a_net)) {
      foreach($this->a_net as $n) {
	$n->fetchAll(1);
        $n->dump($this);
      }
    } 

    /* SAN */
    $this->log('', LLOG_INFO);
    $this->log(sprintf("%15s:", "SAN"), LLOG_INFO);
    if (count($this->a_hba)) {
      foreach($this->a_hba as $n) {
        $n->dump($this);
      }
    }

    /* Disks */
    $this->log('', LLOG_INFO);
    $this->log(sprintf("%15s: Total internal capacity: %d GBytes", "Disks", round($this->countDiskSpace(true) / 1024 / 1024 / 1024, 1)), LLOG_INFO);
    if (count($this->a_disk)) {
      foreach($this->a_disk as $n) {
	if ($n->f_local)
          $n->dump($this);
      }
    }
    $this->log('', LLOG_INFO);
    $this->log(sprintf("%15s: Total SAN provisionned: %d GBytes", "Disks", round($this->countDiskSpace(false, true) / 1024 / 1024 / 1024, 1)), LLOG_INFO);
    if (count($this->a_disk)) {
      foreach($this->a_disk as $n) {
        if ($n->f_san)
          $n->dump($this);
      }
    }


    /* NFS Mounts */
    if (count($this->a_nfsm)) {
      $this->log('', LLOG_INFO);
      $this->log(sprintf("%15s:", 'NFS Mounts'), LLOG_INFO);
      foreach($this->a_nfsm as $n) {
        $n->dump($this);
      }
    } 

    /* NFS Share */
    if (count($this->a_nfss)) {
      $this->log('', LLOG_INFO);
      $this->log(sprintf("%15s:", 'NFS Shares'), LLOG_INFO);
      foreach($this->a_nfss as $n) {
        $n->dump($this);
      }
    }
  }

  public static function printCols() {
    return array('Hostname' => 'hostname',
                 'Description' => 'description',
                 'OS' => 'os',
                 'Update?' => 'f_upd',
                 'RCE' => 'f_rce',
                );
  }

  public function toArray() {

    if (!$this->o_os && $this->fk_os > 0) {
      $this->fetchFK('fk_os');
    }

    return array(
                 'hostname' => $this->hostname,
                 'description' => $this->description,
                 'os' => ($this->o_os)?$this->o_os->name:'Unknown',
                 'f_rce' => $this->f_rce,
                 'f_upd' => $this->f_upd,
                );
  }

  public function htmlDump() {
    $ret = array(
	'Hostname' => $this->hostname,
	'Description' => $this->description,
	'Update?' => ($this->f_upd)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>',
	'RCE' => ($this->f_rce)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>',
	'Updated on' => date('d-m-Y', $this->t_upd),
	'Added on' => date('d-m-Y', $this->t_add),
    );
    if ($this->o_cluster) {
      $ret['Cluster'] = '<a href="/view/w/cluster/i/'.$this->o_cluster->id.'">'.$this->o_cluster.'</a>';
    }
    return $ret;
  }

  public function jsonSerialize() {
    return array(
                'id' => $this->id,
                'hostname' => $this->hostname
           );
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
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
                        't_upd' => SQL_PROPE
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
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_suser", "o_suser", "SUser");
    $this->_addFK("fk_os", "o_os", "OS");
    $this->_addFK("fk_pserver", "o_pserver", "PServer");
    $this->_addFK("fk_cluster", "o_cluster", "Cluster");

    $this->_addRL("a_zone", "Zone", array('id' => 'fk_server'));
    $this->_addRL("a_patch", "Patch", array('id' => 'fk_server'));
    $this->_addRL("a_pkg", "Pkg", array('id' => 'fk_server'));
    $this->_addRL("a_net", "Net", array('id' => 'fk_server'));
    $this->_addRL("a_prj", "Prj", array('id' => 'fk_server'));
    $this->_addRL("a_hba", "Hba", array('id' => 'fk_server'));
    $this->_addRL("a_disk", "Disk", array('id' => 'fk_server'));
    $this->_addRL("a_pool", "Pool", array('id' => 'fk_server'));

    $this->_addRL("a_nfss", "NFS", array('id' => 'fk_server', 'CST:share' => 'type'));
    $this->_addRL("a_nfsm", "NFS", array('id' => 'fk_server', 'CST:mount' => 'type'));

    $this->_log = Logger::getInstance();

  }

}
?>
