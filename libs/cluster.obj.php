<?php
/**
 * Cluster object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Cluster extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $description = '';
  public $fk_clver = -1;
  public $f_upd = 0;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_clver = null;
  public $a_server = array();

  /* Connected */
  public $o_cserver = null;

  /* OS Detection */
  public $fk_os = -1;
  public $o_os = null;

  /* Logging */
  private $_log = null;
  public $_job = null;

  public function detectOsFromNodes() {
    if ($this->a_server && count($this->a_server)) {
      $this->fk_os = $this->a_server[0]->fk_os;
      if ($this->a_server[0]->o_os) {
	$this->o_os = $this->a_server[0]->o_os;
      } else {
	$this->o_os = new OS($this->fk_os);
	$this->o_os->fetchFromId();
      }
      return true;
    }
    return false;
  }

  public function connect() {

    if (!count($this->a_server)) {
      throw(new SPXException('No nodes associated with this cluster.'));
    }

    foreach($this->a_server as $server) {
      try {
	$server->connect();
	$this->o_cserver = &$server;
        return;

      } catch (Exception $e) {
        $this->log("Cluster::connect(): $e", LLOG_ERR);
	continue;
      }
    }
    throw(new SPXException("Cannot connect to cluster $this"));
  }

  public function disconnect() {
    if ($this->o_cserver) { 
      $this->o_cserver->disconnect(); 
    }
  }

  /* Wrappers */
  public function exec($cmd, $args=null, $timeout=30) {
    if (!$this->o_cserver) {
      throw(new SPXException("Not connected to any of the nodes..."));
    }
    return $this->o_cserver->exec($cmd, $args, $timeout);
  }

  public function isFile($path) {
    if (!$this->o_cserver) {
      throw(new SPXException("Not connected to any of the nodes..."));
    }
	    return $this->o_cserver->isFile($path);
  }

  public function findBin($bin, $paths = null) {
    if (!$this->o_cserver) {
      throw(new SPXException("Not connected to any of the nodes..."));
    }

    /* add the default array of path into $paths or load the one from the OS specific class */
    if (!$paths) {
      if (!$this->o_cserver->fk_os || $this->o_cserver->fk_os == -1) {
        $paths = OS::$binPaths;
      } else {
        if (!$this->o_cserver->o_os) {
          $this->o_cserver->fetchFK('fk_os');
        }
        $oclass = $this->o_cserver->o_os->class;
        $paths = $oclass::$binPaths;
      }
      if (!$this->fk_clver || $this->fk_clver == -1) {
        $clpaths = CLVer::$binPaths;
      } else {
        if (!$this->o_clver) {
          $this->fetcFK('fk_clver');
        }
        $cclass = $this->o_clver->class;
        $clpaths = $cclass::$binPaths;
      }
      $paths = array_merge($paths, $clpaths);
    }

    return $this->o_cserver->findBin($bin, $paths);
  }

  public function valid($new = true, &$old = null) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->name)) {
      $ret[] = 'Missing Name';
    } else {
      if ($new) { /* check for already-exist */
        $check = new Cluster();
        $check->name = $this->name;
        if (!$check->fetchFromField('name')) {
          $this->name = '';
          $ret[] = 'Cluster Name already exist';
          $check = null;
        }
      } else {
	if (strcmp($this->name, $old->name)) {
	  $check = new Cluster();
          $check->name = $this->name;
          if (!$check->fetchFromField('name')) {
            $this->name = '';
            $ret[] = 'Cluster Name already exist';
            $check = null;
          } else {
	    $old->name = $this->name;
	  }
	}
      }
    }

    if (!is_array($this->a_server)) {
      $ret[] = 'Wrong or no nodes specification';
    } else {
      $a_server = $this->a_server;
      $this->a_server = array();
      foreach($a_server as $sid) {
	$sobj = new Server($sid);
	if ($sobj->fetchFromId()) {
	  $ret[] = "Server id $sid not found in database";
	  continue;
	}
	if ($new && $sobj->fk_cluster > 0) {
	  $ret[] = "Server $sobj is already inside another cluster";
	  continue;
	}
	if (!$new && !isset($old->a_server[$sobj->id]) && $sobj->fk_cluster > 0) {
	  $ret[] = "Server $sobj is already inside another cluster";
	  continue;
	}
	$this->a_server[$sobj->id] = $sobj;
      }
    }

    if (!$new && $old) {
      if ($this->f_upd != $old->f_upd)
	$old->f_upd = $this->f_upd;

      if (strcmp($this->description, $old->description)) 
	$old->description = $this->description;
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

  public function fetchAll($all = 1) {

    try {

      if ($this->fk_clver > 0 && !$this->o_clver) {
        $this->fetchFK('fk_clver');
      }

      $this->fetchRL('a_server');
      $this->fetchData();

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function delete() {

    parent::delete();
  }

  public function link() {
    return '<a href="/view/w/cluster/i/'.$this->id.'">'.$this.'</a>';
  }


  public function __toString() {
    return $this->name;
  }

  public function dump() {

    /* echo basic infos first */
/*
    $this->log(sprintf("%15s: %s", 'Cluster', $this->hostname.' ('.$this->id.')' ), LLOG_INFO);
    $this->log(sprintf("%15s: %s", 'Description', $this->description), LLOG_INFO);
    $this->log(sprintf("%15s: %s", 'RCE', ($this->f_rce)?"enabled":"disabled"), LLOG_INFO);

    
    if ($this->o_os) {
      $this->o_os->dump($this);
    }
*/
  }

  public static function printCols() {
    return array('Name' => 'name',
                 'Description' => 'description',
                 'Update?' => 'f_upd',
                 'Last Update' => 't_upd',
                );
  }

  public function toArray() {

    return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 'f_upd' => $this->f_upd,
                 't_upd' => date('Y-m-d', $this->t_upd),
                );
  }

  public function htmlDump() {
    return array(
	'Name' => $this->name,
	'Description' => $this->description,
	'Update?' => ($this->f_upd)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>',
	'Updated on' => date('d-m-Y', $this->t_upd),
	'Added on' => date('d-m-Y', $this->t_add),
    );
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_cluster';
    $this->_nfotable = 'nfo_server';
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'description' => SQL_PROPE,
                        'fk_clver' => SQL_PROPE,
                        'f_upd' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'description' => 'description',
                        'fk_clver' => 'fk_clver',
                        'f_upd' => 'f_upd',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_clver", "o_clver", "CLVer");

    $this->_addRL("a_server", "Server", array('id' => 'fk_cluster'));

    $this->_log = Logger::getInstance();

  }

}
?>
