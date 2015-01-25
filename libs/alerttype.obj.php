<?php
/**
 * AlertType object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class AlertType extends mysqlObj
{
  public $id = -1;
  public $short = '';
  public $name = '';
  public $desc = '';
  public $t_add = -1;
  public $t_upd = -1;

  public $a_ugroup = array();

  public function equals($z) {
    if (!strcmp($this->short, $z->short)) {
      return true;
    }
    return false;
  }

  public function fetchAll($all = 1) {

  }

  public function __toString() {
    return $this->name;
  }

  public static function printCols($cfs = array()) {
    return array('Name' => 'name',
                 'Description' => 'desc',
                );
  }

  public function toArray($cfs = array()) {

    global $config;
    return array(
                 'name' => $this->name,
                 'desc' => $this->desc,
                 't_add' => date('d-m-Y', $this->t_add),
                 't_upd' => date('d-m-Y', $this->t_upd),
                );
  }

  public function delete() {

    parent::_delAllJT();
    parent::delete();
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_alerttype';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'short' => SQL_PROPE|SQL_EXIST,
                        'name' => SQL_PROPE,
                        'desc' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'short' => 'short',
                        'name' => 'name',
                        'desc' => 'desc',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_ugroup', 'UGroup', 'jt_alerttype_ugroup', array('id' => 'fk_alerttype'), array('id' => 'fk_ugroup'), array());

  }

}
?>
