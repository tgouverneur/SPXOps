<?php
/**
 * NFS object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

/* @TODO: Add description of nfs share */

class NFS extends mysqlObj
{
  public $id = -1;
  public $type = '';
  public $path = '';
  public $dest = '';
  public $share = '';
  public $acl = '';
  public $size = -1;
  public $used = -1;
  public $fk_server = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_server = null;

  public function equals($z) {
    if (!strcmp($this->type, $z->type)) {
      if ($this->type == 'mount') {
        if (!strcmp($this->path, $z->path) &&
	    $this->fk_server == $z->fk_server) {
	  return true;
	}
      } else if ($this->type == 'share') {
        if (!strcmp($this->share, $z->share) &&
            $this->fk_server == $z->fk_server) {
          return true;
        }
      }
    }
    return false;
  }

  public function fetchAll($all = 1) {

    try {
      if (!$this->o_server && $this->fk_server > 0) {
        $this->fetchFK('fk_server');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    $rc = '['.$this->type.'] ';
    if ($this->type == 'share') {
      $rc .= $this->share;
    }
    if ($this->type == 'mount') {
      $rc .= $this->path;
    }
    return $rc;
  }

  public function pcUsed() {
    $pc = 0;
    if ($this->size > 0) {
      $pc = ($this->used / $this->size) * 100;
    }
    return sprintf("%3d%%", $pc);
  }

  public function dump($s) {
    
    $share = false;
    if (!strcmp($this->type, 'share')) $share = true;
    $type = '['.$this->type.']';
    $txt = '';

    if ($share) {
      $txt .= $this->share.' ('.$this->pcUsed().')';
    } else {
      $txt .= $this->path.' ('.$this->pcUsed().')';
    }
    $s->log(sprintf("\t%15s %s", $type, $txt), LLOG_INFO);
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_nfs';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'type' => SQL_PROPE,
                        'path' => SQL_PROPE,
                        'dest' => SQL_PROPE,
                        'share' => SQL_PROPE,
                        'acl' => SQL_PROPE,
                        'size' => SQL_PROPE,
                        'used' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'type' => 'type',
                        'path' => 'path',
                        'dest' => 'dest',
                        'share' => 'share',
                        'acl' => 'acl',
                        'size' => 'size',
                        'used' => 'used',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_server", "o_server", "Server");

    $this->_log = Logger::getInstance();

  }

}
?>
