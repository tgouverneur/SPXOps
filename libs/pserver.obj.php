<?php
/**
 * PServer object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class PServer extends mysqlObj
{
  use logTrait;
  public static $RIGHT = 'PHY';


  public $id = -1;
  public $name = '';
  public $serial = '';
  public $fk_model = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_model = null;

  public function valid($new = true) { /* validate form-based fields */
    global $config;
    $ret = array();

    if (empty($this->name)) {
      $ret[] = 'Missing Name';
    } else {
      if ($new) { /* check for already-exist */
        $check = new PServer();
        $check->name = $this->name;
        if (!$check->fetchFromField('name')) {
          $this->name = '';
          $ret[] = 'Physical Server Name already exist';
          $check = null;
        }
      }
    }

    if (count($ret)) {
      return $ret;
    } else {
      return null;
    }
  }

  public function link() {
    return '<a href="/view/w/pserver/i/'.$this->id.'">'.$this.'</a>';
  }


  public function __toString() {
    return $this->name;
  }

  public function htmlDump($s = null) {

    $this->fetchAll();

    $ar =  array(
        'Physical Name' => $this->name,
        'Serial Number' => $this->serial,
    );
    if ($this->o_model) {
      $ar = array_merge($ar, $this->o_model->htmlDump($s));
    }
    return $ar;
  }

  public function dump($s) {
    $s->log(sprintf("%15s: %s", 'Physical', $this->name.' / serial: '.$this->serial ), LLOG_INFO);

    if ($this->o_model)
      $this->o_model->dump($s);

    return;
  }

  public function fetchAll() {

    try {

      if (!$this->o_model && $this->fk_model > 0) {
        $this->fetchFK('fk_model');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public static function printCols($cfs = array()) {
    return array('Name' => 'name',
                 'Serial' => 'serial',
                 'Vendor' => 'vendor',
                 'Model' => 'model',
                );
  }

  public function toArray($cfs = array()) {

    if (!$this->o_model && $this->fk_model > 0) {
      $this->fetchFK('fk_model');
    }

    return array(
                 'name' => $this->name,
                 'serial' => $this->serial,
                 'vendor' => ($this->o_model)?$this->o_model->vendor:'Unknown',
                 'model' => ($this->o_model)?$this->o_model->name:'Unknown',
                );
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "list_pserver";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "serial" => SQL_PROPE,
                        "fk_model" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "serial" => "serial",
                        "fk_model" => "fk_model",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd"
                 );

    $this->_addFK("fk_model", "o_model", "Model");

  }

}
?>
